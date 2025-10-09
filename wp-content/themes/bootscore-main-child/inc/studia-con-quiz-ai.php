<?php
// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Assicura che la costante della tabella esista
if (!defined('TABELLA_STUDIA_AI_JOBS')) {
    global $wpdb;
    if (isset($wpdb) && !empty($wpdb->prefix)) {
        define('TABELLA_STUDIA_AI_JOBS', $wpdb->prefix . 'studia_ai_jobs');
    } else {
        define('TABELLA_STUDIA_AI_JOBS', 'wp_studia_ai_jobs');
    }
}




// Generazione del quiz
function handle_generate_quiz() {

    //1.Controlla che l'utente sia loggato
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        return;
    }

    //2.Controlla il nonce per la sicurezza ( DA RIVEDERE--------------------------------)
    check_ajax_referer('nonce_generate_quiz', 'nonce');

    //3.Recupera i parametri del quiz dalla richiesta POST
    if(!isset($_POST['num_questions']) || !isset($_POST['difficulty'])){
        wp_send_json_error(['message' => 'Parametri obbligatori mancanti.']);
        return;
    }

    //4.Recupera il numero di domande/difficoltà
    // Controlla che il numero di domande sia un intero, e sia compreso tra 1 e 20
    $num_questions = intval($_POST['num_questions']);
    if($num_questions < 1 || $num_questions > 20){
        wp_send_json_error(['message' => 'Il numero di domande deve essere compreso tra 1 e 20.']);
        return;
    }
    // Controlla che la difficoltà sia valida
    $difficulty = sanitize_text_field($_POST['difficulty']);
    if($difficulty !== 'easy' && $difficulty !== 'medium' && $difficulty !== 'hard'){
        wp_send_json_error(['message' => 'La difficoltà deve essere facile, media o difficile.']);
        return;
    }

    //5.Conversione: file_id -> file_path
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

    //6.Calcola costo in punti
    $points_cost = ai_calcola_prezzo_punti_per_file($file_id);
    if (is_wp_error($points_cost)) {
        wp_send_json_error(array('message' => 'Impossibile calcolare il costo in punti: ' . $points_cost->get_error_message()));
        return;
    }

    try {
        //7.Crea prima il job nel database per ottenere un job_id univoco
        error_log('Creazione job quiz');
        $job_id = studia_ai_insert_job(get_current_user_id(), 'quiz', $file_id, $file_path, array(
            'question_number' => $num_questions,
            'difficulty' => $difficulty,
            'points_cost' => $points_cost
        ), 0);
        
        if (is_wp_error($job_id)) {
            error_log('Errore nella creazione del job quiz: ' . $job_id->get_error_message());
            wp_send_json_error(array('message' => 'Errore nella creazione del job: ' . $job_id->get_error_message()));
            return;
        }

        //6.1.GESTIONE PUNTI
        try {
            $sistema_pro = function_exists('get_sistema_punti') ? get_sistema_punti('pro') : null;
            if (!$sistema_pro){
                throw new Exception('Sistema punti Pro non disponibile');
            }

            // Determina il nome del file per il log
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
                'description' => 'AI: Generazione quiz per "' . sanitize_text_field($file_name_for_log) . '"',
                'hidden_to_user' => false,
            );

            // Rimuove i punti dal wallet dell'utente SOLO se tutto è andato a buon fine
            $sistema_pro->rimuovi_punti(get_current_user_id(), intval($points_cost), $data_log);
        } catch (Exception $e) {
            if (function_exists('studia_ai_delete_job')) {
                studia_ai_delete_job($job_id, get_current_user_id());
            }
            wp_send_json_error(array('message' => $e->getMessage()));
            return;
        }

        //8.Generazione del quiz 
        $quiz_result = send_quiz_to_flask($file_id, $num_questions, $difficulty, $job_id);

        if (is_wp_error($quiz_result)) {
            error_log('Errore nella generazione del quiz: ' . $quiz_result->get_error_message());
            // Aggiorna il job come fallito
            studia_ai_update_job_status($job_id, 'failed', array('error' => $quiz_result->get_error_message()));
            wp_send_json_error(array('message' => 'Errore nella generazione del quiz: ' . $quiz_result->get_error_message()));
            return;
        }

        $result = studia_ai_update_job_status($job_id, 'completed', array('result_file' => $quiz_result['json_file_path']));

        //10. Filtra la risposta di Flask per rimuovere percorsi sensibili
        $filtered_quiz_data = $quiz_result;
        
        foreach (['json_file_path', 'quiz_file_path', 'path'] as $sensitive_key) {
            if (isset($filtered_quiz_data[$sensitive_key])) {
            unset($filtered_quiz_data[$sensitive_key]);
            }
        }

        //11. Invia il file del quiz al client
        wp_send_json_success([
            'message' => 'Generazione quiz avvenuta con successo',
            'quiz_data' => $filtered_quiz_data,
            'questions_count' => $num_questions,
            'difficulty' => $difficulty,
            'job_id' => $job_id // Aggiunge l'ID del job per tracciamento
        ]);

    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
        return;
    }

    
}
//Hook AJAX per generazione quiz
add_action('wp_ajax_generate_quiz', 'handle_generate_quiz');
add_action('wp_ajax_nopriv_generate_quiz', 'handle_generate_quiz');


// Invio del quiz a Flask
function send_quiz_to_flask($file_id, $question_number, $difficulty, $job_id){
    error_log('-------------INVIO QUIZ A FLASK-------------');
    //1. Conversione file_id -> file_path
    $file_path = get_attached_file($file_id);

    if(!$file_path || !file_exists($file_path)){
        return new WP_Error('file_not_found', 'File non trovato');
    }
    $flask_url = getenv('FLASK_QUIZ_API_URL');
    if(empty($flask_url)){
        error_log('FLASK_QUIZ_API_URL non configurato');
        return new WP_Error('flask_url_not_found', 'URL Flask non configurato');
    }

    //Prepara il payload
    $payload = array(
        'path' => $file_path,
        'num_questions' => $question_number,
        'difficulty' => $difficulty,
        'job_id' => $job_id,
    );
    error_log('Payload: ' . json_encode($payload));
    //Invia la richiesta POST con retry in caso di errori server (500)
    $max_attempts = 5;
    $attempt = 0;
    $backoff_us = 500000; // 0.5s in microsecondi
    $response = null;

    do {
        $attempt++;
        error_log("Invio richiesta a Flask - tentativo {$attempt} di {$max_attempts}");

        $response = wp_remote_post($flask_url, array(
            'body' => json_encode($payload),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 120,
            'blocking' => true,
        ));

        // Log generale della risposta (per debug)
        error_log('Response attempt ' . $attempt . ': ' . print_r($response, true));

        // Se errore di connessione (WP_Error), ritenta fino al massimo
        if (is_wp_error($response)) {
            $error_message = 'Errore di connessione a Flask: ' . $response->get_error_message();
            error_log($error_message);
            if ($attempt >= $max_attempts) {
                return new WP_Error('flask_connection_error', $error_message);
            }
            // backoff esponenziale
            usleep($backoff_us);
            $backoff_us *= 2;
            continue;
        }

        // Controlla il codice di risposta HTTP
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Se server error (5xx) ritenta, altrimenti esci dal loop per gestire la risposta
        if ($http_code >= 500 && $attempt < $max_attempts) {
            error_log("Flask ha restituito errore HTTP {$http_code}, riprovando dopo backoff...");
            usleep($backoff_us);
            $backoff_us *= 2;
            continue;
        }

        // Se codice non 2xx e non è un 5xx rientrato, ritorna errore immediatamente
        if ($http_code < 200 || $http_code >= 300) {
            $error_message = 'Flask ha restituito errore HTTP ' . $http_code . ': ' . $body;
            error_log($error_message);
            return new WP_Error('flask_error', $error_message);
        }

        // Se arriviamo qui, abbiamo una risposta 2xx
        break;

    } while ($attempt < $max_attempts);

    //Decodifica la risposta JSON
    $response_data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $error_message = 'Errore nel parsing JSON della risposta Flask: ' . json_last_error_msg();
        error_log($error_message);
        return new WP_Error('json_parse_error', $error_message);
    }

    // Verifica che la risposta contenga i dati del quiz
    if (!isset($response_data['quiz']) || !is_array($response_data['quiz'])) {
        $error_message = 'Risposta Flask non valida: mancano le domande del quiz';
        error_log($error_message);
        return new WP_Error('invalid_quiz_response', $error_message);
    }

    // Se arriviamo qui, la generazione è andata a buon fine
    error_log('Quiz generato con successo da Flask');
    return $response_data;
 }


/**
 * Restituisce l'URL sicuro per scaricare il JSON del quiz relativo a un job
 */
function handle_get_quiz_download_url() {
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

    // Ottieni il job e verifica appartenenza
    $job = studia_ai_get_job($job_id);
    if (!$job || $job['user_id'] != $user_id) {
        wp_send_json_error(['message' => 'Job non trovato o non autorizzato']);
        return;
    }

    if ($job['status'] !== 'completed') {
        wp_send_json_error(['message' => 'Il quiz non è ancora pronto per il download.']);
        return;
    }

    // Verifica che esista il file risultato
    if (empty($job['result_file'])) {
        wp_die('File non trovato.');
    }
    
    $file_path = $job['result_file'];
    // Normalizza percorso
    $file_path = wp_normalize_path($file_path);

    if (!file_exists($file_path)) {
        error_log('File non esiste per download map: ' . $file_path . ' (job ' . $job_id . ')');
        wp_die('File non trovato.');
    }

    // Determina il nome del file per eventuale log
    $file_name = basename($file_path);

    // Leggi il contenuto JSON del file e restituiscilo al frontend
    $json_content = file_get_contents($file_path);
    if ($json_content === false) {
        wp_send_json_error(['message' => 'Impossibile leggere il file del quiz.']);
        return;
    }

    $response_data = json_decode($json_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'Formato JSON invalido nel file del quiz: ' . json_last_error_msg()]);
        return;
    }

    // Prova a ottenere la difficoltà dai parametri del job, se presente
    $difficulty = null;
    if (!empty($job['request_params'])) {
        $params = json_decode($job['request_params'], true);
        if (json_last_error() === JSON_ERROR_NONE && isset($params['difficulty'])) {
            $difficulty = $params['difficulty'];
        }
    }

    wp_send_json_success([
        'quiz_json' => $response_data,
        'difficulty' => $difficulty,
        'job_id' => $job_id
    ]);
}

add_action('wp_ajax_get_quiz_download_url', 'handle_get_quiz_download_url');
add_action('wp_ajax_nopriv_get_quiz_download_url', 'handle_get_quiz_download_url');
