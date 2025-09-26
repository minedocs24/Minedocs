<?php


function genera_pdf_prime_n_pagine($file, $output_folder_path, $n_pages) {
    error_log($file);
    error_log($output_folder_path);
    $output_folder_path = str_replace('.pdf', '_preview.pdf', $output_folder_path);
    $pdf = new \setasign\Fpdi\Fpdi();


    $pageCount = $pdf->setSourceFile($file);

    //$pageCount = $pdf->setSourceFile($file);
    $pagesToExtract = min($n_pages, $pageCount);

    for ($pageNo = 1; $pageNo <= $pagesToExtract; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
    }

    // Add two random extra pages
    if($pageCount > 3){
        $randomPages = array_rand(range(1, $pageCount), 2);
        foreach ($randomPages as $randomPageNo) {
            $templateId = $pdf->importPage($randomPageNo+1);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }
    }

    $pdf->Output('F', $output_folder_path);

    // Create attachment for the generated PDF
    $preview_file_name = basename($output_folder_path);    
    $upload = array(
        'file' => $output_folder_path,
        'url' => str_replace(wp_upload_dir()['basedir'], wp_upload_dir()['baseurl'], $output_folder_path),
        'type' => 'application/pdf'
    );
    error_log(print_r($upload, true));
    $file = array(
        'name' => $preview_file_name //post title
    );
    return create_attachments($upload, $file);
}

#add_action('admin_post_upload_file', 'handle_file_upload');
#add_action('admin_post_nopriv_upload_file', 'handle_file_upload');

function upload_directory_protected_purchaseable_files($upload) {
    //$upload['subdir'] = '/protected/purchaseable_files' . $upload['subdir'];  // Aggiunge una sottocartella personalizzata
    $upload['path'] = $upload['basedir'] . '/protected/purchaseable_files' . $upload['subdir']; // Percorso fisico
    $upload['url'] = $upload['baseurl'] . '/protected/purchaseable_files' . $upload['subdir'];  // URL visibile nel browser

    return $upload;
}

/**
 * Creates an attachment in WordPress from an uploaded file.
 *
 * @param array $upload An array containing the URL, file path, and type of the uploaded file.
 * @param array $file An array containing the name of the uploaded file.
 * @return int The attachment ID.
 */
function create_attachments($upload, $file){
    $attachment = array(
        'guid' => $upload['url'],
        'post_mime_type' => $upload['type'],
        'post_title' => sanitize_file_name($file['name']),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    // Inserisci l'allegato nel database
    $attach_id = wp_insert_attachment($attachment, $upload['file']);


    if (is_wp_error($attach_id)) {
        error_log(print_r($attach_id));
        return new WP_Error('attachment_error', 'Errore durante la creazione dell\'attachment.');
    }


    // Genera i metadati per l'allegato
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    error_log(print_r($attach_data, true));

$info = gd_info();
// var_dump($info['WebP Support']);
error_log(print_r($info, true));

    wp_update_attachment_metadata($attach_id, $attach_data);
    return $attach_id;
}

/**
 * Creates a thumbnail from image blob data.
 *
 * @param string $blob_data The blob data of the image.
 * @param string $thumbnail_path The path where the thumbnail should be saved.
 * @return int The attachment ID of the created thumbnail.
 */
function create_thumbnail_from_blob($blob_data, $thumbnail_path) {
    // Decode the blob data
    $image_data = base64_decode($blob_data);

    // Save the image data to a file
    file_put_contents($thumbnail_path, $image_data);

    // Create attachment for the generated thumbnail
    $upload = array(
        'file' => $thumbnail_path,
        'url' => str_replace(wp_upload_dir()['basedir'], wp_upload_dir()['baseurl'], $thumbnail_path),
        'type' => 'image/webp'
    );
    $file = array(
        'name' => basename($thumbnail_path) // post title
    );
    error_log('create_thumbnail_from_blob');
    //error_log(print_r($upload, true));
    error_log(print_r($file, true));
    return create_attachments($upload, $file);
}


// Funzione per acquisire i parametri dal POST
function get_upload_document_params() {
    $user_id = get_current_user_id();

    // Check se uploadChoice diverso da 'modify' (caso upload e update) e non sono presenti file_id e preview_file_id, errore
    if (sanitize_text_field( $_POST['uploadChoice']) !== 'modify' && (empty($_POST['file_id']) || empty($_POST['preview_file_id']))) {
        throw new Exception('File non trovato.');
    } elseif (sanitize_text_field( $_POST['uploadChoice']) === 'modify' && (!empty($_POST['file_id']) || !empty($_POST['preview_file_id']))) {
        // Check se uploadChoice è 'modify' e almeno uno fra file_id e preview_file_id non è vuoto, errore
        throw new Exception('File non trovato.');
    } elseif (sanitize_text_field( $_POST['uploadChoice']) !== 'modify' && (!empty($_POST['file_id']) && !empty($_POST['preview_file_id']))) {
        // Caso legittimo di upload e update con file_id e preview_file_id
        $hashed_file_id = sanitize_text_field($_POST['file_id']);
        $file_id = get_attachment_id_by_hash($hashed_file_id);

        if (!$file_id) {
            throw new Exception('File non trovato.');
        }

        // Recupera l'attachment del file
        $file = get_post($file_id);
        if (!$file) {
            throw new Exception('File non trovato.');
        }

        if ($file->post_author != $user_id) {
            throw new Exception('Non sei autorizzato a caricare questo file.');
        }

        $hashed_preview_file_id = sanitize_text_field($_POST['preview_file_id']);
        if (!$hashed_preview_file_id) {
            throw new Exception('Anteprima non trovata.');
        }
        $preview_file_id = get_attachment_id_by_hash($hashed_preview_file_id);

        if (!$preview_file_id) {
            throw new Exception('Anteprima non trovata.');
        }
        $preview_file = get_post($preview_file_id);
        if (!$preview_file) {
            throw new Exception('Anteprima non trovata.');
        }
        if ($preview_file->post_author != $user_id) {
            throw new Exception('Non sei autorizzato a caricare questa anteprima.');
        }

    } elseif (sanitize_text_field( $_POST['uploadChoice']) === 'modify' && (empty($_POST['file_id']) || empty($_POST['preview_file_id']))) {
        $file_id = null;
        $preview_file_id = null;
    } else {
        throw new Exception('Error processing request.');
    }

    
    return array(
        'upload_choice' => sanitize_text_field($_POST['uploadChoice']),
        'post_id' => sanitize_text_field($_POST['post_id']),
        'titolo' => sanitize_text_field($_POST['titolo']),
        'descrizione' => sanitize_text_field($_POST['descrizione']),
        'universita' => sanitize_text_field($_POST['universita']),
        'corsoDiLaurea' => sanitize_text_field($_POST['corsoDiLaurea']),
        'materia' => sanitize_text_field($_POST['materia']),
        'annoAccademico' => sanitize_text_field($_POST['annoAccademico']),
        'tipoDocumento' => sanitize_text_field($_POST['tipoDocumento']),
        'modalita' => sanitize_text_field($_POST['modalita']),
        'prezzo' => sanitize_text_field($_POST['prezzo']),
        'file_id' => $file_id,
        'n_pages' => sanitize_text_field($_POST['n_pages']),
        'preview_file_id' => $preview_file_id,
        'thumbnail_data' => create_thumbnail($file_id)//$_POST['thumbnail']
    );
}

function create_thumbnail($file_id) {
    // Ottieni il percorso assoluto del file PDF dal media ID
    $file_path = get_attached_file($file_id);

    if (!file_exists($file_path)) {
        return new WP_Error('file_not_found', 'Il file non esiste.');
    }

    // Inizializza CURL
    $ch = curl_init();

    // Crea un array per il file da inviare via POST
    $post_fields = [
        'file' => new CURLFile($file_path, 'application/pdf', basename($file_path))
    ];
    error_log("Post fields");
    error_log(print_r($post_fields, true));

    curl_setopt_array($ch, [
        CURLOPT_URL => 'http://localhost:5001/upload-pdf',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post_fields,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    error_log("Response");
    error_log(print_r($response, true));
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code !== 200) {
        return new WP_Error('thumbnail_error', 'Errore nella generazione della miniatura: ' . $error);
    }
    $data = json_decode($response, true);
    if (!isset($data['image_base64'])) {
        return new WP_Error('invalid_response', 'La risposta non contiene l\'immagine.');
    }

    // return $response; // stream binario della WebP
    return $data['image_base64'];
}


/**
 * Funzione che si occupa di validare i parametri di upload del documento in caso di primo caricamento o di aggiornamento
 * del documento.
 * Controlla che i campi obbligatori siano presenti e che i valori siano validi.
 *
 * @param array $input The input array to validate.
 * @return array An array with 'success' => true if validation passes, or 'success' => false with 'errors'.
 */
function validate_upload_document_params($input) {
    $errors = [];
    // error_log("Validating input: " . print_r($input, true));

    // Required fields
    $required_fields = [
        'upload_choice', 'titolo', 'descrizione', 'universita', 'corsoDiLaurea',
        'materia', 'annoAccademico', 'tipoDocumento', 'modalita', 'prezzo', 'file_id', 'n_pages', 'preview_file_id'
    ];


    // Check for empty fields
    foreach ($required_fields as $field) {
        if ($field === 'prezzo') {
            if ($input['modalita'] === 'condividi' && !empty($input['prezzo'])) {
                $errors[] = "Il campo 'prezzo' deve essere vuoto quando la modalità è 'condividi'.";
            }
            continue; // Skip further checks for 'prezzo'
        }
        if (empty($input[$field])) {
            // Skip validation for 'file_id', 'n_pages', and 'preview_file_id' if upload_choice is 'modify'
            if ($input['upload_choice'] === 'modify' && in_array($field, ['file_id', 'n_pages', 'preview_file_id'])) {
                if (!empty($input[$field])) {
                    $errors[] = "Il campo '$field' deve essere vuoto quando l'opzione di upload è 'modify'.";
                }
                continue;
            }
            $errors[] = "Il campo '$field' è obbligatorio.";
        }
    }

    // Validate specific fields
    $valid_tipo_documento = ['paniere', 'riassunto', 'schema'];
    $valid_modalita = ['vendi', 'condividi'];
    $valid_upload_choice = ['uploadNewVersion', 'upload', 'modify'];
    $input['annoAccademico'] = str_replace('/', '-', $input['annoAccademico']);
    $valid_prezzi = get_prezzi_punti_pro();

    if (!in_array($input['upload_choice'], $valid_upload_choice)) {
        $errors[] = "Il campo 'upload_choice' deve essere uno dei seguenti valori: " . implode(', ', $valid_upload_choice) . ".";
    }

    if (!in_array(strtolower($input['tipoDocumento']), $valid_tipo_documento)) {
        $errors[] = "Il campo 'tipoDocumento' deve essere uno dei seguenti valori: " . implode(', ', $valid_tipo_documento) . ".";
    }

    if (!in_array($input['modalita'], $valid_modalita)) {
        $errors[] = "Il campo 'modalita' deve essere uno dei seguenti valori: " . implode(', ', $valid_modalita) . ".";
    }

    if ($input['modalita'] === 'vendi') {
        if (!isset($input['prezzo']) || !is_numeric($input['prezzo']) || $input['prezzo'] <= 0) {
            $errors[] = "Il campo 'prezzo' deve essere maggiore di zero quando la modalità è 'vendi'.";
        } elseif (!in_array($input['prezzo'], $valid_prezzi)) {
            $errors[] = "Il campo 'prezzo' deve essere uno dei seguenti valori consentiti: " . implode(', ', $valid_prezzi) . ".";
        }
    }

    if ($input['upload_choice'] !== 'modify' && (!is_numeric($input['n_pages']) || $input['n_pages'] <= 0)) {
        $errors[] = "Il campo 'n_pages' deve essere un numero maggiore di zero.";
    }

    // valida il titolo in modo che non sia più lungo di 100 caratteri e non abbia caratteri speciali al suo interno
    if (strlen($input['titolo']) > 100) {
        $errors[] = "Il campo 'titolo' non può essere più lungo di 100 caratteri.";
    }
    if (!preg_match('/^[a-zA-Z0-9\s\-()àèéìòùÀÈÉÌÒÙ\']+$/', $input['titolo'])) {
        $errors[] = "Il campo 'titolo' può contenere solo lettere, numeri, spazi, apici, parentesi tonde e lettere accentate.";
    }
    
    // valida la descrizione in modo che non sia più lunga di 500 caratteri e non abbia caratteri speciali al suo interno
    if (strlen($input['descrizione']) > 500) {
        $errors[] = "Il campo 'descrizione' non può essere più lungo di 500 caratteri.";
    }
    if (!preg_match('/^[a-zA-Z0-9\s.,;:!?\'"()àèéìòùÀÈÉÌÒÙ\-]+$/', $input['descrizione'])) {
        $errors[] = "Il campo 'descrizione' può contenere solo lettere, numeri, spazi, caratteri di punteggiatura di base, lettere accentate e parentesi tonde.";
    }

    // valida l'anno accademico recuperando tutti i termini dalla tassonomia anno accademico e verificando che il valore impostato sia fra quelli della tassonomia
    $terms = get_terms(array(
        'taxonomy' => 'anno_accademico',
        'hide_empty' => false,
    ));
    $valid_anno_accademico = array_map(function($term) {
        return $term->slug;
    }, $terms);
    // error_log("Valid anno accademico: " . print_r($valid_anno_accademico, true));
    if (!in_array($input['annoAccademico'], $valid_anno_accademico)) {
        $errors[] = "Il campo 'annoAccademico' deve essere uno dei seguenti valori: " . implode(', ', $valid_anno_accademico) . ".";
    }
    // Validate 'universita', 'corsoDiLaurea', and 'materia' fields using the provided regex
    $regexTermini = '/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ\-]+(?<!\-|\s)$/';

    if (!preg_match($regexTermini, $input['universita'])) {
        $errors[] = "Il campo 'università' contiene caratteri non validi.";
    }

    if (!preg_match($regexTermini, $input['corsoDiLaurea'])) {
        $errors[] = "Il campo 'corso di laurea' contiene caratteri non validi.";
    }

    if (!preg_match($regexTermini, $input['materia'])) {
        $errors[] = "Il campo 'materia' contiene caratteri non validi.";
    }



    // Check for changes if upload_choice is 'modify'
    // if ($input['upload_choice'] === 'modify') {
    //     error_log("Upload choice is 'modify'.");
    //     $post_id = get_product_id_by_hash($input['post_id']);
    //     $post = get_post($post_id);
    //     if (empty($post_id)) {
    //         $errors[] = "Il campo 'post_id' è obbligatorio per 'modify'.";
    //     } else {
    //         $existing_post = get_post($post_id);
    //         if (!$existing_post) {
    //             $errors[] = "Il post con ID $post_id non esiste.";
    //         } else {
    //             // Compare fields with existing post meta or taxonomy terms
    //             $fields_to_check = [
    //                 'titolo' => 'property:name', // Recupera il nome del prodotto
    //                 'descrizione' => 'property:description', // Recupera la descrizione del prodotto
    //                 'universita' => 'taxonomy:nome_istituto',
    //                 'corsoDiLaurea' => 'taxonomy:nome_corso_di_laurea',
    //                 'materia' => 'taxonomy:nome_corso',
    //                 'annoAccademico' => 'taxonomy:anno_accademico',
    //                 'tipoDocumento' => 'taxonomy:tipo_prodotto',
    //                 'modalita' => 'taxonomy:modalita_pubblicazione',
    //                 'prezzo' => 'conditional_meta' // Prezzo dipende dalla modalità
    //             ];

    //             $all_fields_unchanged = true; // Flag per verificare se tutti i campi sono invariati

    //             foreach ($fields_to_check as $field => $type) {
    //                 if (strpos($type, 'meta') === 0) {
    //                     error_log("Checking meta field: $field");
    //                     // Check post meta
    //                     $existing_value = get_post_meta($post_id, $field, true);
    //                     if ($existing_value !== $input[$field]) {
    //                         $all_fields_unchanged = false; // Almeno un campo è stato modificato
    //                         $errors[] = "Il campo '$field' è stato modificato.";
    //                         break;
    //                     }
    //                 } elseif (strpos($type, 'taxonomy:') === 0) {
    //                     error_log("Checking taxonomy field: $field");
    //                     // Extract taxonomy name
    //                     $taxonomy = explode(':', $type)[1];

    //                     // Check taxonomy terms
    //                     $existing_terms = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'names']);
    //                     if (!is_wp_error($existing_terms) && !in_array($input[$field], $existing_terms)) {
    //                         $all_fields_unchanged = false; // Almeno un campo è stato modificato
    //                         $errors[] = "Il campo '$field' è stato modificato.";
    //                         break;
    //                     }
    //                 } elseif ($type === 'conditional_meta' && $field === 'prezzo') {
    //                     error_log("Checking conditional meta field: $field");
    //                     // Check prezzo based on modalita_pubblicazione
    //                     $modalita = wp_get_post_terms($post_id, 'modalita_pubblicazione', ['fields' => 'slugs']);
    //                     if (!is_wp_error($modalita) && !empty($modalita)) {
    //                         $modalita = $modalita[0]; // Assume un solo valore per la tassonomia
    //                         $meta_key = $modalita === 'vendi' ? '_costo_in_punti_pro' : '_costo_in_punti_blu';
    //                         $existing_value = get_post_meta($post_id, $meta_key, true);

    //                         if ($existing_value !== $input['prezzo']) {
    //                             $all_fields_unchanged = false; // Almeno un campo è stato modificato
    //                             $errors[] = "Il campo 'prezzo' è stato modificato.";
    //                             break;
    //                         }
    //                     }
    //                 } elseif ($type === 'property:name') {
    //                     error_log("Checking property field: $field");
    //                     // Check post title
    //                     if ($post->post_title !== $input[$field]) {
    //                         $all_fields_unchanged = false; // Almeno un campo è stato modificato
    //                         $errors[] = "Il campo '$field' è stato modificato.";
    //                         break;
    //                     }
    //                 } elseif ($type === 'property:description') {
    //                     error_log("Checking property field: $field");
    //                     // Check post content
    //                     if ($post->post_content !== $input[$field]) {
    //                         $all_fields_unchanged = false; // Almeno un campo è stato modificato
    //                         $errors[] = "Il campo '$field' è stato modificato.";
    //                         break;
    //                     }
    //                 }
    //             }

    //             // Se tutti i campi sono invariati, segnala un errore
    //             if ($all_fields_unchanged) {
    //                 $errors[] = "Nessun campo è stato modificato rispetto alla versione precedente.";
    //             }
    //         }
    //     }
    // }

    error_log("Errori: " . implode(', ', $errors));

    // Return validation result
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    return ['success' => true];
}

// Funzione per creare il documento (chiamata o in creazione primo documento o in aggiornamento documento con nuova versione)
function create_document($params) {
    // Crea la thumbnail
    $thumbnail_data = str_replace('data:image/webp;base64,', '', $params['thumbnail_data']);
    $thumbnail_data = str_replace(' ', '+', $thumbnail_data);
    $thumbnail_id = create_thumbnail_from_blob($thumbnail_data, wp_upload_dir()['basedir'] . '/' . $params['file_id'] . '_' . sanitize_title($params['titolo']) . '.webp');
    $post_id = $params['post_id'];

    $validation_result = validate_upload_document_params($params);
    if (!$validation_result['success']) {
        throw new Exception(implode(', ', $validation_result['errors']));
    } 

    if ($params['upload_choice'] === 'uploadNewVersion') {
        $product_base_id = get_post_meta($post_id, '_id_base_prodotto', true);
        $version_number = intval(get_post_meta($post_id, '_numero_versione', true)) + 1;
        // Controlla che il file non sia in approvazione o rifiutato
        $stato_approvazione = get_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true);
        error_log('Stato approvazione: ' . $stato_approvazione);
        if ($stato_approvazione == 'in_approvazione' || $stato_approvazione == 'non_approvato') {
            throw new Exception('Il file è in approvazione o rifiutato. Non puoi modificarlo.');
        }
    } else {
        $product_base_id = uniqid();
        $version_number = 1;
    }



    // Crea un array con i dati del post
    $post_data = array(
        'post_title' => $params['titolo'],
        'post_content' => $params['descrizione'],
        'universita' => $params['universita'],
        'corso_di_laurea' => $params['corsoDiLaurea'],
        'materia' => $params['materia'],
        'anno_accademico' => $params['annoAccademico'],
        'tipo_documento' => $params['tipoDocumento'],
        'modalita' => $params['modalita'],
        'prezzo' => $params['prezzo'],
        'file_id' => $params['file_id'],
        'n_pages' => $params['n_pages'],
        'preview_file_id' => $params['preview_file_id'],
        'thumbnail_id' => $thumbnail_id,
        'product_base_id' => $product_base_id,
        'version_number' => $version_number,
        'skip_approval' => $params['skip_approval']
    );

    $id_prodotto = crea_prodotto_virtuale_scaricabile($post_data);

    if (!$id_prodotto) {
        throw new Exception('Errore durante la creazione del prodotto.');

    }

    set_product_hash_id($id_prodotto);

    return get_product_hash_id($id_prodotto);
}

// Crea la funzione ajax upload_document
function upload_document() {
    // Verifica se l'utente è loggato
    if (!is_user_logged_in()) {
        wp_send_json_error('Devi effettuare il login per caricare un documento.');
    }
    check_ajax_referer('upload_nonce', 'nonce');
    // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'upload_nonce')) {
    //     wp_send_json_error(['message' => 'Nonce non valido.']);
    // }

    // Recupera i parametri
    try {
        $params = get_upload_document_params();
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }


    // Crea il documento
    try {
        $created_post_id = create_document($params);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }

    wp_send_json_success(array('message' => 'Documento caricato con successo.', 'created_post_id' => $created_post_id));
}
add_action('wp_ajax_upload_document', 'upload_document');
add_action('wp_ajax_nopriv_upload_document', 'upload_document');

// Funzione per aggiornare un documento esistente
function update_document() {

    error_log('UPDATE DOCUMENT');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    check_ajax_referer('update_nonce', 'nonce');
    // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update_nonce')) {
    //     wp_send_json_error(['message' => 'Nonce non valido.']);
    // }

    // Recupera i dati inviati con la richiesta
    $hashed_post_id = sanitize_text_field($_POST['post_id']);

    $post_id = get_product_id_by_hash($hashed_post_id);
    $user_id = get_current_user_id();

    if (!$post_id) {
        wp_send_json_error(['message' => 'Documento non trovato.']);
    }
    $post = get_post($post_id);
    if (!$post) {
        wp_send_json_error(['message' => 'Documento non trovato.']);
    }
    if ($post->post_author != $user_id) {
        wp_send_json_error(['message' => 'Non sei autorizzato a modificare questo documento.']);
    }
    // Controlla che il file non sia in approvazione o rifiutato
    $stato_approvazione = get_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true);
    error_log('Stato approvazione: ' . $stato_approvazione);
    if ($stato_approvazione == 'in_approvazione' || $stato_approvazione == 'non_approvato') {
        wp_send_json_error(['message' => 'Il file è in approvazione o rifiutato. Non puoi modificarlo.']);
    }

    $titolo = sanitize_text_field($_POST['titolo']);
    $descrizione = sanitize_text_field($_POST['descrizione']);
    $universita = sanitize_text_field($_POST['universita']);
    $corsoDiLaurea = sanitize_text_field($_POST['corsoDiLaurea']);
    $materia = sanitize_text_field($_POST['materia']);
    $annoAccademico = sanitize_text_field($_POST['annoAccademico']);
    $tipoDocumento = sanitize_text_field($_POST['tipoDocumento']);
    $modalita = sanitize_text_field($_POST['modalita']);
    $prezzo = sanitize_text_field($_POST['prezzo']);

    // Recupera i parametri del documento
    try {
        error_log(print_r($_POST, true));
        $params = get_upload_document_params();
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
    // error_log('PARAMS: ' . print_r($params, true));
    
    if ($params['upload_choice'] !== 'modify') {
        wp_send_json_error(['message' => 'Opzione di upload non valida.']);
    }

    $validation_result = validate_upload_document_params($params);
    if (!$validation_result['success']) {
        wp_send_json_error(['message' => implode(', ', $validation_result['errors'])]);
    } 
    // $required_fields = [
    //     'upload_choice', 'titolo', 'descrizione', 'universita', 'corsoDiLaurea',
    //     'materia', 'annoAccademico', 'tipoDocumento', 'modalita', 'prezzo', 'file_id', 'n_pages', 'preview_file_id'
    // ];
    $isEdited = there_is_edited_fields($post_id, $params);
    if (!$isEdited) {
        wp_send_json_error(['message' => 'Nessun campo è stato modificato.']);
    }

    // Crea un array con i dati del post //TODO provare a utilizzare l'array params al posto di questo facendo attenzione alle 
    // logiche di invio mail e aggiornamento del prodotto
    $post_data = array(
        'post_id' => $post_id,
        'post_title' => $titolo,
        'post_content' => $descrizione,
        'nome_istituto' => $universita,
        'nome_corso_di_laurea' => $corsoDiLaurea,
        'nome_corso' => $materia,
        'anno_accademico' => $annoAccademico,
        'tipo_prodotto' => $tipoDocumento,
        'modalita_pubblicazione' => $modalita,
        'prezzo'=> $prezzo
    );
    error_log('POST DATA: ' . print_r($post_data, true));
    aggiorna_prodotto_esistente($post_data);

    //DA DECOMMENTARE
    // Recupera il prodotto esistente
    $product = wc_get_product($post_id);

    if (!$product) {
        wp_send_json_error('Prodotto non trovato.');
        wp_die();
    }
    //Imposto il metadato per sottolineare che è richiesta una approvazione per questo prodotto
    update_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, 'in_approvazione');

    // Invio una mail per richiedere l'approvazione delle modifiche
    send_email_approve_product_edit($post_id, $post_data);
    // Da vedere schermata di success
    wp_send_json_success('Documento modificato con successo.');
}
add_action('wp_ajax_update_document', 'update_document');
add_action('wp_ajax_nopriv_update_document', 'update_document');


// Funzione di aggiornamento del prodotto esistente
function aggiorna_prodotto_esistente($post_data) {

    error_log('AGGIORNA PRODOTTO ESISTENTE');

    error_log(print_r($post_data, true));
    // Recupera l'ID del prodotto esistente dai post_data
    $product_id = sanitize_text_field($post_data['post_id']);

    // Recupera il prodotto esistente
    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error('Prodotto non trovato.');
        wp_die();
    }

    // Aggiorna i dati del prodotto
    $name_product = $post_data['post_title'];
    $product->set_name($name_product);
    $product->set_description($post_data['post_content']);

    

    // Aggiorna le tassonomie
    // Rimuovi i termini esistenti della tassonomia 'nome_istituto'
    wp_set_post_terms($product_id, array(), 'nome_istituto');

    $term_universita = get_term_by('slug', $post_data['nome_istituto'], 'nome_istituto');
    if ($term_universita) {
        wp_set_post_terms($product_id, $term_universita->term_id, 'nome_istituto', true);
    } else {
        $term_universita = wp_insert_term($post_data['nome_istituto'], 'nome_istituto');
        add_term_meta($term_universita['term_id'], 'status', 'draft', true);
        wp_set_post_terms($product_id, $term_universita['term_id'], 'nome_istituto', true);
        send_mail_approval_term($term_universita['term_id'], $name_product);
    }

    // Rimuovi i termini esistenti della tassonomia 'nome_corso_di_laurea'
    wp_set_post_terms($product_id, array(), 'nome_corso_di_laurea');

    $term_corso_di_laurea = get_term_by('slug', $post_data['nome_corso_di_laurea'], 'nome_corso_di_laurea');
    if ($term_corso_di_laurea) {
        wp_set_post_terms($product_id, $term_corso_di_laurea->term_id, 'nome_corso_di_laurea', true);
    } else {
        $term_corso_di_laurea = wp_insert_term($post_data['nome_corso_di_laurea'], 'nome_corso_di_laurea');
        add_term_meta($term_corso_di_laurea['term_id'], 'status', 'draft', true);
        wp_set_post_terms($product_id, $term_corso_di_laurea['term_id'], 'nome_corso_di_laurea', true);
        send_mail_approval_term($term_corso_di_laurea['term_id'], $name_product);
    }

    // Rimuovi i termini esistenti della tassonomia 'nome_corso'
    wp_set_post_terms($product_id, array(), 'nome_corso');

    $term_materia = get_term_by('slug', $post_data['nome_corso'], 'nome_corso');
    if ($term_materia) {
        wp_set_post_terms($product_id, $term_materia->term_id, 'nome_corso', true);
    } else {
        $term_materia = wp_insert_term($post_data['nome_corso'], 'nome_corso');
        add_term_meta($term_materia['term_id'], 'status', 'draft', true);
        wp_set_post_terms($product_id, $term_materia['term_id'], 'nome_corso', true);
        send_mail_approval_term($term_materia['term_id'], $name_product);
    }

    // Rimuovi i termini esistenti della tassonomia 'anno_accademico'
    wp_set_post_terms($product_id, array(), 'anno_accademico');

    $term_anno_accademico = get_term_by('slug', $post_data['anno_accademico'], 'anno_accademico');
    if ($term_anno_accademico) {
        wp_set_post_terms($product_id, $term_anno_accademico->term_id, 'anno_accademico', true);
    }

    // Rimuovi i termini esistenti della tassonomia 'tipo_prodotto'
    wp_set_post_terms($product_id, array(), 'tipo_prodotto');

    $term_tipo_documento = get_term_by('slug', $post_data['tipo_prodotto'], 'tipo_prodotto');
    if ($term_tipo_documento) {
        $term_documento = get_term_by( 'slug', 'documento', 'tipo_prodotto');
        wp_set_post_terms($product_id, array($term_tipo_documento->term_id , $term_documento->term_id), 'tipo_prodotto', true);
    }

    // Rimuovi i termini esistenti della tassonomia 'modalita_pubblicazione'
    wp_set_post_terms($product_id, array(), 'modalita_pubblicazione');

    $term_modalita = get_term_by('slug', $post_data['modalita_pubblicazione'], 'modalita_pubblicazione');
    if ($term_modalita) {
        wp_set_post_terms($product_id, $term_modalita->term_id, 'modalita_pubblicazione', true);
    }

    $prezzo = $post_data['prezzo'];
    if ($post_data['modalita_pubblicazione'] == 'vendi') {
        update_post_meta( $product_id, '_costo_in_punti_pro', $prezzo );
        update_post_meta( $product_id, '_costo_in_punti_blu', 0 );
    } else {
        update_post_meta( $product_id, '_costo_in_punti_pro', 0 );
        update_post_meta( $product_id, '_costo_in_punti_blu', PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO );
    }
    
    // Salva il prodotto aggiornato
    $product->save();

    return true;
    // wp_send_json_success('Prodotto aggiornato con successo.');
}


function crea_prodotto_virtuale_scaricabile($info_product) {
    $product_name = $info_product['post_title'];
    $product_description = $info_product['post_content'];
    $purchaseable_file_path = $info_product['purchaseable_file_path'];
    $preview_file_path = $info_product['preview_file_path'];
    $thumbnail_id = $info_product['thumbnail_id'];
    $product_base_id = $info_product['product_base_id'];
    $version_number = $info_product['version_number'];


    // Controlla se il prodotto esiste già
    //$product_name = $file['name'];
    // Crea un nuovo prodotto
    $product = new WC_Product_Simple();

    // Imposta il titolo del prodotto
    $product->set_name($product_name);

    // Imposta la descrizione del prodotto
    $product->set_description($product_description);

    // Imposta la visibilità del prodotto
    $product->set_catalog_visibility('visible');

    // Imposta lo stato del prodotto a bozza
    if ($info_product['skip_approval']) {
        $product->set_status('publish');
    } else {
        $product->set_status('draft');
    }
    //$product->set_status('draft');

    // Imposta la durata del download
    $product->set_download_expiry(30);

    // Imposta il prodotto come virtuale
    $product->set_virtual(true);

    // Imposta il prodotto come scaricabile
    $product->set_downloadable(true);

    // Aggiungi un file scaricabile
    // Ricava file name e file path dal media con id $file_id
    $file_id = $info_product['file_id'];
    $file = get_post($file_id);
    $file_url = wp_get_attachment_url($file_id);
    $file_path = get_attached_file($file_id);
    $file_name = $product_name; // Nome che apparirà all'utente
    $product->set_downloads(array(array(
        'name' => $file_name,
        'file' => $file_url,
    )));

    // Salva il prodotto
    $product->save();

    $id = $product->get_id();

    // Imposta la tassonomia dell'università
    $universita = $info_product['universita'];
    $term = get_term_by('slug', $universita, 'nome_istituto');
    if ($term) {
        // Se il termine esiste già, imposta per il post di id $id la tassonomia con id $term->term_id
        wp_set_post_terms( $id, $term->term_id, 'nome_istituto', true );
    } else {
        $term = wp_insert_term($universita, 'nome_istituto');
        // add term meta
        add_term_meta($term['term_id'], 'status', 'draft', true);
        wp_set_post_terms( $id, $term['term_id'], 'nome_istituto', true );
        send_mail_approval_term($term['term_id'], $product_name);
    }

    // Imposta la tassonomia del corso di laurea
    $corso_di_laurea = $info_product['corso_di_laurea'];
    $term = get_term_by('slug', $corso_di_laurea, 'nome_corso_di_laurea');
    if ($term) {
        wp_set_post_terms( $id, $term->term_id, 'nome_corso_di_laurea', true );
    } else { //Caso in cui il termine non esiste
        $term = wp_insert_term($corso_di_laurea, 'nome_corso_di_laurea');
        add_term_meta($term['term_id'], 'status', 'draft', true);
        wp_set_post_terms( $id, $term['term_id'], 'nome_corso_di_laurea', true );
        send_mail_approval_term($term['term_id'], $product_name);
    }

    // Imposta la tassonomia della materia
    $materia = $info_product['materia'];
    $term = get_term_by('slug', $materia, 'nome_corso');
    if ($term) {
        wp_set_post_terms( $id, $term->term_id, 'nome_corso', true );
    } else { //Caso in cui il termine non esiste
        $term = wp_insert_term($materia, 'nome_corso');
        add_term_meta($term['term_id'], 'status', 'draft', true);
        wp_set_post_terms( $id, $term['term_id'], 'nome_corso', true );
        send_mail_approval_term($term['term_id'], $product_name);
    } 

    // Imposta anno accademico
    $anno_accademico = $info_product['anno_accademico'];
    $term = get_term_by('slug', $anno_accademico, 'anno_accademico');
    if ($term) {
        wp_set_post_terms( $id, $term->term_id, 'anno_accademico', true );
    } 

    // Imposta il tipo di documento
    $tipo_documento = $info_product['tipo_documento'];
    $term = get_term_by('slug', $tipo_documento, 'tipo_prodotto');
    if ($term) {
        $term_documento = get_term_by( 'slug', 'documento', 'tipo_prodotto');
        wp_set_post_terms( $id, array($term->term_id, $term_documento->term_id), 'tipo_prodotto', true );
    } 
    
    // Imposta la modalità di pubblicazione
    $modalita = $info_product['modalita'];
    $term = get_term_by('slug', $modalita, 'modalita_pubblicazione');
    if ($term) {
        wp_set_post_terms( $id, $term->term_id, 'modalita_pubblicazione', true );
    }
    
    // Imposta il prezzo del prodotto
    $product->set_price(0);

    // Imposta il prezzo in punti pro e blu
    $prezzo = $info_product['prezzo'];
    if ($modalita == 'vendi') {
        add_post_meta( $id, '_costo_in_punti_pro', $prezzo, true );
        add_post_meta( $id, '_costo_in_punti_blu', 0, true );
    } else {
        add_post_meta( $id, '_costo_in_punti_pro', 0, true );
        add_post_meta( $id, '_costo_in_punti_blu', PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO, true );
    }

    // Imposta il numero di pagine del documento
    $n_pages = $info_product['n_pages'];
    add_post_meta( $id, '_num_pagine', $n_pages, true );

    // Imposta la thumbnail del prodotto
    set_post_thumbnail( $id, $thumbnail_id );

    // Imposta l'id del prodotto base
    add_post_meta( $id, '_id_base_prodotto', $product_base_id, true );
    // Imposta il numero di versione del prodotto
    add_post_meta( $id, '_numero_versione', $version_number, true );

    $attachment_id = get_post_thumbnail_id( $id );
    if (!$attachment_id) {
        $attachment_id = 644; // ID dell'immagine predefinita
        set_post_thumbnail( $id, $attachment_id );
    }

    //imposta il post meta 
    add_post_meta($id, '_file_anteprima', $info_product['preview_file_id'], true);

    // Imposta lo stato di approvazione del prodotto
    if($info_product['skip_approval']){
        add_post_meta($id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, 'approvato', true);
    } else {
        add_post_meta($id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, 'in_approvazione', true);
        send_email_approval_product($id);
    }

    return $product->get_id();
}





//////////////////////////////////////////////////////UPLOAD FINALE////////////////////////////////


add_action('wp_ajax_upload_and_control_file_ajax', 'upload_and_control_file');
add_action('wp_ajax_nopriv_upload_and_control_file_ajax', 'upload_and_control_file');

function upload_and_control_file() {
    error_log('upload_and_control_file');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    check_ajax_referer('file_upload_nonce', 'nonce');
    // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'file_upload_nonce')) {
    //     wp_send_json_error(['message' => 'Nonce non valido.']);
    // }

    // Controllo se il file è stato caricato correttamente
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(['message' => 'Errore durante il caricamento del file.', 'upload_error' => 'upload_failed']);
        //wp_redirect(add_query_arg('upload_error', 'upload_failed', wp_get_referer()));
        wp_die();
    }

    $file = $_FILES['file'];
    $result = handle_file_upload($file);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message(), 'upload_error' => $result->get_error_code()]);
    } else {
        wp_send_json_success($result);
    }

    wp_die();
}

function handle_file_upload($file) {
    // Lista di mime type permessi
    $allowed_types = ['application/pdf'];

    $file_hash = hash_file('sha256', $file['tmp_name']);
    error_log('File hash: ' . $file_hash);

    $tika_port = start_tika_server();
    error_log('tika_port: ' . $tika_port);
    if (!$tika_port) {
        return new WP_Error('server_error', 'Errore 7000');
    }

    // Inizializzazione del client Apache Tika
    $client = \Vaites\ApacheTika\Client::make('localhost', $tika_port); // server mode (default)
    $client = \Vaites\ApacheTika\Client::prepare('localhost', $tika_port);

    // Configura il timeout di cURL
    $client->setOption(CURLOPT_TIMEOUT, 3000); // Timeout di 300 secondi
    $client->setOption(CURLOPT_CONNECTTIMEOUT, 3000); // Timeout di connessione di 300 secondi

    // Imposta un timeout per la richiesta al server Tika
    $timeout = 3000; // Timeout di 60 secondi
    ini_set('default_socket_timeout', $timeout);

    $n_pages = 0;
    try {
        // Ottengo i metadati del file
        $metadata = $client->getMetadata($file['tmp_name']);
        error_log('Metadata: ' . print_r($metadata, true));
        stop_tika_server($tika_port);
        if ($metadata->mime !== 'application/pdf') {
            return new WP_Error('invalid_file_type', 'Tipo di file non permesso');
        }
        $n_pages = $metadata->pages;
    } catch (Exception $e) {
        try {
            stop_tika_server($tika_port);
        } catch (Exception $e) {
            error_log('Errore durante l\'arresto del server Tika: ' . $e->getMessage());
        } finally {
            return new WP_Error('server_error', 'Errore durante l\'elaborazione del file.');
        }
    }

    // Verifica dimensione massima (10MB)
    $max_file_size = 10.1 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_file_size) {
        wp_send_json_error(['message' => 'Il file PDF eccede le dimensioni massime consentite.', 'upload_error' => 'file_size_exceeds_limits'], 413);
        wp_die();
        return new WP_Error('file_size_exceeds_limits', 'Il file PDF eccede le dimensioni massime consentite.');
    }

    // Verifica del contenuto del file
    $file_contents = file_get_contents($file['tmp_name']);
    if (stripos($file_contents, '<?php') !== false) {
        return new WP_Error('unsafe_contents', 'Il contenuto del file PDF non è sicuro.');
    }

    // Imposta la cartella di destinazione per il file
    add_filter('upload_dir', 'upload_directory_protected_purchaseable_files');

    // Carica il file nella cartella uploads di WordPress
    $upload = wp_handle_upload($file, array('test_form' => false));

    // Rimuovi il filtro per evitare problemi con altri upload
    remove_filter('upload_dir', 'upload_directory_protected_purchaseable_files');

    $preview_file_path = str_replace('/protected/purchaseable_files', '/protected/preview', $upload['file']);
    $directory_specifica = substr($preview_file_path, 0, strrpos($preview_file_path, '/'));
    if (!file_exists($directory_specifica)) {
        wp_mkdir_p($directory_specifica);
    }

    // Genero l'anteprima del file PDF
    try {
        $preview_file_id = genera_pdf_prime_n_pagine($upload['file'], $preview_file_path, 3); // Genera l'anteprima delle prime 3 pagine del PDF
    } catch (Exception $e) {
        return new WP_Error('preview_generation_error', 'Il file che stai cercando di caricare risulta protetto o il formato non è accettabile. Ti preghiamo di provare a caricare un altro file.');
    }

    error_log('preview_file_id: ' . $preview_file_id);

    $attachment_path = get_attached_file($preview_file_id);
    error_log('Attachment path: ' . $attachment_path);

    if ($upload && !isset($upload['error'])) { // Caricamento completato con successo
        // Creo l'attachment che rappresenta il file caricato nella sezione Media di WordPress
        $file_id = create_attachments($upload, $file);

        set_attachment_hash_id($file_id);   
        set_attachment_hash_id($preview_file_id);

        $hashed_file_id = get_attachment_hash_id($file_id);
        $hashed_preview_file_id = get_attachment_hash_id($preview_file_id);

        return ['file_id' => $hashed_file_id, 'n_pages' => $n_pages, 'preview_file_id' => $hashed_preview_file_id];
    } else {
        return new WP_Error('upload_failed', 'Errore durante il caricamento del file.');
    }
}

function generate_attachment_hash_id($attachment_id) {
    $salt = 'ATTACHASHSALT'; // Sostituisci con una stringa casuale
    $hash = hash('sha256', $salt . $attachment_id);
    return $hash;
}


function set_attachment_hash_id($attachment_id) {
    $hashed_file_id = generate_attachment_hash_id($attachment_id);
    update_post_meta($attachment_id, 'hashed_file_id', $hashed_file_id);
}
function get_attachment_hash_id($attachment_id) {
    return get_post_meta($attachment_id, 'hashed_file_id', true);
}

function get_attachment_id_by_hash($hash) {

    $hash = str_replace('\\', '', $hash);
    $hash = str_replace('"', '', $hash);

    error_log('get_attachment_id_by_hash: ' . $hash);


    $args = array(
        'post_type' => 'attachment',
        'meta_query' => array(
            array(
                'key' => 'hashed_file_id',
                'value' => $hash,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'any'
    );
    $query = new WP_Query($args);

    if (is_wp_error($query)) {
        error_log('Errore nella query: ' . $query->get_error_message());
        return null;
    }
    if (empty($query->posts)) {
        error_log('Nessun post trovato con l\'hash fornito.');
        return null;
    }
    error_log('Post trovato: ' . print_r($query->posts, true));


    return !empty($query->posts) ? $query->posts[0]->ID : null;
}
 /*
add_action('init', 'test_get_attachment_id_by_hash');

function test_get_attachment_id_by_hash() {
    $hash = '00b0fa5ec688bfb2b96b49aec758b9889ff079dcfeef936037d51e6433804bc0'; // Sostituisci con l'hash che vuoi testare
    $attachment_id = get_attachment_id_by_hash($hash);
    if ($attachment_id) {
        error_log('ID dell\'allegato trovato: ' . $attachment_id->ID);
    } else {
        error_log('Nessun allegato trovato con l\'hash fornito.');
    }
}*/

function start_tika_server() {
    $url = APACHE_TIKA_SERVER_URL . '/start_tika'; // URL del server Flask

    $response = wp_remote_post($url, array(
        'method'    => 'POST',
        'timeout'   => 60,
        #'headers'   => array('Content-Type' => 'application/json'),
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Errore nella richiesta di avvio di Tika: $error_message");
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Verifica che la porta sia presente nella risposta
    if (isset($data['port'])) {
        return $data['port']; // Porta su cui è in esecuzione Tika
    } else {
        error_log("Errore: Porta non trovata nella risposta di Tika");
        return false;
    }
}


function stop_tika_server($tika_port) {
    $url = APACHE_TIKA_SERVER_URL . '/stop_tika';

    $response = wp_remote_post($url, array(
        'method'    => 'POST',
        'headers'   => array('Content-Type' => 'application/json'),
        'body'      => json_encode(array('port' => $tika_port)),
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Errore nella richiesta di terminazione di Tika: $error_message");
    } else {
        error_log("Server Tika terminato con successo sulla porta $tika_port");
    }
}


function check_product_data($product){
    $product_id = $product->get_id(); // Recupera l'ID del prodotto da analizzare
    $taxonomies = array('nome_istituto', 'nome_corso_di_laurea', 'nome_corso'); // Sostituisci con i nomi delle tassonomie
    $terms_with_draft_status = array(); // Per salvare i termini in "draft"
    $missing_taxonomies = array(); // Per tenere traccia delle tassonomie mancanti

    // Recupera i termini associati al prodotto per ciascuna tassonomia
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_post_terms($product_id, $taxonomy);
        error_log($taxonomy . ' - terms: ' . print_r($terms, true));
        
        // Se la tassonomia non ha termini associati, aggiungila all'elenco delle mancanti
        if (empty($terms) || is_wp_error($terms)) {
            $missing_taxonomies[] = $taxonomy;
            continue;
        }

        // Controlla se il termine ha lo stato "draft"
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $status = get_term_meta($term->term_id, 'status', true);
                if ($status === 'draft') {
                    $terms_with_draft_status[] = array(
                        'term_id'   => $term->term_id,
                        'name'      => $term->name,
                        'taxonomy'  => $taxonomy,
                    );
                }
            }
        }
    }

    return array(
        'terms_with_draft_status' => $terms_with_draft_status,
        'missing_taxonomies' => $missing_taxonomies
    );

    // // Output dei risultati
    // if (!empty($terms_with_draft_status)) {
    //     echo 'I seguenti termini associati al prodotto sono in stato "draft":<br>';
    //     foreach ($terms_with_draft_status as $term_data) {
    //         echo 'Termine: ' . $term_data['name'] . ' (ID: ' . $term_data['term_id'] . ') - Tassonomia: ' . $term_data['taxonomy'] . '<br>';
    //     }
    // } else {
    //     echo 'Nessun termine associato al prodotto è in stato "draft".';
    // }

    // error_log($product);
    // return true;
}

function get_product_taxonomy_info($product_id) {
    $taxonomies = array('nome_istituto', 'nome_corso_di_laurea', 'nome_corso', 'anno_accademico', 'tipo_prodotto', 'modalita_pubblicazione'); // Sostituisci con i nomi reali delle tassonomie
    $taxonomy_info = array(); // Array per salvare le informazioni
    $missing_taxonomies = array(); // Per tracciare le tassonomie mancanti

    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_post_terms($product_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            $missing_taxonomies[$taxonomy] = 'Nessun termine assegnato';
            continue;
        }

        $term_names = [];
        foreach ($terms as $term) {
            $status = get_term_meta($term->term_id, 'status', true);
            $term_label = $term->name;
            
            if ($status === 'draft') {
                $term_label .= ' (IN APPROVAZIONE)';
            }

            $term_names[] = $term_label;
        }

        // Salva i nomi dei termini per la tassonomia
        $taxonomy_info[$taxonomy] = implode(', ', $term_names);
    }
    error_log('Taxonomy info: ' . print_r($taxonomy_info, true));
    return array(
        'product_id' => $product_id,
        'taxonomies' => $taxonomy_info,
        'missing_taxonomies' => $missing_taxonomies
    );
}

/**
 * Verifica se ci sono campi modificati rispetto ai dati esistenti.
 *
 * @param int $post_id L'ID del post da confrontare.
 * @param array $params I nuovi parametri da confrontare.
 * @return bool True se ci sono campi modificati, False altrimenti.
 */
function there_is_edited_fields($post_id, $params) {
    error_log('there_is_edited_fields');
    error_log('POST ID: ' . $post_id);
    error_log('PARAMS THERE IS EDITED FIELDS: ' . print_r($params, true));
    // Recupera i dati esistenti del post
    $existing_post = get_post($post_id);
    if (!$existing_post) {
        return false; // Post non trovato
    }

    try {
        $existing_data = [
            'titolo' => $existing_post->post_title,
            'descrizione' => $existing_post->post_content,
            'universita' => get_nome_istituto($post_id, true),
            'corsoDiLaurea' => get_nome_corso_di_laurea($post_id, true),
            'materia' => get_nome_corso($post_id, true),
            'annoAccademico' => get_anno_accademico($post_id),
            'tipoDocumento' => get_tipo_prodotto($post_id, true),
            'modalita' => get_modalita_pubblicazione($post_id, true),
            'prezzo' => get_prezzo_prodotto($post_id),
        ];
        error_log('Existing data: ' . print_r($existing_data, true));

        foreach ($existing_data as $field => $value) {
            if ($field === 'prezzo' && $existing_data['modalita'] === 'condividi' && $params['modalita'] === 'condividi') {
            continue; // Salta il controllo del campo prezzo se la modalità è 'condividi' perchè il metodo get_prezzo_prodotto($post_id) restituisce restituisce il prezzo in punti blu, che dalla POST non arriva perchè di default = 20
            }
            if (isset($params[$field]) && $params[$field] != $value) {
            return true; // Modifica trovata
            }
        }
    } catch (Exception $e) {
        return false; // Errore durante il recupero dei dati
    }


    return false; // Nessun campo modificato
}
