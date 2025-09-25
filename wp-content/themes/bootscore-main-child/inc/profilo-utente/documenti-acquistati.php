<?php
// Aggiungi l'azione per gestire la richiesta AJAX
add_action('wp_ajax_get_user_purchased_files', 'get_user_purchased_files_ajax');
add_action('wp_ajax_nopriv_get_user_purchased_files', 'get_user_purchased_files_ajax');

function get_user_purchased_files_ajax() {
    // Verifica che l'utente sia autenticato
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Utente non autenticato.'));
        wp_die();
    }

    // Verifica il nonce per la sicurezza
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_load_user_purchased_documents')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $customer_orders = wc_get_orders(array(
        'customer_id' => $current_user->ID,
        'status' => 'completed',
        'limit' => -1,
    ));

    $product_ids = array();
    $mapping_product_id_to_order_id = array();
    foreach ($customer_orders as $order) {
        foreach ($order->get_items() as $item) {
            $product_ids[] = $item->get_product_id();
            $mapping_product_id_to_order_id[$item->get_product_id()] = $order->get_id();
        }
    }

    $files = array();
    $grouped_files = array();

    if (!empty($product_ids)) {
        $args = array(
            'post_type' => 'product',
            'post_status' => array('publish'),
            'post__in' => $product_ids,
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'tipo_prodotto',
                    'field' => 'slug',
                    'terms' => 'documento',
                    'include_children' => true,
                )
            )
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post_id = get_the_ID();
                $order_id = $mapping_product_id_to_order_id[$post_id];
                $anni_accademici = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'anno_accademico', array('fields' => 'all')));
                $tipi_istituti = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'tipo_istituto', array('fields' => 'all')));
                $nomi_istituti = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'nome_istituto', array('fields' => 'all')));
                $nomi_corsi = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'nome_corso', array('fields' => 'all')));
                $nomi_corsi_laurea = array_map(function($term) { return $term->name; }, wp_get_post_terms($post_id, 'nome_corso_di_laurea', array('fields' => 'all')));

                $recensione_utente = get_user_review_for_product($post_id, $user_id);

                $product_hid = get_product_hash_id($post_id);

                $gruppo = $nomi_istituti[0] . ' - ' . $nomi_corsi_laurea[0];

                $author_id = get_post_field('post_author', $post_id);
                $author_hid = get_user_hash_id($author_id);

                $order_hid = get_order_hash($order_id);
                error_log('Order HID: ' . $order_hid); // Debugging line
                $costo_in_punti_pro = get_post_meta($post_id, '_costo_in_punti_pro', true);
                $tipo = $costo_in_punti_pro > 0 ? 'pro' : 'blu';

                $files[] = array(
                    //'id' => $post_id,
                    'hid' => $product_hid,
                    'name' => get_the_title(),
                    'upload_date' => get_the_date(),
                    'description' => get_the_excerpt(),
                    'anno_accademico' => implode(', ', $anni_accademici),
                    'tipo_istituto' => implode(', ', $tipi_istituti),
                    'nome_istituto' => implode(', ', $nomi_istituti),
                    'nome_corso' => implode(', ', $nomi_corsi),
                    'nome_corso_di_laurea' => implode(', ', $nomi_corsi_laurea),
                    'costo_in_punti_pro' => get_post_meta($post_id, '_costo_in_punti_pro', true),
                    'costo_in_punti_blu' => get_post_meta($post_id, '_costo_in_punti_blu', true),
                    'document_link' => get_permalink($post_id),
                    'stato_approvazione' => get_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true),
                    'recensione' => get_post_meta($post_id, '_wc_average_rating', true),
                    'order_id' => $order_id,
                    'author_id' => $author_id,
                    'order_hid' => get_order_hash($order_id),
                    'tipo' => $tipo,
                    'gruppo' => $gruppo,
                    'pulsante_fatturazione' =>mostra_pulsante_richiesta_fattura_php(get_order_hash($order_id), $author_hid),
                );

                if ($recensione_utente) {
                    $files[count($files) - 1]['recensione_utente_testo'] = $recensione_utente['review_text'];
                    $files[count($files) - 1]['recensione_utente_voto'] = $recensione_utente['rating'];
                } else {
                    $files[count($files) - 1]['recensione_utente_testo'] = null;
                    $files[count($files) - 1]['recensione_utente_voto'] = null;
                }

                


            }
        }

        wp_reset_postdata();
    }

    // Restituisci i file in formato JSON
    wp_send_json_success($files);
    wp_die();
}