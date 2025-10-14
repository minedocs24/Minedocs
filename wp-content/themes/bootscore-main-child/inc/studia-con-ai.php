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

//--------------------------ANALISI TESTO ----------------------------------
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

// ------------------------------ GESTIONE JOB ------------------------------

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

// Gestione aggiuntiva dei job
add_action('wp_ajax_get_job_details', 'handle_get_job_details');
add_action('wp_ajax_nopriv_get_job_details', 'handle_get_job_details');

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

// Gestione aggiuntiva dei job
add_action('wp_ajax_delete_job', 'handle_delete_job');
add_action('wp_ajax_nopriv_delete_job', 'handle_delete_job');

/**
 * Restituisce la lista dei job di generazione dell'utente ( DA RIVEDERE CON I VARI SERVIZI )
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

// ------------------------------ GESTIONE DOWNLOAD ------------------------------
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

// ------------------------------ GESTIONE PREZZO GENERAZIONE ------------------------------


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

    // GESTIONE FILE
    $file_data = get_file_path();
    if (!$file_data || !is_array($file_data) || empty($file_data['file_path'])) {
        if (is_callable($notify_admin)) {
            $notify_admin('Parametro file non specificato o file non trovato (helper get_file_path)');
        }
        wp_send_json_error(['message' => 'File del documento non trovato']);
        return;
    }

    $file_path = $file_data['file_path'];

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
 * Helper per calcolare i punti partendo da file_id allegato
 */
function ai_calcola_prezzo_punti_per_file($file_id) {
    $path = get_attached_file(intval($file_id));
    if (!$path || !file_exists($path)) {
        return new WP_Error('file_not_found', 'File del documento non trovato');
    }
    return ai_calcola_prezzo_punti($path);
}

// ------------------------------ GESTIONE FUNZIONI GENERALIZZATE ------------------------------

function get_file_path() {
    $file_path = '';
    $file_id = null;
    error_log('DEBUG get_file_path: $_POST = ' . print_r($_POST, true));

    if (isset($_POST['document_id']) && !empty($_POST['document_id'])) {
        error_log('DEBUG: Processing document_id: ' . $_POST['document_id']);
        // Documento già presente in piattaforma
        $document_id = sanitize_text_field($_POST['document_id']);
        $product_id = get_product_id_by_hash($document_id);
        
        if (!$product_id) {
            #wp_send_json_error(array('message' => 'Documento non trovato'));
            error_log('DEBUG: Product ID not found for document_id: ' . $document_id);
            return;
        }
        
        // Recupera il file_id dal prodotto
        $file_anteprima_id = get_post_meta($product_id, '_file_anteprima', true);
        if (!$file_anteprima_id) {
            #wp_send_json_error(array('message' => 'File del documento non trovato'));
            error_log('DEBUG: File anteprima ID not found for product_id: ' . $product_id);
            return;
        }
        
        $file_id = intval($file_anteprima_id);
        $file_path = get_attached_file($file_id);
        
        if (!$file_path || !file_exists($file_path)) {
            #wp_send_json_error(array('message' => 'File del documento non trovato'));
            error_log('DEBUG: File path not found for file_id: ' . $file_id);
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
            #wp_send_json_error(array('message' => 'File del documento non trovato'));
            error_log('DEBUG: File path not found for file_id: ' . $file_id);
            return;
        }

        $file_id = intval($_POST['file_id']);
        $file_path = get_attached_file($file_id);

        if (!$file_path || !file_exists($file_path)) {
            #wp_send_json_error(array('message' => 'File del documento non trovato'));
            return;
        }
    }

    return array(
        'file_id' => $file_id,
        'file_path' => $file_path
    );
}


function ai_get_request_label($request_type) {
    $type_to_label = array(
        'summary' => 'riassunto',
        'mappa' => 'mappa concettuale',
        'quiz' => 'quiz',
        'evidenza' => 'evidenza',
        'interroga' => 'interroga'
    );
    return isset($type_to_label[$request_type]) ? $type_to_label[$request_type] : $request_type;
}
//FLASK HEALTH CHECK
function check_health($service_type) {
    // Verifica che l'endpoint Flask sia raggiungibile prima di creare il job e scalare punti
    $services = array(
        'summary' => FLASK_SUMMARY_API_URL,
        'mappa' => FLASK_MAP_API_URL_HEALTH,
        'quiz' => FLASK_QUIZ_API_URL_HEALTH,
        #'evidenza' => FLASK_EVIDENZA_API_URL,
        #'interroga' => FLASK_INTERROGA_API_URL
    );
    $flask_url = $services[$service_type];
    $label = function_exists('ai_get_request_label')
    ? ai_get_request_label($service_type)
    : $service_type;

    // Controlla se il servizio è disponibile
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
        $html_message .= '<p>Il servizio di generazione ' . esc_html($label) . ' (Flask) non è raggiungibile.</p>';
        $html_message .= '<table cellpadding="6" cellspacing="0" style="border:1px solid #eee;border-collapse:collapse;background:#fafafa;">';
        $html_message .= '<tr><td><strong>URL controllato</strong></td><td>' . esc_html($flask_url) . '</td></tr>';
        $html_message .= '<tr><td><strong>Ora</strong></td><td>' . esc_html(date('Y-m-d H:i:s')) . '</td></tr>';
        $html_message .= '<tr><td><strong>Utente</strong></td><td>' . esc_html($user_email) . ' (ID: ' . intval($user_id) . ')</td></tr>';
        $html_message .= '<tr><td><strong>Dettagli errore</strong></td><td>' . esc_html($error_details) . '</td></tr>';
        $html_message .= '</table>';
        $html_message .= '<p>Si prega di verificare lo stato del servizio Flask ' . esc_html($label) . ' e riavviarlo se necessario.</p>';
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

        #wp_send_json_error(array('message' => 'Servizio di generazione non disponibile al momento. Riprova più tardi. Nessun punto è stato addebitato.'));
        return;
    }
    return true;
}

/**
 * Ottiene un nome leggibile per il file da usare nei log
 * Priorità: titolo prodotto > titolo allegato > basename del file
 */
function get_readable_file_name_for_log($file_id, $file_path = '') {
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
        if (empty($file_name_for_log) && !empty($file_path)) {
            $file_name_for_log = basename($file_path);
        }
    } catch (Exception $e) {
        // se qualcosa va storto, fallback al basename
        $file_name_for_log = !empty($file_path) ? basename($file_path) : 'File ID ' . $file_id;
    }
    
    return $file_name_for_log;
}

// DEDUZIONE PUNTI PRO
function deduci_punti_pro($file_id, $file_path, $job_id, $points_cost, $config = array()){
    
    try {
        $sistema_pro = function_exists('get_sistema_punti') ? get_sistema_punti('pro') : null;
        if (!$sistema_pro) {
            throw new Exception('Sistema punti Pro non disponibile');
        }

        $request_type = isset($config['request_type']) ? $config['request_type'] : 'summary';
        $label = function_exists('ai_get_request_label')
        ? ai_get_request_label($request_type)
        : $request_type;
        $descrizione = 'AI: Generazione ' . $label;

        // Usa la funzione helper per ottenere il nome leggibile
        $file_name_for_log = get_readable_file_name_for_log($file_id, $file_path);

        $data_log = array(
            'description' => $descrizione . ' per "' . sanitize_text_field($file_name_for_log) . '"',
            'hidden_to_user' => false,
        );

        // Se lancia eccezione per punti insufficienti o altro, verrà gestita dal catch
        $sistema_pro->rimuovi_punti(get_current_user_id(), intval($points_cost), $data_log);
        return true;
    } catch (Exception $e) {
        // Elimina il job creato se la decurtazione fallisce
        if (function_exists('studia_ai_delete_job')) {
            studia_ai_delete_job($job_id, get_current_user_id());
        }
        return new WP_Error('points_deduction_failed', $e->getMessage());
    }
}

