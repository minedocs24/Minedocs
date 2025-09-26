<?php

include_once get_stylesheet_directory() . '/inc/upload.php';
include_once ABSPATH . 'wp-admin/includes/file.php';
include_once ABSPATH . 'wp-admin/includes/media.php';
include_once ABSPATH . 'wp-admin/includes/image.php';

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/upload-document/', array(
        'methods' => 'POST',
        'callback' => 'handle_upload_document_api',
        'permission_callback' => function () {
            return  current_user_can('manage_woocommerce'); // Solo utenti con permessi admin
        }
        
    ));
});

function handle_upload_document_api(WP_REST_Request $request) {
    try {
        if (empty($_FILES['file'])) {
            return new WP_REST_Response(['message' => 'Nessun file caricato'], 400);
        }
        
        $uploaded_file = $_FILES['file'];

        $result = handle_file_upload($uploaded_file);

        if (is_wp_error($result)) {
            return new WP_REST_Response(['message' => $result->get_error_message(), 'upload_error' => $result->get_error_code()], 400);
        }

        $universita = sanitize_text_field($request->get_param('universita'));
        $corsoDiLaurea = sanitize_text_field($request->get_param('corsoDiLaurea'));
        $materia = sanitize_text_field($request->get_param('materia'));
        $annoAccademico = sanitize_text_field($request->get_param('annoAccademico'));
        $tipoDocumento = sanitize_text_field($request->get_param('tipoDocumento'));
        $modalita = sanitize_text_field($request->get_param('modalita'));

        $term_universita = term_exists($universita, 'nome_istituto');
        if (!$term_universita) {
            return new WP_REST_Response(['message' => 'Università non valida'], 400);
        }

        $term_corsoDiLaurea = term_exists($corsoDiLaurea, 'nome_corso_di_laurea');
        error_log('Corso di laurea cercato: ' . $corsoDiLaurea);
        error_log('term_exists result: ' . print_r($term_corsoDiLaurea, true));
        
        if (!$term_corsoDiLaurea) {
            error_log('Termine non trovato con term_exists, provo get_term_by');
            $term_corsoDiLaurea = get_term_by('name', $corsoDiLaurea, 'nome_corso_di_laurea');
            error_log('get_term_by result: ' . print_r($term_corsoDiLaurea, true));
            
            if (!$term_corsoDiLaurea) {
                error_log('Termine non trovato con get_term_by, provo ricerca con name__like');
                // Prova a cercare un termine che inizia con la stringa
                $terms = get_terms(array(
                    'taxonomy' => 'nome_corso_di_laurea',
                    'name__like' => $corsoDiLaurea,
                    'hide_empty' => false
                ));
                error_log('get_terms con name__like result: ' . print_r($terms, true));
                
                if (!empty($terms)) {
                    $term_corsoDiLaurea = $terms[0]; // Prendi il primo termine trovato
                    error_log('Termine trovato con name__like, term_id: ' . $term_corsoDiLaurea->term_id);
                }
            }
            
            if (!$term_corsoDiLaurea) {
                error_log('Corso di laurea non valido - nessun termine trovato');
                return new WP_REST_Response(['message' => 'Corso di laurea non valido'], 400);
            }
        }

        $term_materia = term_exists($materia, 'nome_corso');
        if (!$term_materia) {
            return new WP_REST_Response(['message' => 'Materia non valida'], 400);
        }

        $term_annoAccademico = term_exists($annoAccademico, 'anno_accademico');
        if (!$term_annoAccademico) {
            return new WP_REST_Response(['message' => 'Anno accademico non valido'], 400);
        }

        $term_tipoDocumento = term_exists($tipoDocumento, 'tipo_prodotto');
        if (!$term_tipoDocumento) {
            return new WP_REST_Response(['message' => 'Tipo documento non valido'], 400);
        }

        $term_modalita = term_exists($modalita, 'modalita_pubblicazione');
        if (!$term_modalita) {
            return new WP_REST_Response(['message' => 'Modalità non valida'], 400);
        }

        // Gestione corretta dei termini per ottenere gli slug
        if (is_array($term_universita)) {
            $term_universita = get_term($term_universita['term_id'])->slug;
        } else {
            $term_universita = $term_universita->slug;
        }
        
        if (is_object($term_corsoDiLaurea)) {
            $term_corsoDiLaurea = $term_corsoDiLaurea->slug;
        } else {
            $term_corsoDiLaurea = get_term($term_corsoDiLaurea['term_id'])->slug;
        }
        
        if (is_array($term_materia)) {
            $term_materia = get_term($term_materia['term_id'])->slug;
        } else {
            $term_materia = $term_materia->slug;
        }
        
        if (is_array($term_annoAccademico)) {
            $term_annoAccademico = get_term($term_annoAccademico['term_id'])->slug;
        } else {
            $term_annoAccademico = $term_annoAccademico->slug;
        }
        
        if (is_array($term_tipoDocumento)) {
            $term_tipoDocumento = get_term($term_tipoDocumento['term_id'])->slug;
        } else {
            $term_tipoDocumento = $term_tipoDocumento->slug;
        }
        
        if (is_array($term_modalita)) {
            $term_modalita = get_term($term_modalita['term_id'])->slug;
        } else {
            $term_modalita = $term_modalita->slug;
        }

        $skip_approval = false;

        if ($request->get_param('skip_approval') === 'true') {
            $skip_approval = true;
        }

        // Converti gli hash in ID numerici degli attachment
        $file_id = get_attachment_id_by_hash($result['file_id']);
        if (!$file_id) {
            return new WP_REST_Response(['message' => 'File non trovato'], 400);
        }

        $preview_file_id = get_attachment_id_by_hash($result['preview_file_id']);
        if (!$preview_file_id) {
            return new WP_REST_Response(['message' => 'File di anteprima non trovato'], 400);
        }

        $params = array(
            'upload_choice' => 'upload',
            'post_id' => null,
            'titolo' => sanitize_text_field($request->get_param('titolo')),
            'descrizione' => sanitize_text_field($request->get_param('descrizione')),
            'universita' => $term_universita,
            'corsoDiLaurea' => $term_corsoDiLaurea,
            'materia' => $term_materia,
            'annoAccademico' => $term_annoAccademico,
            'tipoDocumento' => $term_tipoDocumento,
            'modalita' => $term_modalita,
            'prezzo' => sanitize_text_field($request->get_param('prezzo')),
            'file_id' => $file_id,
            'n_pages' => $result['n_pages'],
            'preview_file_id' => $preview_file_id,
            'skip_approval' => $skip_approval,
            'thumbnail_data' => create_thumbnail($file_id)
        );

        $product_id = create_document($params);
        
        if (is_wp_error($product_id)) {
            return new WP_REST_Response(['message' => $product_id->get_error_message()], 400);
        }
        
        return new WP_REST_Response(['message' => 'Documento caricato con successo', 'product_id' => $product_id], 201);
        
    } catch (Exception $e) {
        error_log('Errore nell\'API upload document: ' . $e->getMessage());
        return new WP_REST_Response(['message' => 'Errore interno del server: ' . $e->getMessage()], 500);
    }
}
