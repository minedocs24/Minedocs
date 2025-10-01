<?php
// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Includi le funzioni per la gestione della coda job
require_once get_stylesheet_directory() . '/inc/studia-AI/coda-job.php';

// Assicura che la costante della tabella job sia definita (se non caricata altrove)
if (!defined('TABELLA_STUDIA_AI_JOBS')) {
    global $wpdb;
    if (isset($wpdb) && !empty($wpdb->prefix)) {
        define('TABELLA_STUDIA_AI_JOBS', $wpdb->prefix . 'studia_ai_jobs');
    } else {
        define('TABELLA_STUDIA_AI_JOBS', 'wp_studia_ai_jobs');
    }
}

// Registra gli script e gli stili
function studia_con_ai_enqueue_scripts() {
    wp_enqueue_script('studia-con-ai', get_stylesheet_directory_uri() . '/assets/js/studia-con-ai.js', array('jquery'), '1.0.0', true);
    wp_localize_script('studia-con-ai', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'studia_con_ai_enqueue_scripts');

// Gestione dell'analisi del documento
function handle_analyze_document() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }
    
    check_ajax_referer('nonce_analyze_document', 'nonce');

    if (!isset($_FILES['document'])) {
        wp_send_json_error(['message' => 'Nessun file caricato.']);
        return;
    }
    
    if ($_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(['message' => 'Errore durante il caricamento del file.', 'upload_error' => 'upload_failed']);
        return;
    }

    $file = $_FILES['document'];
    
    // Verifica il tipo di file
    $allowed_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error(array('message' => 'Tipo di file non supportato'));
        return;
    }

    // Verifica dimensione massima (10MB)
    $max_file_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_file_size) {
        wp_send_json_error(array('message' => 'Il file eccede le dimensioni massime consentite (10MB)'));
        return;
    }

    // Analizza il documento con Tika
    $result = handle_studia_ai_file_upload($file);
    
    // Se il numero di parole è troppo basso, probabilmente il PDF contiene solo immagini
    if (!is_wp_error($result) && isset($result['words']) && $result['words'] <= 10) {
        wp_send_json_error(array(
            'code' => 'no_text_extracted',
            'message' => 'Il documento caricato non contiene testo estraibile. Probabilmente il PDF contiene solo immagini o testo non riconoscibile. La generazione del riassunto non è disponibile per questo file.'
        ));
        return;
    }

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success($result);
}
add_action('wp_ajax_analyze_document', 'handle_analyze_document');
add_action('wp_ajax_nopriv_analyze_document', 'handle_analyze_document');

/**
 * Gestisce il caricamento e l'analisi del file per Studia con AI
 */
function handle_studia_ai_file_upload($file) {
    // Verifica del contenuto del file
    $file_contents = file_get_contents($file['tmp_name']);
    if (stripos($file_contents, '<?php') !== false) {
        return new WP_Error('unsafe_contents', 'Il contenuto del file non è sicuro.');
    }

    // Imposta la cartella di destinazione per il file AI
    add_filter('upload_dir', 'upload_directory_protected_ai_files');

    // Carica il file nella cartella uploads di WordPress
    $upload = wp_handle_upload($file, array('test_form' => false));

    // Rimuovi il filtro per evitare problemi con altri upload
    remove_filter('upload_dir', 'upload_directory_protected_ai_files');

    if ($upload && !$upload['error']) {
        
        // Analizza il documento con Tika
        $tika_result = analyze_document_with_tika($upload['file']);
        
        if (is_wp_error($tika_result)) {
            return $tika_result;
        }

        // Crea l'attachment per il file
        $file_id = create_attachments($upload, $file);
        
        if (is_wp_error($file_id)) {
            return $file_id;
        }

        // Combina i risultati
        $result = array_merge($tika_result, array(
            'file_id' => $file_id,
            'file_path' => $upload['file']
        ));
        
        return $result;
    } else {
        return new WP_Error('upload_failed', 'Errore durante il caricamento del file.');
    }
}

/**
 * Analizza il documento con Apache Tika
 */
function analyze_document_with_tika($file_path) {
    $tika_port = start_tika_server();
    if (!$tika_port) {
        return new WP_Error('server_error', 'Errore nell\'avvio del server di analisi.');
    }

    try {
        // Inizializzazione del client Apache Tika
        $client = \Vaites\ApacheTika\Client::make('localhost', $tika_port);
        $client = \Vaites\ApacheTika\Client::prepare('localhost', $tika_port);

        // Configura il timeout di cURL
        $client->setOption(CURLOPT_TIMEOUT, 300);
        $client->setOption(CURLOPT_CONNECTTIMEOUT, 300);

        // Ottieni i metadati del file
        $metadata = $client->getMetadata($file_path);

        // Verifica che il file sia un PDF
        if ($metadata->mime !== 'application/pdf') {
            stop_tika_server($tika_port);
            return new WP_Error('invalid_file_type', 'Tipo di file non permesso');
        }
        
        // Ottieni il testo del documento per contare le parole
        $text = $client->getText($file_path);
        $word_count = str_word_count(strip_tags($text));
        
        stop_tika_server($tika_port);

        return array(
            'title' => pathinfo($file_path, PATHINFO_FILENAME),
            'pages' => $metadata->pages ?? 1,
            'words' => $word_count,
            'file_type' => $metadata->mime ?? 'unknown'
        );

    } catch (Exception $e) {
        try {
            stop_tika_server($tika_port);
        } catch (Exception $e) {
            error_log('Errore durante l\'arresto del server Tika: ' . $e->getMessage());
        }
        return new WP_Error('analysis_error', 'Errore durante l\'analisi del documento: ' . $e->getMessage());
    }
}

/**
 * Modifica la directory di upload per i file AI
 */
function upload_directory_protected_ai_files($upload) {
    $upload['path'] = $upload['basedir'] . '/protected/ai' . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . '/protected/ai' . $upload['subdir'];
    
    // Crea la directory se non esiste
    $dir = $upload['path'];
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }
    
    return $upload;
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
    if (isset($_POST['document_id']) && !empty($_POST['document_id'])) {
        // Documento già presente in piattaforma
        $document_id = sanitize_text_field($_POST['document_id']);
        $product_id = get_product_id_by_hash($document_id);
        
        if (!$product_id) {
            wp_send_json_error(array('message' => 'Documento non trovato'));
            return;
        }
        
        // Recupera il file_id dal prodotto
        $file_anteprima_id = get_post_meta($product_id, '_file_anteprima', true);
        if (!$file_anteprima_id) {
            wp_send_json_error(array('message' => 'File del documento non trovato'));
            return;
        }
        
        $file_id = intval($file_anteprima_id);
        $file_path = get_attached_file($file_id);
        
        if (!$file_path || !file_exists($file_path)) {
            wp_send_json_error(array('message' => 'File del documento non trovato'));
            return;
        }
        
        // Aggiungi il tipo di richiesta se specificato
        if (isset($_POST['request_type'])) {
            $config['request_type'] = sanitize_text_field($_POST['request_type']);
        }
        error_log('Request type: ' . $config['request_type']);
        
    } else {
        // Documento caricato tramite upload
        if (!isset($_POST['file_id'])) {
            wp_send_json_error(array('message' => 'File del documento non trovato'));
            return;
        }

        $file_id = intval($_POST['file_id']);
        $file_path = get_attached_file($file_id);

        if (!$file_path || !file_exists($file_path)) {
            wp_send_json_error(array('message' => 'File del documento non trovato'));
            return;
        }
    }

    // Calcolo il costo in punti PRO lato server (affidabile)
    $points_cost = ai_calcola_prezzo_punti_per_file($file_id);
    if (is_wp_error($points_cost)) {
        wp_send_json_error(array('message' => 'Impossibile calcolare il costo in punti: ' . $points_cost->get_error_message()));
        return;
    }

    // Verifica che l'endpoint Flask sia raggiungibile prima di creare il job e scalare punti
    $flask_url = 'http://localhost:4999/summarize';
    $health_check = wp_remote_get($flask_url, array('timeout' => 5, 'blocking' => true));
    if (is_wp_error($health_check) || (isset($health_check['response']['code']) && intval($health_check['response']['code']) >= 500)) {
        // Service not available -> inform user, no punti scalati
        $admins = get_users(array('role' => 'administrator', 'fields' => array('user_email','display_name')));
        $site_name = get_bloginfo('name');
        $current_user = wp_get_current_user();
        $user_email = isset($current_user->user_email) ? $current_user->user_email : '';
        $user_id = get_current_user_id();

        // Dettagli errore
        if (is_wp_error($health_check)) {
            $error_details = esc_html($health_check->get_error_message());
            $http_code = 'n/a';
        } else {
            $http_code = isset($health_check['response']['code']) ? intval($health_check['response']['code']) : 'unknown';
            $error_details = 'HTTP status: ' . $http_code;
        }

        $subject = sprintf('[%s] Servizio AI non disponibile', $site_name);

        // Costruisci messaggio HTML semplice ma leggibile
        $html_message  = '<div style="font-family:Arial,sans-serif;color:#333;">';
        $html_message .= '<h2 style="color:#2c3e50;">Servizio AI non raggiungibile</h2>';
        $html_message .= '<p>Il servizio di generazione riassunti (Flask) non è raggiungibile.</p>';
        $html_message .= '<table cellpadding="6" cellspacing="0" style="border:1px solid #eee;border-collapse:collapse;background:#fafafa;">';
        $html_message .= '<tr><td><strong>URL controllato</strong></td><td>' . esc_html($flask_url) . '</td></tr>';
        $html_message .= '<tr><td><strong>Ora</strong></td><td>' . esc_html(date('Y-m-d H:i:s')) . '</td></tr>';
        $html_message .= '<tr><td><strong>Utente</strong></td><td>' . esc_html($user_email) . ' (ID: ' . intval($user_id) . ')</td></tr>';
        $html_message .= '<tr><td><strong>Dettagli errore</strong></td><td>' . esc_html($error_details) . '</td></tr>';
        $html_message .= '</table>';
        $html_message .= '<p>Si prega di verificare lo stato del servizio Flask Summary e riavviarlo se necessario.</p>';
        $html_message .= '</div>';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (!empty($admins)) {
            foreach ($admins as $admin) {
                $to = isset($admin->user_email) ? $admin->user_email : '';
                if (!empty($to)) {
                    wp_mail($to, $subject, $html_message, $headers);
                }
            }
        }

        error_log('Flask health check failed: ' . $error_details);

        wp_send_json_error(array('message' => 'Servizio di generazione non disponibile al momento. Riprova più tardi. Nessun punto è stato scalato.'));
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

    // Deduce i punti PRO dal wallet dell'utente
    try {
        $sistema_pro = function_exists('get_sistema_punti') ? get_sistema_punti('pro') : null;
        if (!$sistema_pro) {
            throw new Exception('Sistema punti Pro non disponibile');
        }

        $request_type = isset($config['request_type']) ? $config['request_type'] : 'summary';
        $descrizione = 'AI: Generazione ' . ($request_type === 'summary' ? 'riassunto' : $request_type);

        // Determina il nome del file (prodotto della piattaforma o file caricato dall'utente)
        $file_name_for_log = '';
        try {
            // Se il file è associato a un prodotto della piattaforma
            if (function_exists('get_product_id_by_file_id')) {
                $maybe_product = get_product_id_by_file_id($file_id);
                if ($maybe_product) {
                    $prod = get_post($maybe_product);
                    if ($prod && !empty($prod->post_title)) {
                        $file_name_for_log = $prod->post_title;
                    }
                }
            }

            // Se non trovato come prodotto, prova a leggere il titolo dell'allegato
            if (empty($file_name_for_log)) {
                $attachment = get_post($file_id);
                if ($attachment && !empty($attachment->post_title)) {
                    $file_name_for_log = $attachment->post_title;
                }
            }

            // Fallback: usa il nome del file dal percorso
            if (empty($file_name_for_log)) {
                $file_name_for_log = basename($file_path);
            }
        } catch (Exception $e) {
            // se qualcosa va storto, fallback al basename
            $file_name_for_log = basename($file_path);
        }

        $data_log = array(
            'description' => $descrizione . ' per "' . sanitize_text_field($file_name_for_log) . '"',
            'hidden_to_user' => false,
        );

        // Se lancia eccezione per punti insufficienti o altro, verrà gestita dal catch
        $sistema_pro->rimuovi_punti(get_current_user_id(), intval($points_cost), $data_log);
    } catch (Exception $e) {
        // Elimina il job creato se la decurtazione fallisce
        if (function_exists('studia_ai_delete_job')) {
            studia_ai_delete_job($job_id, get_current_user_id());
        }
        wp_send_json_error(array('message' => $e->getMessage()));
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

/**
 * Invia il job al server Flask
 */
function send_job_to_flask($job_id, $file_id, $config) {
    $file_path = get_attached_file($file_id);

    // Endpoint corretto
    $flask_url = 'http://localhost:4999/summarize';

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
    /*
        $file_path = get_attached_file($file_id);
    $user_id = get_current_user_id();
    
    // Configurazione Flask (da personalizzare)
    $flask_url = 'http://localhost:5000/summarize';
    
    // Prepara i dati per Flask
    $flask_data = array(
        'job_id' => $job_id,
        'user_id' => $user_id,
        'file_path' => $file_path,
        'config' => $config,
        'callback_url' => home_url('/wp-admin/admin-ajax.php?action=summary_completed')
    );
    error_log('Flask data: ' . json_encode($flask_data));
    // Invia in background (non bloccare l'utente)
    wp_remote_post($flask_url, array(
        'body' => json_encode($flask_data),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 5, // Timeout breve per non bloccare
        'blocking' => false // Non bloccare l'esecuzione
    ));
    */
    // Log per debug
    error_log('Flask data: ' . json_encode($payload));
    error_log('Flask response: ' . print_r($response, true));
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

/**
 * Estrae il testo dal documento usando Tika
 */
function extract_text_with_tika($file_path) {
    $tika_port = start_tika_server();
    if (!$tika_port) {
        return new WP_Error('server_error', 'Errore nell\'avvio del server di analisi.');
    }

    try {
        // Inizializzazione del client Apache Tika
        $client = \Vaites\ApacheTika\Client::make('localhost', $tika_port);
        $client = \Vaites\ApacheTika\Client::prepare('localhost', $tika_port);

        // Configura il timeout di cURL
        $client->setOption(CURLOPT_TIMEOUT, 300);
        $client->setOption(CURLOPT_CONNECTTIMEOUT, 300);

        // Ottieni il testo del documento
        $text = $client->getText($file_path);
        
        stop_tika_server($tika_port);

        return $text;

    } catch (Exception $e) {
        try {
            stop_tika_server($tika_port);
        } catch (Exception $e) {
            error_log('Errore durante l\'arresto del server Tika: ' . $e->getMessage());
        }
        return new WP_Error('extraction_error', 'Errore nell\'estrazione del testo: ' . $e->getMessage());
    }
}

/**
 * Genera il riassunto dal testo basato sui parametri
 * TEMPORANEAMENTE COMMENTATO - In futuro si integrerà con Flask
 */
function generate_summary_from_text($text, $config) {
    /*
    // CODICE TEMPORANEAMENTE COMMENTATO
    // Per ora, creiamo un riassunto semplice
    // In futuro, qui si integrerà con un servizio AI reale
    
    $summary = "RIASSUNTO GENERATO CON STUDIA CON AI\n";
    $summary .= "=====================================\n\n";
    
    $summary .= "PARAMETRI UTILIZZATI:\n";
    $summary .= "=====================\n";
    $summary .= "- Modalità: " . ucfirst($config['mode']) . "\n";
    $summary .= "- Livello di dettaglio: " . ucfirst($config['detail_level']) . "\n";
    $summary .= "- Lingua: " . ucfirst($config['language']) . "\n";
    $summary .= "- Tempo di lettura: " . ucfirst($config['reading_time']) . "\n";
    
    // Parametri avanzati (solo se popolati)
    if (isset($config['max_words']) && $config['max_words'] !== '' && $config['max_words'] !== null) {
        $summary .= "- Parole massime: " . $config['max_words'] . "\n";
    }
    if (isset($config['min_words']) && $config['min_words'] !== '' && $config['min_words'] !== null) {
        $summary .= "- Parole minime: " . $config['min_words'] . "\n";
    }
    if (isset($config['include_quotes']) && $config['include_quotes'] !== '' && $config['include_quotes'] !== null) {
        $summary .= "- Includi citazioni: " . ucfirst($config['include_quotes']) . "\n";
    }
    if (isset($config['tone']) && $config['tone'] !== '' && $config['tone'] !== null) {
        $summary .= "- Tono: " . ucfirst($config['tone']) . "\n";
    }
    if (isset($config['comprehension_level']) && $config['comprehension_level'] !== '' && $config['comprehension_level'] !== null) {
        $summary .= "- Livello di comprensione: " . ucfirst($config['comprehension_level']) . "\n";
    }
    if (isset($config['summary_objective']) && $config['summary_objective'] !== '' && $config['summary_objective'] !== null) {
        $summary .= "- Obiettivo: " . ucfirst($config['summary_objective']) . "\n";
    }
    
    $summary .= "\nTESTO ORIGINALE:\n";
    $summary .= "================\n";
    $summary .= substr($text, 0, 1000) . "...\n\n";
    
    $summary .= "RIASSUNTO:\n";
    $summary .= "==========\n";
    
    // Genera un riassunto più realistico basato sui parametri
    $word_count = str_word_count($text);
    $summary .= "Questo è un riassunto di esempio generato dal sistema Studia con AI.\n\n";
    
    // Adatta il riassunto in base alla modalità
    if ($config['mode'] === 'discorsivo') {
        $summary .= "Il documento originale contiene " . $word_count . " parole e presenta i seguenti contenuti principali:\n\n";
        $summary .= "Il testo analizzato tratta di argomenti di interesse generale, strutturati in modo logico e sequenziale. ";
        $summary .= "I concetti principali sono presentati in modo discorsivo, facilitando la comprensione e l'apprendimento.\n\n";
    } else { // elenco
        $summary .= "Il documento originale contiene " . $word_count . " parole. Ecco i punti principali:\n\n";
        $summary .= "• Primo punto chiave del documento\n";
        $summary .= "• Secondo punto importante da ricordare\n";
        $summary .= "• Terzo concetto fondamentale\n";
        $summary .= "• Quarto elemento significativo\n\n";
    }
    
    // Adatta in base al livello di dettaglio
    switch ($config['detail_level']) {
        case 'alto':
            $summary .= "Il riassunto è stato generato con un livello di dettaglio elevato, includendo tutti i concetti principali e secondari.\n";
            break;
        case 'medio':
            $summary .= "Il riassunto mantiene un equilibrio tra completezza e concisione, focalizzandosi sui concetti essenziali.\n";
            break;
        case 'basso':
            $summary .= "Il riassunto è conciso e focalizzato sui punti chiave, ideale per una rapida revisione.\n";
            break;
    }
    
    // Adatta in base al tempo di lettura
    switch ($config['reading_time']) {
        case 'breve':
            $summary .= "Tempo di lettura stimato: 2-3 minuti\n";
            break;
        case 'medio':
            $summary .= "Tempo di lettura stimato: 5-7 minuti\n";
            break;
        case 'lungo':
            $summary .= "Tempo di lettura stimato: 10-15 minuti\n";
            break;
    }
    
    // Adatta in base al tono (solo se specificato)
    if (isset($config['tone']) && $config['tone'] !== '' && $config['tone'] !== null) {
        switch ($config['tone']) {
            case 'informale':
                $summary .= "Il tono utilizzato è informale e amichevole.\n";
                break;
            case 'professionale':
                $summary .= "Il tono utilizzato è professionale e formale.\n";
                break;
            case 'tecnico':
                $summary .= "Il tono utilizzato è tecnico e specialistico.\n";
                break;
            default:
                $summary .= "Il tono utilizzato è neutro e bilanciato.\n";
                break;
        }
    }
    
    // Adatta in base al livello di comprensione (solo se specificato)
    if (isset($config['comprehension_level']) && $config['comprehension_level'] !== '' && $config['comprehension_level'] !== null) {
        switch ($config['comprehension_level']) {
            case 'liceale':
                $summary .= "Il riassunto è adattato per un livello di comprensione liceale.\n";
                break;
            case 'universitario':
                $summary .= "Il riassunto è adattato per un livello di comprensione universitario.\n";
                break;
            case 'esperto':
                $summary .= "Il riassunto è adattato per un livello di comprensione esperto.\n";
                break;
            case 'bambino':
                $summary .= "Il riassunto è adattato per un livello di comprensione elementare.\n";
                break;
        }
    }
    
    // Adatta in base all'obiettivo (solo se specificato)
    if (isset($config['summary_objective']) && $config['summary_objective'] !== '' && $config['summary_objective'] !== null) {
        switch ($config['summary_objective']) {
            case 'studiare':
                $summary .= "Il riassunto è ottimizzato per lo studio e la memorizzazione.\n";
                break;
            case 'presentare':
                $summary .= "Il riassunto è strutturato per essere utilizzato in presentazioni.\n";
                break;
            case 'condividere':
                $summary .= "Il riassunto è adattato per la condivisione online.\n";
                break;
            case 'ripetere':
                $summary .= "Il riassunto è ottimizzato per la ripetizione e il ripasso.\n";
                break;
        }
    }
    
    $summary .= "\n" . str_repeat("=", 50) . "\n";
    $summary .= "Data di generazione: " . date('d/m/Y H:i:s') . "\n";
    $summary .= "Generato da: Studia con AI - Minedocs\n";
    $summary .= "Lingua: " . ucfirst($config['language']) . "\n";
    
    return $summary;
    */
    
    // TEMPORANEO: Ritorna un messaggio di placeholder
    return "Generazione riassunto temporaneamente disabilitata. In attesa dell'integrazione con Flask.";
}
add_action('wp_ajax_generate_summary', 'handle_generate_summary');
add_action('wp_ajax_nopriv_generate_summary', 'handle_generate_summary'); 

// Gestione della coda delle generazioni
add_action('wp_ajax_get_summary_jobs', 'handle_get_summary_jobs');
add_action('wp_ajax_nopriv_get_summary_jobs', 'handle_get_summary_jobs');

// Gestione aggiuntiva dei job
add_action('wp_ajax_get_job_details', 'handle_get_job_details');
add_action('wp_ajax_nopriv_get_job_details', 'handle_get_job_details');

add_action('wp_ajax_delete_job', 'handle_delete_job');
add_action('wp_ajax_nopriv_delete_job', 'handle_delete_job');

/**
 * Restituisce i dettagli di un job specifico
 */
function handle_get_job_details() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }

    check_ajax_referer('nonce_get_job_details', 'nonce');
    
    if (!isset($_POST['job_id'])) {
        wp_send_json_error(['message' => 'ID job mancante']);
        return;
    }
    
    $job_id = intval($_POST['job_id']);
    $user_id = get_current_user_id();
    
    // Ottieni il job dal database
    $job = studia_ai_get_job($job_id);
    
    if (!$job || $job['user_id'] != $user_id) {
        wp_send_json_error(['message' => 'Job non trovato o non autorizzato']);
        return;
    }
    
    // Formatta i dati per il frontend
    $formatted_job = array(
        'job_id' => $job['id'],
        // 'user_id' => $job['user_id'],
        'request_type' => $job['request_type'],
        'request_date' => $job['request_date'],
        // 'file_id' => $job['file_id'],
        // 'file_path' => $job['file_path'],
        'config' => $job['request_params'],
        'status' => $job['status'],
        // 'priority' => $job['priority'],
        'points_cost' => isset($job['points_cost']) ? intval($job['points_cost']) : null,
        'download_url' => ($job['status'] === 'completed') ? get_summary_download_url($job['id'], $job['user_id']) : null,
        // 'started_at' => $job['started_at'],
        'completed_at' => $job['completed_at'],
        // 'result_file' => $job['result_file'],
        // 'result_url' => $job['result_url'],
        'error_message' => $job['error_message'],
        // 'retry_count' => $job['retry_count'],
        // 'max_retries' => $job['max_retries'],
        // 'created_at' => $job['created_at'],
        // 'updated_at' => $job['updated_at']
    );
    
    wp_send_json_success($formatted_job);
}

/**
 * Elimina un job (solo per l'utente proprietario)
 */
function handle_delete_job() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }

    check_ajax_referer('nonce_delete_job', 'nonce');
    
    if (!isset($_POST['job_id'])) {
        wp_send_json_error(['message' => 'ID job mancante']);
        return;
    }
    
    $job_id = intval($_POST['job_id']);
    $user_id = get_current_user_id();
    
    // Verifica che il job appartenga all'utente
    $job = studia_ai_get_job($job_id);
    if (!$job || $job['user_id'] != $user_id) {
        wp_send_json_error(['message' => 'Job non trovato o non autorizzato']);
        return;
    }
    
    // Elimina il job
    $result = studia_ai_delete_job($job_id, $user_id);
    
    if ($result) {
        wp_send_json_success(['message' => 'Job eliminato con successo']);
    } else {
        wp_send_json_error(['message' => 'Errore nell\'eliminazione del job']);
    }
}

/**
 * Restituisce la lista dei job di generazione dell'utente
 */
function handle_get_summary_jobs() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }

    check_ajax_referer('nonce_summary_jobs', 'nonce');
    
    $user_id = get_current_user_id();
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    
    // Calcola offset
    $offset = ($page - 1) * $per_page;
    
    // Ottieni i job dal database con paginazione
    $jobs = studia_ai_get_user_jobs($user_id, $per_page, $offset);
    
    // Ottieni il totale dei job per l'utente
    $total_jobs = studia_ai_get_user_jobs_count($user_id);
    
    // Formatta i dati per il frontend
    $formatted_jobs = array();
    foreach ($jobs as $job) {
        // Ottieni il nome del file o il titolo del documento
        $document_title = '';
        $is_platform_document = false;
        
        if ($job['file_id']) {
            $attachment = get_post($job['file_id']);
            if ($attachment) {
                // Controlla se questo file è associato a un prodotto della piattaforma
                $product_id = get_product_id_by_file_id($job['file_id']);
                if ($product_id) {
                    // È un documento della piattaforma, usa il titolo del prodotto
                    $product = get_post($product_id);
                    if ($product) {
                        $document_title = $product->post_title;
                        $is_platform_document = true;
                    }
                } else {
                    // È un file caricato dall'utente, usa il nome del file
                    $document_title = $attachment->post_title;
                    if (empty($document_title)) {
                        // Se il titolo è vuoto, usa il nome del file dal percorso
                        $file_path = $job['file_path'];
                        if ($file_path) {
                            $document_title = basename($file_path);
                        }
                    }
                }
            }
        }
        
        $download_url = null;
        if ($job['status'] === 'completed') {
            $download_url = get_summary_download_url($job['id'], $user_id);
        }

        $formatted_jobs[$job['id']] = array(
            'job_id' => $job['id'],
            // 'user_id' => $job['user_id'],
            'request_type' => $job['request_type'],
            'request_date' => $job['request_date'],
            // 'file_id' => $job['file_id'],
            // 'file_path' => $job['file_path'],
            'file_name' => $document_title,
            'is_platform_document' => $is_platform_document,
            'config' => $job['request_params'], // Già JSON
            'status' => $job['status'],
            'points_cost' => isset($job['points_cost']) ? intval($job['points_cost']) : null,
            // 'started_at' => $job['started_at'],
            'completed_at' => $job['completed_at'],
            // 'result_file' => $job['result_file'],
            // 'result_url' => $job['result_url'],
            'download_url' => $download_url,
            'error_message' => $job['error_message'],
            // 'retry_count' => $job['retry_count'],
            // 'max_retries' => $job['max_retries'],
            // 'created_at' => $job['created_at'],
            // 'updated_at' => $job['updated_at']
        );
    }
    
    wp_send_json_success(array(
        'jobs' => $formatted_jobs,
        'total' => $total_jobs,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total_jobs / $per_page)
    ));
}

/**
 * Ottiene il product_id associato a un file_id
 */
function get_product_id_by_file_id($file_id) {
    global $wpdb;
    
    // Cerca prodotti che hanno questo file_id come _file_anteprima
    $args = array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => '_file_anteprima',
                'value' => $file_id,
                'compare' => '='
            )
        ),
        'post_status' => array('publish', 'draft'),
        'posts_per_page' => 1
    );
    
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $post = $query->posts[0];
        return $post->ID;
    }
    
    return null;
}

/**
 * Genera un URL di download sicuro per un riassunto generato
 */
function get_summary_download_url($job_id, $user_id) {
    // Verifica che il job esista e appartenga all'utente
    $job = studia_ai_get_job($job_id);
    if (!$job || $job['user_id'] != $user_id) {
        return false;
    }
    
    // Verifica che il job sia completato e abbia un file risultato
    if ($job['status'] !== 'completed' || empty($job['result_file'])) {
        return false;
    }
    
    // Genera un token sicuro per il download
    $token = hash('sha256', $job_id . $user_id . 'summary_download_secure');
    
    // Costruisci l'URL di download sicuro
    $download_url = home_url('/wp-admin/admin-ajax.php?action=download_ai&job_id=' . $job_id . '&token=' . $token);
    error_log('Download URL: ' . $download_url);
    return $download_url;
}


/**
 * Gestisce il download sicuro dei file generati da AI (Riassunti, Mappe, etc.)
 */
function handle_ai_download() {
    error_log('handle_ai_download');
    // Verifica che l'utente sia loggato
    if (!is_user_logged_in()) {
        wp_die('Devi essere loggato per scaricare questo file.');
    }

    // Verifica i parametri
    if (!isset($_GET['job_id']) || !isset($_GET['token'])) {
        wp_die('Parametri mancanti.');
    }

    $job_id = intval($_GET['job_id']);
    $user_id = get_current_user_id();
    $token = sanitize_text_field($_GET['token']);

    // Verifica il token (stesso schema utilizzato per i summary)
    $expected_token = hash('sha256', $job_id . $user_id . 'summary_download_secure');
    if ($token !== $expected_token) {
        wp_die('Token non valido.');
    }

    // Ottieni il job dal database
    $job = studia_ai_get_job($job_id);
    if (!$job || $job['user_id'] != $user_id) {
        wp_die('Accesso negato.');
    }

    // Verifica che il job sia completato
    if ($job['status'] !== 'completed') {
        wp_die('Il file non è ancora pronto per il download.');
    }

    // Verifica che esista il file risultato
    if (empty($job['result_file'])) {
        wp_die('File non trovato.');
    }

    $file_path = $job['result_file'];

    // Se il risultato è un URL remoto, prova a scaricarlo e salvarlo localmente
    if (preg_match('#^https?://#i', $file_path)) {
        $tmp = wp_tempnam();
        $response = wp_remote_get($file_path, array('timeout' => 60, 'stream' => true, 'filename' => $tmp));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            error_log('Errore scaricando immagine remota per job ' . $job_id . ': ' . (is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_response_message($response)));
            wp_die('Impossibile recuperare il file remoto.');
        }
        // Sposta il file temporaneo nella directory protetta e ottieni path definitivo
        $saved = save_map_png_file($tmp, $job_id);
        if (is_wp_error($saved) || !$saved) {
            error_log('Salvataggio PNG remoto fallito per job ' . $job_id);
            wp_die('Impossibile salvare il file.');
        }
        $file_path = $saved;
    }

    // Normalizza percorso
    $file_path = wp_normalize_path($file_path);

    if (!file_exists($file_path)) {
        error_log('File non esiste per download map: ' . $file_path . ' (job ' . $job_id . ')');
        wp_die('File non trovato.');
    }

    // Determina il nome del file per il download
    $file_name = basename($file_path);
    if (empty($file_name)) {
        $file_name = 'mappa_' . $job_id . '.png';
    }

    // Rileva MIME type reale
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);
    } else {
        $mime = 'application/octet-stream';
    }

    // Disabilita la visualizzazione degli errori e pulisci buffer per evitare output extra
    @ini_set('display_errors', '0');
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Log dei primi byte (debug temporaneo)
    $fh = fopen($file_path, 'rb');
    if ($fh) {
        $header = fread($fh, 8);
        fclose($fh);
        error_log('Map PNG header hex for job ' . $job_id . ': ' . bin2hex($header));
    }

    // Invia headers corretti per PNG/binary
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    // Forza l'invio e invia il file
    flush();
    readfile($file_path);
    exit;
}

add_action('wp_ajax_download_ai', 'handle_ai_download');
add_action('wp_ajax_nopriv_download_ai', 'handle_ai_download');

/**
 * Gestisce la richiesta AJAX per ottenere l'URL di download sicuro
 */
function handle_get_summary_download_url() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }
    
    check_ajax_referer('nonce_summary_download', 'nonce');
    
    if (!isset($_POST['job_id'])) {
        wp_send_json_error(['message' => 'ID job mancante']);
        return;
    }
    
    $job_id = intval($_POST['job_id']);
    $user_id = get_current_user_id();
    
    // Ottieni l'URL di download sicuro
    $download_url = get_summary_download_url($job_id, $user_id);
    
    if (!$download_url) {
        wp_send_json_error(['message' => 'Impossibile generare l\'URL di download']);
        return;
    }
    
    wp_send_json_success(['download_url' => $download_url]);
}

// Registra gli handler per il download sicuro


add_action('wp_ajax_get_summary_download_url', 'handle_get_summary_download_url');
add_action('wp_ajax_nopriv_get_summary_download_url', 'handle_get_summary_download_url');

/**
 * Calcola il costo dinamico in punti pro interrogando Flask (solo backend)
 */
function handle_get_dynamic_price() {
    // Closure per notificare gli amministratori in caso di errore
    $notify_admin = function($message, $context = array()) {
        if (!function_exists('get_option') || !function_exists('wp_mail')) {
            return;
        }
        $admins = get_users(['role' => 'administrator', 'fields' => array('user_email','display_name')]);
        if (empty($admins)) {
            return;
        }
        $site_name = function_exists('get_bloginfo') ? get_bloginfo('name') : 'Sito';
        $current_user = function_exists('wp_get_current_user') ? wp_get_current_user() : null;
        $user_info = $current_user ? (sprintf('ID %d (%s)', $current_user->ID, $current_user->user_login)) : 'Utente non disponibile';

        $subject = '[' . $site_name . '] Errore calcolo prezzo AI';

        // Corpo HTML semplice
        $html  = '<div style="font-family:Arial,sans-serif;color:#333;">';
        $html .= '<h2 style="color:#c0392b;">Errore durante il calcolo del prezzo AI</h2>';
        $html .= '<p>Si è verificato un problema durante il calcolo dinamico del prezzo per un documento.</p>';
        $html .= '<table cellpadding="6" cellspacing="0" style="border:1px solid #eee;border-collapse:collapse;background:#fafafa;">';
        $html .= '<tr><td><strong>Utente</strong></td><td>' . esc_html($user_info) . '</td></tr>';
        $html .= '<tr><td><strong>Data/Ora</strong></td><td>' . esc_html(gmdate('Y-m-d H:i:s')) . ' UTC</td></tr>';
        $html .= '<tr><td><strong>Messaggio</strong></td><td>' . esc_html($message) . '</td></tr>';
        if (!empty($context)) {
            $html .= '<tr><td><strong>Contesto</strong></td><td><pre style="white-space:pre-wrap;">' . esc_html(print_r($context, true)) . '</pre></td></tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        foreach ($admins as $admin) {
            $to = isset($admin->user_email) ? $admin->user_email : '';
            if (!empty($to)) {
                wp_mail($to, $subject, $html, $headers);
            }
        }
    };

    if (!is_user_logged_in()) {
        $notify_admin('Richiesta non autorizzata per calcolo prezzo AI', array('reason' => 'not_logged_in'));
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }

    check_ajax_referer('nonce_get_dynamic_price', 'nonce');

    // file_id o document_id sono le due fonti
    $file_path = '';

    if (isset($_POST['file_id']) && !empty($_POST['file_id'])) {
        $file_id = intval($_POST['file_id']);
        $path = get_attached_file($file_id);
        if (!$path || !file_exists($path)) {
            $notify_admin('File non trovato per file_id', array('file_id' => $file_id));
            wp_send_json_error(['message' => 'File non trovato.']);
            return;
        }
        $file_path = $path;
    } elseif (isset($_POST['document_id']) && !empty($_POST['document_id'])) {
        // Documento in piattaforma
        if (!function_exists('get_product_id_by_hash')) {
            require_once get_stylesheet_directory() . '/inc/products.php';
        }
        $document_id = sanitize_text_field($_POST['document_id']);
        $product_id = get_product_id_by_hash($document_id);
        if (!$product_id) {
            $notify_admin('Documento non trovato', array('document_id' => $document_id));
            wp_send_json_error(['message' => 'Documento non trovato']);
            return;
        }
        $file_anteprima_id = get_post_meta($product_id, '_file_anteprima', true);
        if (!$file_anteprima_id) {
            $notify_admin('File del documento non trovato (meta _file_anteprima mancante)', array('product_id' => $product_id));
            wp_send_json_error(['message' => 'File del documento non trovato']);
            return;
        }
        $path = get_attached_file(intval($file_anteprima_id));
        if (!$path || !file_exists($path)) {
            $notify_admin('Percorso file del documento non valido o inesistente', array('product_id' => $product_id, 'file_id' => intval($file_anteprima_id)));
            wp_send_json_error(['message' => 'File del documento non trovato']);
            return;
        }
        $file_path = $path;
    } else {
        $notify_admin('Parametro file non specificato (assenza di file_id e document_id)');
        wp_send_json_error(['message' => 'Parametro file non specificato.']);
        return;
    }

    // Calcolo tramite funzione condivisa (riuso lato server)
    $points_or_error = ai_calcola_prezzo_punti($file_path, $notify_admin);
    if (is_wp_error($points_or_error)) {
        wp_send_json_error(['message' => $points_or_error->get_error_message()]);
        return;
    }

    wp_send_json_success([
        'points' => intval($points_or_error)
    ]);
}

add_action('wp_ajax_get_dynamic_price', 'handle_get_dynamic_price');
add_action('wp_ajax_nopriv_get_dynamic_price', 'handle_get_dynamic_price');

/**
 * Funzione riutilizzabile per calcolare i punti necessari su un file
 * Ritorna intero punti o WP_Error
 */
function ai_calcola_prezzo_punti($file_path, $notify_admin = null) {
    if (empty($file_path) || !file_exists($file_path)) {
        return new WP_Error('file_not_found', 'File non trovato.');
    }

    $endpoint = function_exists('get_option') ? get_option('FLASK_ANALYSIS_API_URL', 'http://localhost:4998/analizza-file') : 'http://localhost:4998/analizza-file';
    $api_key = function_exists('get_option') ? get_option('FLASK_ANALYSIS_API_KEY', 'key1') : 'key1';

    $payload = [ 'file_path' => $file_path ];

    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-API-Key' => $api_key,
        ],
        'body' => wp_json_encode($payload),
        'timeout' => 15,
        'blocking' => true,
    ]);

    if (is_wp_error($response)) {
        if (is_callable($notify_admin)) {
            $notify_admin('Errore di connessione al servizio di analisi', array(
                'endpoint' => $endpoint,
                'payload' => $payload,
                'wp_error' => $response->get_error_message(),
            ));
        }
        return new WP_Error('analysis_connection_error', 'Errore di connessione al servizio di analisi.');
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($code < 200 || $code >= 300) {
        if (is_callable($notify_admin)) {
            $notify_admin('Risposta non valida dal servizio di analisi', array(
                'endpoint' => $endpoint,
                'payload' => $payload,
                'http_code' => $code,
                'body' => $body,
            ));
        }
        return new WP_Error('analysis_bad_response', 'Risposta non valida dal servizio di analisi.');
    }

    $data = json_decode($body, true);
    if (!is_array($data) || !isset($data['analisi']) || !isset($data['analisi']['punti_finali'])) {
        if (is_callable($notify_admin)) {
            $notify_admin('Formato risposta inatteso dal servizio di analisi', array(
                'endpoint' => $endpoint,
                'payload' => $payload,
                'body' => $body,
            ));
        }
        return new WP_Error('analysis_bad_format', 'Formato risposta inatteso.');
    }

    $rawPoints = floatval($data['analisi']['punti_finali']);
    $points = (int) ceil($rawPoints);
    if ($points <= 0) {
        $points = 1; // costo minimo 1 punto
    }
    return $points;
}

/**
 * Helper per calcolare i punti partendo da file_id allegato
 */
function ai_calcola_prezzo_punti_per_file($file_id) {
    $path = get_attached_file(intval($file_id));
    if (!$path || !file_exists($path)) {
        return new WP_Error('file_not_found', 'File del documento non trovato');
    }
    return ai_calcola_prezzo_punti($path);
}

/**
 * Funzione di test per verificare il sistema di download sicuro
 * (da rimuovere in produzione)
 */
function test_summary_download_system() {
    if (!current_user_can('administrator')) {
        return;
    }
    
    // Test: crea un job di test
    $user_id = get_current_user_id();
    $test_job_id = studia_ai_insert_job(
        $user_id,
        'summary',
        0, // file_id di test
        '/path/to/test/file.pdf',
        array('mode' => 'discorsivo', 'detail_level' => 'medio'),
        0
    );
    
    if (is_wp_error($test_job_id)) {
        error_log('Errore nella creazione del job di test: ' . $test_job_id->get_error_message());
        return;
    }
    
    // Simula il completamento del job
    $test_file_path = wp_upload_dir()['basedir'] . '/protected/ai/test_summary.txt';
    $test_content = "Questo è un riassunto di test generato il " . date('Y-m-d H:i:s');
    file_put_contents($test_file_path, $test_content);
    
    // Aggiorna il job come completato
    studia_ai_update_job_status($test_job_id, 'completed', array(
        'result_file' => $test_file_path,
        'result_url' => ''
    ));
    
    // Test: genera l'URL di download
    $download_url = get_summary_download_url($test_job_id, $user_id);
    
    if ($download_url) {
        error_log('Test download URL generato con successo: ' . $download_url);
    } else {
        error_log('Errore nella generazione dell\'URL di download di test');
    }
}

// Uncomment per testare il sistema (solo per amministratori)
// add_action('init', 'test_summary_download_system'); 