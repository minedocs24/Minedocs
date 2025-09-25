<?php
// Aggiungi l'azione per registrare lo script AJAX
add_action('wp_ajax_get_user_uploaded_documents', 'get_user_uploaded_documents');
add_action('wp_ajax_nopriv_get_user_uploaded_documents', 'get_user_uploaded_documents');

// Funzione per recuperare i documenti caricati dall'utente loggato
function get_user_uploaded_documents() {
    // Verifica che l'utente sia loggato
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Utente non loggato.'));
        wp_die();
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_load_user_uploaded_documents')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    // Ottieni l'ID dell'utente loggato
    $user_id = get_current_user_id();

    // Query per recuperare i post creati dall'utente loggato
    $args = array(
        'post_type' => 'product', // Tipo di post
        'post_status' => array('publish', 'draft'), // Recupera i post pubblicati o in bozza
        'author' => $user_id, // Filtra per l'ID dell'utente loggato
        'posts_per_page' => -1, // Recupera tutti i post
        'tax_query' => array(
            array(
                'taxonomy' => 'tipo_prodotto',
                'field' => 'slug',
                'terms' => 'documento'
            )
        ),
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'stato_prodotto',
                'value' => 'pubblicato',
                'compare' => '='
            ),
            array(
                'key' => 'stato_prodotto',
                'compare' => 'NOT EXISTS'
            )
        )
    );

    // Recupera i post
    $query = new WP_Query($args);

    $files = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $anni_accademici = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'anno_accademico', array('fields' => 'all')));
            $tipi_istituti = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'tipo_istituto', array('fields' => 'all')));
            $nomi_istituti = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'nome_istituto', array('fields' => 'all')));
            $nomi_corsi = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'nome_corso', array('fields' => 'all')));
            $nomi_corsi_laurea = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'nome_corso_di_laurea', array('fields' => 'all')));

            $costo_in_punti_pro = get_post_meta($post_id, '_costo_in_punti_pro', true);
            $costo_in_punti_blu = get_post_meta($post_id, '_costo_in_punti_blu', true);
            $tipo = $costo_in_punti_pro > 0 ? 'pro' : 'blu';

            $files[] = array(
                //'id' => $post_id,
                'hid' => get_product_hash_id($post_id),
                'name' => get_the_title(),
                'upload_date' => get_the_date(),
                'description' => get_the_excerpt(),
                'anno_accademico' => implode(', ', $anni_accademici),
                'tipo_istituto' => implode(', ', $tipi_istituti),
                'nome_istituto' => implode(', ', $nomi_istituti),
                'nome_corso' => implode(', ', $nomi_corsi),
                'nome_corso_di_laurea' => implode(', ', $nomi_corsi_laurea),
                'costo_in_punti_pro' => $costo_in_punti_pro,
                'costo_in_punti_blu' => $costo_in_punti_blu,
                'tipo' => $tipo,
                'document_link' => get_permalink($post_id),
                'stato_approvazione' => get_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true),
                'stato_prodotto' => get_stato_prodotto($post_id),
                'status' => get_post_status($post_id)
            );
        }
    }

    

    // Ripristina i dati globali del post
    wp_reset_postdata();

    // Restituisci i dati in formato JSON
    wp_send_json_success($files);
    wp_die();
}

