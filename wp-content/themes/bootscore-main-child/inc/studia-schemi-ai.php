<?php
// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Includi le funzioni della coda job
require_once get_stylesheet_directory() . '/inc/studia-AI/coda-job.php';

// Assicura che la costante della tabella esista (fallback)
if (!defined('TABELLA_STUDIA_AI_JOBS')) {
    global $wpdb;
    if (isset($wpdb) && !empty($wpdb->prefix)) {
        define('TABELLA_STUDIA_AI_JOBS', $wpdb->prefix . 'studia_ai_jobs');
    } else {
        define('TABELLA_STUDIA_AI_JOBS', 'wp_studia_ai_jobs');
    }
}

/**
 * 
 * 
 * @return void (invia risposta JSON)
 */
// -----------------GESTISCE LA GENERAZIONE MAPPA CONCETTUALE-----------------
function handle_generate_map() {
    /* Utente carica documento --> genera job in coda --> invia job a Flask --> Flask genera mappa --> Flask invia risultato --> job completato --> ritorna risultato
      1. Verifica che l'utente sia loggato.
      2. Controlla il nonce per sicurezza.
      3. Verifica tipo e dimensione del file caricato.
      4. Controlla se il documento è già presente (document_id) o se è un nuovo upload (file_id)
      5. Recupera il percorso fisico del file.
      6. Calcola il costo in punti.
      7. Se qualcosa fallisce, invia errore JSON e interrompi esecuzione.
      8. Gestione health del servizio Flask
      9. Creazione job in coda, invio job a Flask e salvataggio costo punti*/

    // Controlla che l'utente sia loggato
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }

    // Controlla il nonce per la sicurezza ajax (Se proviene o meno da front-end legittimo)
    check_ajax_referer('nonce_generate_mappe', 'nonce');

    // Recupera il file
     $file = $_FILES['document'];

     // Verifica il tipo di file (PDF o Word) 
     /*$allowed_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
     if (!in_array($file['type'], $allowed_types)) {
         wp_send_json_error(array('message' => 'Tipo di file non supportato'));
         return;
     }
 
     // Verifica dimensione massima (10MB)
     $max_file_size = 10 * 1024 * 1024; // 10MB
     if ($file['size'] > $max_file_size) {
         wp_send_json_error(array('message' => 'Il file eccede le dimensioni massime consentite (10MB)'));
         return;
     }*/

    // Controlla se il documento è già presente in piattaforma
    if (isset($_POST['document_id']) && !empty($_POST['document_id'])) {
        // Documento già presente in piattaforma
        $document_id = sanitize_text_field($_POST['document_id']);
        $product_id = get_product_id_by_hash($document_id); // Recupera file_id dal prodotto
        
        if (!$product_id) {
            wp_send_json_error(['message' => 'Documento non trovato']);
            return;
        }

        // Recupera il file_id dell'anteprima
        $file_anteprima_id = get_post_meta($product_id, '_file_anteprima', true);
        if (!$file_anteprima_id) {
            wp_send_json_error(['message' => 'File del documento non trovato']);
            return;
        }
        
        $file_id = intval($file_anteprima_id);
    } else {
        // Documento caricato tramite upload
        if (!isset($_POST['file_id'])) {
            wp_send_json_error(['message' => 'File del documento non trovato']);
            return;
        }
        $file_id = intval($_POST['file_id']);
    }

    // Recupera il percorso del file sul server
    $file_path = get_attached_file($file_id);
    if (!$file_path || !file_exists($file_path)) {
        wp_send_json_error(['message' => 'File del documento non trovato']);
        return;
    }

    // Calcola il costo in punti PRO lato server
    $points_cost = ai_calcola_prezzo_punti_per_file($file_id);
    if (is_wp_error($points_cost)) {
        wp_send_json_error(array('message' => 'Impossibile calcolare il costo in punti: ' . $points_cost->get_error_message()));
        return;
    }

    // Gestione health del servizio Flask
    $api_url = getenv('FLASK_MAP_API_URL_HEALTH');
    $health_check = wp_remote_get($api_url, array('timeout' => 5, 'blocking' => true));

    //Controlla se il servizio è disponibile
    if (is_wp_error($health_check) || (isset($health_check['response']['code']) && intval($health_check['response']['code']) >= 500)) {
        // Servizio non disponibile
        $admins = get_users(array('role' => 'administrator', 'fields' => array('user_email','display_name')));
        $site_name = get_bloginfo('name');
        $current_user = wp_get_current_user();
        $user_email = isset($current_user->user_email) ? $current_user->user_email : '';
        $user_id = get_current_user_id();

        if (is_wp_error($health_check)) {
            $error_details = esc_html($health_check->get_error_message());
            $http_code = 'n/a';
        } else {
            $http_code = isset($health_check['response']['code']) ? intval($health_check['response']['code']) : 'unknown';
            $error_details = 'HTTP status: ' . $http_code;
        }

        $subject = sprintf('[%s] Servizio AI non disponibile', $site_name);

        // Costruzione messaggio Html
        $html_message = '<div style="font-family:Arial,sans-serif;color:#333;">';
        $html_message .= '<h2 style="color:#2c3e50;">Servizio AI non raggiungibile</h2>';
        $html_message .= '<p>Il servizio di generazione mappe concettuali (Flask) non è raggiungibile.</p>';
        $html_message .= '<table cellpadding="6" cellspacing="0" style="border:1px solid #eee;border-collapse:collapse;background:#fafafa;">';
        $html_message .= '<tr><td><strong>URL controllato</strong></td><td>' . esc_html($api_url) . '</td></tr>';
        $html_message .= '<tr><td><strong>Ora</strong></td><td>' . esc_html(date('Y-m-d H:i:s')) . '</td></tr>';
        $html_message .= '<tr><td><strong>Utente</strong></td><td>' . esc_html($user_email) . ' (ID: ' . intval($user_id) . ')</td></tr>';
        $html_message .= '<tr><td><strong>Dettagli errore</strong></td><td>' . esc_html($error_details) . '</td></tr>';
        $html_message .= '</table>';
        $html_message .= '<p>Si prega di verificare lo stato del servizio Flask e riavviarlo se necessario.</p>';
        $html_message .= '</div>';

        // Invio Email in caso di errore
        /*
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $to = implode(',', array_column($admins, 'user_email'));
        wp_mail($to, $subject, $html_message, $headers);*/

        error_log('Flask health check failed: ' . $error_details);

        wp_send_json_error(array('message' => 'Servizio di generazione non disponibile al momento. Riprova più tardi. Nessun punto è stato scalato.'));
        return;
    }

    // Inizializzazione
    $config = array();
    

    // Creazione job in coda, invio job a Flask e salvataggio costo punti
    $result = create_map_generation_job($file_id, $config);
    if(is_wp_error($result)) {
        wp_send_json_error(array('message' => 'Errore nella creazione del job: ' . $result->get_error_message()));
        return;
    }
    $job_id = $result;

    // Controlla se la funzione esiste e aggiorna tabella dei job con il costo in punti del job e aggiorna l'orario di aggiornamento
    if(function_exists('studia_ai_update_job_status')) {
        global $wpdb;
        $table = TABELLA_STUDIA_AI_JOBS;
        $wpdb->update($table, array('points_cost' => intval($points_cost), 'updated_at' => current_time('mysql')), array('id' => $job_id), array('%d', '%s'), array('%d'));
    }

    // Recupera oggetto del sistema punti Pro 
    try {
        $sistema_pro = function_exists('get_sistema_punti') ? get_sistema_punti('pro') : null;
        if (!$sistema_pro){
            throw new Exception('Sistema punti Pro non disponibile');
        }

        // Recupera il tipo di richiesta
        $request_type = isset($config['request_type']) ? $config['request_type'] : 'map';
        //$descrizione = 'AI: Generazione ' . ($request_type === 'map' ? 'mappa' : $request_type);
        
        // Crea array per il log
        $data_log = array(
            'description' => 'AI: Generazione mappa concettuale',
            'hidden_to_user' => false,
        );

        // Rimuove i punti dal wallet dell'utente
        $sistema_pro->rimuovi_punti(get_current_user_id(), intval($points_cost), $data_log);
    } catch (Exception $e) {
        if (function_exists('studia_ai_delete_job')) {
            studia_ai_delete_job($job_id, get_current_user_id());
        }
        wp_send_json_error(array('message' => $e->getMessage()));
        return;
    }

    $existing_job = studia_ai_find_existing_map($file_id, $config, $request_type);

   $message = 'Generazione mappa avvenuta con successo. Puoi continuare a navigare sul sito e controllare lo stato nella sezione "Le mie generazioni".';

   wp_send_json_success(array(
    'job_id' => $result,
    'message' => $message,
    'is_duplicate' => $existing_job ? true : false
   ));
}

// -----------------INVILA IL JOB AL SERVER FLASK-----------------
function send_map_job_to_flask($job_id, $file_id, $config) {
    $file_path = get_attached_file($file_id);

    // Endpoint corretto
    $flask_url = getenv('FLASK_mappe_API_URL');

    /* Credenziali Basic Auth
    $username = 'admin';
    $password = 'password';
    $auth = base64_encode("$username:$password"); */

    // Prepara il payload come richiesto dal servizio Python
    $payload = array(
        'file_path' => $file_path,
        'job_id' => $job_id,
        'callback_url' => home_url('/wp-admin/admin-ajax.php?action=map_completed')
    );

    // Invia la richiesta POST
    $response = wp_remote_post($flask_url, array(
        'body' => json_encode($payload),
        'headers' => array(
            'Content-Type' => 'application/json'
            //'Authorization' => 'Basic ' . $auth
        ),
        'timeout' => 10,
        'blocking' => false // Non attendere la risposta
    ));
    // Log per debug
    error_log('Flask data: ' . json_encode($payload));
    error_log('Flask response: ' . print_r($response, true));
}


 // -----------------CREA UN JOB DI GENERAZIONE MAPPA CONCETTUALE ASINCRONO-----------------
function create_map_generation_job($file_id, $config) {
    $user_id = get_current_user_id();
    if (!is_user_logged_in()) {
        return new WP_Error('user_not_logged', 'Utente non autenticato');
    }

    $file_path = get_attached_file($file_id);
    if (!$file_path || !file_exists($file_path)) {
        return new WP_Error('file_not_found', 'File non trovato');
    }

    $request_type = 'mappe-ai';
    if (isset($config['request_type'])) {
        $request_type = $config['request_type'];
    }

        // Controlla se esiste già una mappa per questo file
    $existing_job = studia_ai_find_existing_map($file_id, $config, $request_type);
    if ($existing_job) {
        // Crea un job duplicato che riutilizza la mappa esistente
        return studia_ai_create_duplicate_job($user_id, $file_id, $config, $existing_job, $request_type);
    }

    $job_id = studia_ai_insert_job(
        $user_id,
        $request_type,
        $file_id,
        $file_path,
        $config,
        0
    );

    if (is_wp_error($job_id)) {
        return $job_id;
    }

    //Invia il job a Flask
    send_map_job_to_flask($job_id, $file_id, $config);

    return $job_id;
}



// -----------------GESTISCE IL CALLBACK DA FLASK QUANDO UNA MAPPA È COMPLETATA-----------------
function handle_map_completed() {
    if (!isset($_POST['job_id']) || !isset($_POST['status'])) {
        wp_send_json_error(['message' => 'Parametri mancanti']);
        return;
    }
    
    // Recupera i parametri
    $job_id = intval($_POST['job_id']);
    $status = sanitize_text_field($_POST['status']);
    
    $additional_data = array();
    
    // Se il job è completato, salva il file
    if ($status === 'completed') {
        // Se è presente result-file (percorso temporaneo) salva il file PNG
        if (isset($_POST['result_file'])) {
            $temp_file = sanitize_text_field($_POST['result_file']);
            $final_path = save_map_png_file($temp_file, $job_id);

            // Se il salvataggio è riuscito, aggiorna i dati del job
            if ($final_path) {
                $additional_data['result_file'] = $final_path;
                $additional_data['result_url'] = str_replace(
                    wp_upload_dir()['basedir'], 
                    wp_upload_dir()['baseurl'], 
                    $final_path
                );
            } else {
                // Se il salvataggio fallisce, marca come errore
                $additional_data['error_message'] = 'Errore nel salvataggio del file PNG';
                $status = 'error';
            }
        }
    }
    // Salva messaggio di errore
    if ($status === 'error' && isset($_POST['error_message'])) {
        $additional_data['error_message'] = sanitize_text_field($_POST['error_message']);
    }

    // Aggiorna lo stato del job nel database
    $result = studia_ai_update_job_status($job_id, $status, $additional_data);
    
    if ($result){
        wp_send_json_success(['message' => 'Job aggiornato con successo']);
    } else {
        wp_send_json_error(['message' => 'Errore nell\'aggiornamento del job']);
    }

}



// -----------------MODIFICA LA DIRECTORY DI UPLOAD PER I FILE DELLE MAPPE-----------------
function upload_directory_protected_maps_files($upload){
    $upload['path'] = $upload['basedir'] . '/protected/ai/maps' . $upload['subdir'];
    // URL visibile nel browser
    $upload['url'] = $upload['baseurl'] . '/protected/ai/maps' . $upload['subdir'];

    $dir = $upload['path'];
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    return $upload;
}



// -----------------SALVA IL FILE PNG DELLA MAPPA-----------------
function save_map_png_file($tempFile_path, $job_id) {
    if (!file_exists($tempFile_path)) {
        return new WP_Error('file_not_found', 'Il file temporaneo non esiste.');
    }
    
    //Applica filtro per mappe
    add_filter('upload_dir', 'upload_directory_protected_maps_files');
    // Recupera la directory di upload
    $upload_dir = wp_upload_dir();
    remove_filter('upload_dir', 'upload_directory_protected_maps_files');

    $final_path = $upload_dir['path'] . '/map_' . $job_id . '_' . time() . '.png';

    // Copia il file nella directory di upload
    if (copy($tempFile_path, $final_path)) {
        chmod($final_path, 0644);
        return $final_path;
    }
    return false;
}

// -----------------TEST FLASK HEALTH-----------------
function handle_test_flask_health() {
    // Controlla che l'utente sia loggato
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }

    // Controlla il nonce per la sicurezza ajax
    check_ajax_referer('studia_con_ai_nonce', 'nonce');

    $flask_url = getenv('FLASK_MAP_API_URL_HEALTH') ?: 'http://localhost:4997/health';
    
    $response = wp_remote_get($flask_url, array(
        'timeout' => 5,
        'blocking' => true
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Errore di connessione: ' . $response->get_error_message()]);
        return;
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($http_code >= 200 && $http_code < 300) {
        wp_send_json_success(['message' => 'Flask attivo (HTTP ' . $http_code . ')', 'response' => $body]);
    } else {
        wp_send_json_error(['message' => 'Flask non risponde correttamente (HTTP ' . $http_code . ')']);
    }
}

// Hook AJAX per WordPress
add_action('wp_ajax_generate_map', 'handle_generate_map');
add_action('wp_ajax_nopriv_generate_map', 'handle_generate_map'); // per utenti non loggati
add_action('wp_ajax_map_completed', 'handle_map_completed');
add_action('wp_ajax_nopriv_map_completed', 'handle_map_completed'); // per utenti non loggati
add_action('wp_ajax_test_flask_health', 'handle_test_flask_health');
add_action('wp_ajax_nopriv_test_flask_health', 'handle_test_flask_health');


