<?php

// Funzione AJAX per recuperare i dettagli del documento
function get_document_details() {
    // Verifica che l'utente sia loggato
    if (!is_user_logged_in()) {
        wp_send_json_error('Utente non loggato.');
        return;
    }

    check_ajax_referer('nonce_document_details', 'nonce');

    // Recupera l'ID del documento
    $document_id = sanitize_text_field($_POST['document_id']);
    if (empty($document_id)) {
        wp_send_json_error('ID documento mancante.');
        return;
    }
    // Converti l'hash in ID del prodotto
    $product_id = get_product_id_by_hash($document_id);
    if (!$product_id) {
        wp_send_json_error('Documento non trovato.');
        return;
    }

    // Recupera i dettagli del prodotto
    $product = get_post($product_id);
    if (!$product) {
        wp_send_json_error('Documento non trovato.');
        return;
    }

    // Recupera i metadati del documento
    $file_anteprima_id = get_post_meta($product_id, '_file_anteprima', true);
    $num_pagine = get_post_meta($product_id, '_num_pagine', true);
    
    // Recupera le tassonomie
    $anni_accademici = array_map(function($term) { return $term->name; }, wp_get_post_terms($product_id, 'anno_accademico', array('fields' => 'all')));
    $tipi_istituti = array_map(function($term) { return $term->name; }, wp_get_post_terms($product_id, 'tipo_istituto', array('fields' => 'all')));
    $nomi_istituti = array_map(function($term) { return $term->name; }, wp_get_post_terms($product_id, 'nome_istituto', array('fields' => 'all')));
    $nomi_corsi = array_map(function($term) { return $term->name; }, wp_get_post_terms($product_id, 'nome_corso', array('fields' => 'all')));
    $nomi_corsi_laurea = array_map(function($term) { return $term->name; }, wp_get_post_terms($product_id, 'nome_corso_di_laurea', array('fields' => 'all')));

    // Prepara i dati del documento
    $document_data = array(
        'id' => $product_id,
        'hash_id' => $document_id,
        'title' => $product->post_title,
        'description' => $product->post_excerpt,
        'content' => $product->post_content,
        'pages' => $num_pagine ? intval($num_pagine) : 0,
        'upload_date' => get_the_date('d/m/Y', $product_id),
        'file_anteprima_id' => $file_anteprima_id,
        'file_url' => wp_get_attachment_url($file_anteprima_id),
        'anno_accademico' => implode(', ', $anni_accademici),
        'tipo_istituto' => implode(', ', $tipi_istituti),
        'nome_istituto' => implode(', ', $nomi_istituti),
        'nome_corso' => implode(', ', $nomi_corsi),
        'nome_corso_di_laurea' => implode(', ', $nomi_corsi_laurea),
        'author_id' => $product->post_author,
        'author_name' => get_the_author_meta('display_name', $product->post_author),
        'status' => $product->post_status
    );

    wp_send_json_success($document_data);
}
add_action('wp_ajax_get_document_details', 'get_document_details');
add_action('wp_ajax_nopriv_get_document_details', 'get_document_details');

?>