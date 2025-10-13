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
    error_log('handle_generate_map');
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

    parse_str($_POST['config'], $config);

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

    // Calcola il costo in punti PRO lato server
    $points_cost = ai_calcola_prezzo_punti_per_file($file_id);
    if (is_wp_error($points_cost)) {
        wp_send_json_error(array('message' => 'Impossibile calcolare il costo in punti: ' . $points_cost->get_error_message()));
        return;
    }

    // Verifica che l'utente abbia abbastanza punti pro
    $user_points_pro = get_points_pro_utente(get_current_user_id());
    if ($user_points_pro < $points_cost) {
        wp_send_json_error(array('message' => 'Non hai abbastanza punti pro per generare la mappa concettuale.'));
        return;
    }

    // Gestione health del servizio Flask
    $api_url = FLASK_MAP_API_URL_HEALTH;
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
        $html_message .= '<p>Si prega di verificare lo stato del servizio Flask MindMap e riavviarlo se necessario.</p>';
        $html_message .= '</div>';

        // Invio Email in caso di errore
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

        wp_send_json_error(array('message' => 'Servizio di generazione non disponibile al momento. Riprova più tardi. Nessun punto è stato addebitato.'));
        return;
    }

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
        $request_type = isset($config['request_type']) ? $config['request_type'] : 'mindmap';
        //$descrizione = 'AI: Generazione ' . ($request_type === 'mindmap' ? 'Mappa concettuale' : $request_type);
        
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

        // Crea array per il log
        $data_log = array(
            'description' => 'AI: Generazione mappa concettuale per "' . sanitize_text_field($file_name_for_log) . '"',
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

// -----------------INVIA JOB AL SERVER FLASK-----------------
function send_map_job_to_flask($job_id, $file_id, $config) {
    $file_path = get_attached_file($file_id);

    // Endpoint corretto
    $flask_url = FLASK_MAP_API_URL;
    
    // Verifica che l'URL sia configurato
    if (empty($flask_url)) {
        error_log('FLASK_MAP_API_URL non configurato');
        studia_ai_update_job_status($job_id, 'error', array('error_message' => 'URL Flask non configurato'));
        return false;
    }

    /* Credenziali Basic Auth
    $username = 'admin';
    $password = 'password';
    $auth = base64_encode("$username:$password"); */

    // Prepara il payload come richiesto dal servizio Python
    $payload = array(
        'path' => $file_path,
        'job_id' => $job_id,
        'callback_url' => home_url('/wp-admin/admin-ajax.php?action=map_completed')
    );

    // Invia la richiesta POST con gestione errori
    $response = wp_remote_post($flask_url, array(
        'body' => json_encode($payload),
        'headers' => array(
            'Content-Type' => 'application/json'
            //'Authorization' => 'Basic ' . $auth
        ),
        'timeout' => 5, 
        'blocking' => false // Non attendere la risposta 
    ));

    // Se arriviamo qui, la richiesta è andata a buon fine
    error_log('Job inviato a Flask con successo');
    return true;
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

    $request_type = 'mindmap';
    if (isset($config['request_type'])) {
        $request_type = $config['request_type'];
    }

    // Controlla se esiste già una mappa per questo file
    $existing_job = studia_ai_find_existing_map($file_id, $config, $request_type);
    if ($existing_job) {
        // Crea un job duplicato che riutilizza la mappa esistente
        return studia_ai_create_duplicate_job($user_id, $file_id, $config, $existing_job, $request_type);
    }

    // Non esiste una mappa con questa configurazione, creane una nuova
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
    $flask_result = send_map_job_to_flask($job_id, $file_id, $config);
    
    // Se l'invio a Flask fallisce, il job è già stato marcato come errore
    if (!$flask_result) {
        error_log('Invio job a Flask fallito per job_id: ' . $job_id);
        return new WP_Error('flask_send_failed', 'Impossibile inviare il job a Flask');
    }

    return $job_id;
}



// -----------------GESTISCE IL CALLBACK DA FLASK QUANDO UNA MAPPA È COMPLETATA-----------------
function handle_map_completed() {
    $raw_input = file_get_contents('php://input');
    error_log('Callback Flask ricevuto per mappa: ' . $raw_input);

    $data = $_POST;
    if (empty($data) && !empty($raw_input)) {
        $json_data = json_decode($raw_input, true);
        if ($json_data) {
            $data = $json_data;
            error_log('Callback Flask: dati JSON decodificati: ' . print_r($data, true));
        }
    }

    if (!isset($data['job_id']) || !isset($data['status'])) {
        wp_send_json_error(['message' => 'Parametri mancanti']);
        return;
    }

    $job_id = intval($data['job_id']);
    $status = sanitize_text_field($data['status']);
    $additional_data = array();

    if ($status === 'completed') {
        if (isset($data['png_file'])) {
            error_log('Callback Flask: png_file: ' . $data['png_file']);
            $png_file = sanitize_text_field($data['png_file']);
            error_log('Callback Flask: png_file_absolute: ' . $png_file);
            $png_file_absolute = $png_file;

            // Crea URL di download per il PDF (assumendo che sia accessibile via web)
            $pdf_url = '';
            if ($png_file) {
                // Costruisci l'URL assumendo che i file siano serviti da Flask
                $pdf_url = FLASK_SUMMARY_API_URL_DOWNLOAD . urlencode($png_file);
            }
            error_log('Callback Flask: pdf_url: ' . $pdf_url);
            // Prepara i dati aggiuntivi per l'aggiornamento
            $additional_data = array(
                'result_file' => $png_file,
                'result_url' => $pdf_url
            );
            
            // Aggiorna il job come completato
            $result = studia_ai_update_job_status($job_id, 'completed', $additional_data);

            if ($result) {
                wp_send_json_success([
                    'message' => 'Job completato con successo',
                    'job_id' => $job_id,
                    'pdf_path' => $png_file,
                    'pdf_url' => $pdf_url
                ]);
            } else {
                error_log("Errore nell'aggiornamento del job $job_id");
                wp_send_json_error(['message' => "Errore nell'aggiornamento del job $job_id"]);
            }
        }
        
    } else {
        // Errore nell'elaborazione
        $error_message = 'Errore sconosciuto nell\'elaborazione';
        
        // Aggiorna il job come errore
        $additional_data = array(
            'error_message' => $error_message
        );
        
        $result = studia_ai_update_job_status($job_id, 'error', $additional_data);
        if ($result) {
            error_log("Job $job_id completato con errore: $error_message");
            wp_send_json_success([
                'message' => 'Job aggiornato con errore',
                'job_id' => $job_id,
                'error' => $error_message
            ]);
        } else {
            error_log("Errore nell'aggiornamento del job $job_id con errore");
            wp_send_json_error(['message' => "Errore nell'aggiornamento del job $job_id"]);
        }
    }

}



// -----------------MODIFICA LA DIRECTORY DI UPLOAD PER I FILE DELLE MAPPE-----------------
function upload_directory_protected_maps_files($upload){
    $upload['path'] = $upload['basedir'] . '/protected/ai-maps' . $upload['subdir'];
    // URL visibile nel browser
    $upload['url'] = $upload['baseurl'] . '/protected/ai-maps' . $upload['subdir'];

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

    $flask_url = FLASK_MAP_API_URL_HEALTH;
    $response = wp_remote_get($flask_url, array(
        'timeout' => 10,
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


