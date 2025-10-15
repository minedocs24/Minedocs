<?php
// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
// Includi le funzioni per la gestione della coda job
require_once get_stylesheet_directory() . '/inc/studia-AI/coda-job.php';
// Funzioni generalizzate
require_once get_stylesheet_directory() . '/inc/studia-con-ai.php';


// Assicura che la costante della tabella job sia definita (se non caricata altrove)
if (!defined('TABELLA_STUDIA_AI_JOBS')) {
    global $wpdb;
    if (isset($wpdb) && !empty($wpdb->prefix)) {
        define('TABELLA_STUDIA_AI_JOBS', $wpdb->prefix . 'studia_ai_jobs');
    } else {
        define('TABELLA_STUDIA_AI_JOBS', 'wp_studia_ai_jobs');
    }
}


// Gestione della generazione del riassunto (NUOVO SISTEMA ASINCRONO)
function handle_generate_summary() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }
    
    check_ajax_referer('nonce_generate_summary', 'nonce');

    parse_str($_POST['config'], $config);

    // Verifica i parametri obbligatori
    $required_params = array('mode', 'detail_level', 'language', 'reading_time');
    // foreach ($required_params as $param) {
    //     if (!isset($config[$param]) || empty($config[$param])) {
    //         wp_send_json_error(array('message' => 'Parametro obbligatorio mancante: ' . $param));
    //         return;
    //     }
    // }

    // Validazione parametri con valori ammessi
    $validation_errors = validate_summary_parameters($config);
    // if (!empty($validation_errors)) {
    //     wp_send_json_error(array('message' => 'Parametri non validi: ' . implode(', ', $validation_errors)));
    //     return;
    // }

    // Gestione per documenti già presenti in piattaforma 
    $file_data = get_file_path();
    if (isset($_POST['request_type'])) {
        $config['request_type'] = sanitize_text_field($_POST['request_type']);
    }
    error_log('Request type: ' . $config['request_type']);
    if (is_wp_error($file_data)) {
        wp_send_json_error(array('message' => 'File non trovato'));
        return;
    }
    $file_id = $file_data['file_id'];
    $file_path = $file_data['file_path'];
    
    // Calcolo il costo in punti PRO lato server (affidabile)
    $points_cost = ai_calcola_prezzo_punti_per_file($file_id);
    if (is_wp_error($points_cost)) {
        wp_send_json_error(array('message' => 'Impossibile calcolare il costo in punti: ' . $points_cost->get_error_message()));
        return;
    }

    // Verifica che l'utente abbia abbastanza punti pro
    $user_points_pro = get_points_pro_utente(get_current_user_id());
    if ($user_points_pro < $points_cost) {
        wp_send_json_error(array('message' => 'Non hai abbastanza punti pro per generare il riassunto.'));
        return;
    }

    if(!check_health('summary')) {
        wp_send_json_error(array('message' => 'Servizio di generazione riassunto non disponibile al momento. Riprova più tardi. Nessun punto è stato addebitato.'));
        return;
    }

    // Crea un job di generazione asincrono (il send_job_to_flask invierà il payload in background)
    $result = create_summary_generation_job($file_id, $config);
    
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => 'Errore nella creazione del job: ' . $result->get_error_message()));
        return;
    }

    $job_id = $result;

    // Salva il costo nel job appena creato in DB per evitare ricalcoli
    if (function_exists('studia_ai_update_job_status')) {
        global $wpdb;
        $table = TABELLA_STUDIA_AI_JOBS;
        $wpdb->update($table, array('points_cost' => intval($points_cost), 'updated_at' => current_time('mysql')), array('id' => $job_id), array('%d', '%s'), array('%d'));
    }

    // DEDUZIONE PUNTI PRO
    $deduct = deduci_punti_pro($file_id, $file_path, $job_id, $points_cost, $config);
    if(is_wp_error($deduct)) {
        wp_send_json_error(array('message' => $deduct->get_error_message()));
        return;
    }

    // Controlla se è un riassunto riutilizzato o nuovo
    $existing_job = studia_ai_find_existing_summary($file_id, $config, isset($config['request_type']) ? $config['request_type'] : 'summary');
    
    $message = 'Generazione riassunto avviata con successo. Puoi continuare a navigare sul sito e controllare lo stato nella sezione "Le mie generazioni".';

    wp_send_json_success(array(
        'job_id' => $result,
        'message' => $message,
        'is_duplicate' => $existing_job ? true : false
    ));
}


/**
 * Crea un job di generazione riassunto asincrono
 */
function create_summary_generation_job($file_id, $config) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('user_not_logged', 'Utente non autenticato');
    }

    $file_path = get_attached_file($file_id);
    if (!$file_path || !file_exists($file_path)) {
        return new WP_Error('file_not_found', 'File non trovato');
    }

    // Determina il tipo di richiesta
    $request_type = 'summary';
    if (isset($config['request_type'])) {
        $request_type = $config['request_type'];
    }
    
    // Controlla se esiste già un riassunto completato con la stessa configurazione
    $existing_job = studia_ai_find_existing_summary($file_id, $config, $request_type);
    
    if ($existing_job) {
        error_log('Esiste già un riassunto con questa configurazione');
        // Esiste già un riassunto con questa configurazione
        // Crea un job duplicato completato che riutilizza il file esistente
        $duplicate_job_id = studia_ai_create_duplicate_job(
            $user_id,
            $file_id, 
            $config,
            $existing_job,
            $request_type
        );
        
        if (is_wp_error($duplicate_job_id)) {
            return $duplicate_job_id;
        }
        
        return $duplicate_job_id;
    }
    
    // Non esiste un riassunto con questa configurazione, creane uno nuovo
    $job_id = studia_ai_insert_job(
        $user_id,
        $request_type,
        $file_id,
        $file_path,
        $config,
        0 // priorità normale
    );

    if (is_wp_error($job_id)) {
        return $job_id;
    }

    // Invia la richiesta a Flask (in background)
    send_job_to_flask($job_id, $file_id, $config);

    return $job_id;
}

add_action('wp_ajax_generate_summary', 'handle_generate_summary');
add_action('wp_ajax_nopriv_generate_summary', 'handle_generate_summary'); 



/**
 * Invia il job al server Flask
 */
function send_job_to_flask($job_id, $file_id, $config) {
    $file_path = get_attached_file($file_id);

    // Endpoint corretto
    $flask_url = FLASK_SUMMARY_API_URL;

    // Credenziali Basic Auth
    $username = 'admin';
    $password = 'password';
    $auth = base64_encode("$username:$password");

    // Prepara il payload come richiesto dal servizio Python
    $payload = array(
        'file_path' => $file_path,
        'modalita' => $config['mode'] ?? '',
        'livello_dettaglio' => $config['detail_level'] ?? '',
        'tono' => $config['tone'] ?? '',
        'lingua' => $config['language'] ?? '',
        /*'tempo_lettura' => $config['reading_time'] ?? '',
        'max_words' => $config['max_words'] ?? '',
        'min_words' => $config['min_words'] ?? '',
        'include_quotes' => $config['include_quotes'] ?? '',
        'comprehension_level' => $config['comprehension_level'] ?? '',
        'obiettivo_riassunto' => $config['summary_objective'] ?? '',*/
        'job_id' => $job_id,
        'callback_url' => home_url('/wp-admin/admin-ajax.php?action=summary_completed')
    );

    // Invia la richiesta POST
    $response = wp_remote_post($flask_url, array(
        'body' => json_encode($payload),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $auth
        ),
        'timeout' => 10,
        'blocking' => false // Non attendere la risposta
    ));
}


/**
 * Valida i parametri del riassunto con controlli puntuali
 */
function validate_summary_parameters($config) {
    $errors = array();
    
    // Validazione parametri obbligatori
    $errors = array_merge($errors, validate_required_parameters($config));
    
    // Validazione parametri avanzati (solo se popolati)
    $errors = array_merge($errors, validate_advanced_parameters($config));
    
    return $errors;
}


/**
 * Valida i parametri obbligatori
 */
function validate_required_parameters($config) {
    $errors = array();
    
    // Validazione modalità
    $allowed_modes = array('discorsivo', 'elenco');
    if (!in_array($config['mode'], $allowed_modes)) {
        $errors[] = 'Modalità non valida. Valori ammessi: ' . implode(', ', $allowed_modes);
    }
    
    // Validazione livello di dettaglio
    $allowed_detail_levels = array('alto', 'medio', 'basso');
    if (!in_array($config['detail_level'], $allowed_detail_levels)) {
        $errors[] = 'Livello di dettaglio non valido. Valori ammessi: ' . implode(', ', $allowed_detail_levels);
    }
    
    // Validazione lingua
    $allowed_languages = array('italiano', 'inglese', 'francese', 'tedesco', 'spagnolo', 'portoghese');
    if (!in_array($config['language'], $allowed_languages)) {
        $errors[] = 'Lingua non valida. Valori ammessi: ' . implode(', ', $allowed_languages);
    }
    
    // Validazione tempo di lettura
    $allowed_reading_times = array('breve', 'medio', 'lungo');
    if (!in_array($config['reading_time'], $allowed_reading_times)) {
        $errors[] = 'Tempo di lettura non valido. Valori ammessi: ' . implode(', ', $allowed_reading_times);
    }
    
    return $errors;
}

/**
 * Valida i parametri avanzati (solo se popolati)
 */
function validate_advanced_parameters($config) {
    $errors = array();
    
    // Validazione numero massimo di parole (solo se popolato)
    if (isset($config['max_words']) && $config['max_words'] !== '' && $config['max_words'] !== null) {
        $max_words = intval($config['max_words']);
        if ($max_words < 100 || $max_words > 5000) {
            $errors[] = 'Numero massimo di parole deve essere tra 100 e 5000';
        }
    }
    
    // Validazione numero minimo di parole (solo se popolato)
    if (isset($config['min_words']) && $config['min_words'] !== '' && $config['min_words'] !== null) {
        $min_words = intval($config['min_words']);
        if ($min_words < 50 || $min_words > 1000) {
            $errors[] = 'Numero minimo di parole deve essere tra 50 e 1000';
        }
    }
    
    // Validazione includi citazioni (solo se popolato)
    if (isset($config['include_quotes']) && $config['include_quotes'] !== '' && $config['include_quotes'] !== null) {
        $allowed_quotes = array('no', 'si');
        if (!in_array($config['include_quotes'], $allowed_quotes)) {
            $errors[] = 'Includi citazioni non valido. Valori ammessi: ' . implode(', ', $allowed_quotes);
        }
    }
    
    // Validazione tono (solo se popolato)
    if (isset($config['tone']) && $config['tone'] !== '' && $config['tone'] !== null) {
        $allowed_tones = array('neutro', 'informale', 'professionale', 'tecnico');
        if (!in_array($config['tone'], $allowed_tones)) {
            $errors[] = 'Tono non valido. Valori ammessi: ' . implode(', ', $allowed_tones);
        }
    }
    
    // Validazione livello di comprensione (solo se popolato)
    if (isset($config['comprehension_level']) && $config['comprehension_level'] !== '' && $config['comprehension_level'] !== null) {
        $allowed_comprehension_levels = array('liceale', 'universitario', 'esperto', 'bambino');
        if (!in_array($config['comprehension_level'], $allowed_comprehension_levels)) {
            $errors[] = 'Livello di comprensione non valido. Valori ammessi: ' . implode(', ', $allowed_comprehension_levels);
        }
    }
    
    // Validazione obiettivo riassunto (solo se popolato)
    if (isset($config['summary_objective']) && $config['summary_objective'] !== '' && $config['summary_objective'] !== null) {
        $allowed_objectives = array('studiare', 'presentare', 'condividere', 'ripetere');
        if (!in_array($config['summary_objective'], $allowed_objectives)) {
            $errors[] = 'Obiettivo riassunto non valido. Valori ammessi: ' . implode(', ', $allowed_objectives);
        }
    }
    
    // Validazione logica: min_words non può essere maggiore di max_words (solo se entrambi popolati)
    if (isset($config['min_words']) && isset($config['max_words']) && 
        $config['min_words'] !== '' && $config['min_words'] !== null &&
        $config['max_words'] !== '' && $config['max_words'] !== null) {
        $min_words = intval($config['min_words']);
        $max_words = intval($config['max_words']);
        if ($min_words > $max_words) {
            $errors[] = 'Il numero minimo di parole non può essere maggiore del numero massimo';
        }
    }
    
    return $errors;
}


// Gestione della coda delle generazioni
add_action('wp_ajax_get_summary_jobs', 'handle_get_summary_jobs');
add_action('wp_ajax_nopriv_get_summary_jobs', 'handle_get_summary_jobs');