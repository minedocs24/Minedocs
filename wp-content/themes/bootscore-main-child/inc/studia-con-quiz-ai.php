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
    if(!isset($_POST['file_id']) || !isset($_POST['num_questions']) || !isset($_POST['difficulty'])){
        wp_send_json_error(['message' => 'Parametri obbligatori mancanti.']);
        return;
    }

    //4.Recupera il file/numero di domande/difficoltà
    $file_id = intval($_POST['file_id']);
    $num_questions = intval($_POST['num_questions']);
    $difficulty = sanitize_text_field($_POST['difficulty']);

    //5.Conversione: file_id -> file_path
    $file_path = get_attached_file($file_id);
    
    // Debug: log del file_id e file_path
    error_log("Quiz Debug - file_id: $file_id, file_path: $file_path");
    
    if(!$file_path || !file_exists($file_path)){
        error_log("Quiz Error - File non trovato. file_id: $file_id, file_path: $file_path");
        wp_send_json_error(['message' => "File non trovato. ID: $file_id, Path: $file_path"]);
        return;
    }

    //6.Calcola costo in punti
    $points_cost = ai_calcola_prezzo_punti_per_file($file_id);
    if (is_wp_error($points_cost)) {
        wp_send_json_error(array('message' => 'Impossibile calcolare il costo in punti: ' . $points_cost->get_error_message()));
        return;
    }


    /*
    //7.Verifica che il servizio Flask sia raggiungibile
    $api_url = getenv('FLASK_QUIZ_API_URL_HEALTH');
    $health_check = wp_remote_get($api_url, array('timeout' => 5, 'blocking' => true));

    //8.Controlla se il servizio è disponibile
    if (is_wp_error($health_check) || (isset($health_check['response']['code']) && intval($health_check['response']['code']) >= 500)) {
        error_log('Servizio non disponibile.');
        wp_send_json_error(array('message' => 'Servizio non disponibile.'));
        return;
    }*/

    try {
        //7.Generazione del quiz sincrono
        $quiz_result = send_quiz_to_flask($file_id, $num_questions, $difficulty);

        if (is_wp_error($quiz_result)) {
            error_log('Errore nella generazione del quiz: ' . $quiz_result->get_error_message());
            wp_send_json_error(array('message' => 'Errore nella generazione del quiz: ' . $quiz_result->get_error_message()));
            return;
        }

        //8.Salvataggio del quiz
        $quiz_file_path = save_quiz_json($quiz_result, $file_id);
        if (is_wp_error($quiz_file_path)) {
            error_log('Errore nel salvataggio del quiz: ' . $quiz_file_path->get_error_message());
            wp_send_json_error(array('message' => 'Errore nel salvataggio del quiz: ' . $quiz_file_path->get_error_message()));
            return;
        }

        //8.1.Salvataggio del job nella tabella (per tracciamento)
        $job_id = save_quiz_job_to_database($file_id, $file_path, $num_questions, $difficulty, $points_cost, $quiz_file_path);
        if (is_wp_error($job_id)) {
            error_log('Errore nel salvataggio del job quiz: ' . $job_id->get_error_message());

            $job_id = null; // Imposta a null per evitare errori nella risposta
        }

        //9. GESTIONE PUNTI (stesso pattern di studia-schemi-ai.php)
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

            // Rimuove i punti dal wallet dell'utente
            $sistema_pro->rimuovi_punti(get_current_user_id(), intval($points_cost), $data_log);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
            return;
        }

        //10. Invia il file del quiz al client
        wp_send_json_success([
            'message' => 'Generazione quiz avvenuta con successo',
            'quiz_data' => $quiz_result,
            'quiz_file_path' => $quiz_file_path,
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


// Filtro per il percorso di upload dei file del quiz
function upload_directory_protected_quiz_files($upload) {
    error_log('-------------MODIFICA PERCORSO DI UPLOAD QUIZ-------------');
    // Modifica il percorso di upload di WordPress
    $upload['path'] = $upload['basedir'] . '/protected/ai-quiz' . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . '/protected/ai-quiz' . $upload['subdir'];
    
    // Crea la cartella se non esiste
    $dir = $upload['path'];
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }
    
    return $upload; 
}

// Salva il job nella tabella (per tracciamento) (SINCRONO)
function save_quiz_job_to_database($file_id, $file_path, $num_questions, $difficulty, $points_cost, $quiz_file_path) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('user_not_logged', 'Utente non autenticato');
    }
    
    // Prepara i parametri della richiesta
    $config = array(
        'question_number' => $num_questions,
        'difficulty' => $difficulty,
        'points_cost' => $points_cost
    );
    
    // Crea il job come pending (usando la funzione esistente)
    $job_id = studia_ai_insert_job($user_id, 'quiz', $file_id, $file_path, $config, 0);
    if (is_wp_error($job_id)) {
        return $job_id;
    }
    
    // Aggiorna il job come completato (dato che è sincrono)
    $result = studia_ai_update_job_status($job_id, 'completed', array('result_file' => $quiz_file_path));
    if (is_wp_error($result)) {
        return $result;
    }
    
    return $job_id;
}

// Invio del quiz a Flask
 function send_quiz_to_flask($file_id, $question_number, $difficulty){
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
            'timeout' => 40,
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
    if (!isset($response_data['questions']) || !is_array($response_data['questions'])) {
        $error_message = 'Risposta Flask non valida: mancano le domande del quiz';
        error_log($error_message);
        return new WP_Error('invalid_quiz_response', $error_message);
    }

    // Se arriviamo qui, la generazione è andata a buon fine
    error_log('Quiz generato con successo da Flask');
    return $response_data;
 }


// -----------------SALVA IL FILE JSON DEL QUIZ-----------------
function save_quiz_json($quiz_data, $file_id){
    error_log('-------------SALVATAGGIO QUIZ JSON-------------');
    //Applica filtro per quiz
    add_filter('upload_dir', 'upload_directory_protected_quiz_files');
    //Recupera la directory di upload
    $upload_dir = wp_upload_dir();
    remove_filter('upload_dir', 'upload_directory_protected_quiz_files');

    //Genera il nome del file
    $filename = 'quiz_' . $file_id . '_' . time() . '.json';
    $file_path = $upload_dir['path'] . '/' . $filename;
    
    //Salva il file JSON
    $json_content = json_encode($quiz_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($file_path, $json_content) === false) {
        return new WP_Error('file_save_error', 'Errore nel salvataggio del file JSON');
    }

    // Imposta permessi corretti
    chmod($file_path, 0644);

    error_log('Quiz JSON salvato: ' . $file_path);
    return $file_path;
}




