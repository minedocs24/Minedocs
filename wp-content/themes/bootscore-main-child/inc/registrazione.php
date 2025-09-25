<?php


add_action('wp_ajax_profilazione_utente', 'profilazione_utente_callback');
add_action('wp_ajax_nopriv_profilazione_utente', 'profilazione_utente_callback');

function profilazione_utente_callback() {
    error_log('profilazione_utente_callback');
    // Check nonce for security
    check_ajax_referer('profilazione-utente', 'nonce');

    /*if (wp_verify_nonce($_POST['nonce'], 'profilazione-utente') === false) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    } */

    // Get current user
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        wp_send_json_error('User not logged in');
        wp_die();
    }

    if (!isset($_POST['selectedPlan'])) {
        wp_send_json_error('Nessun piano selezionato');
        wp_die();
    }

    $selectedPlan = sanitize_text_field($_POST['selectedPlan']);

    // Imposta il piano selezionato
    if ($selectedPlan != null) {
        if(!valida_stringa($selectedPlan)) {
            wp_send_json_error('Piano non valido: ' . $selectedPlan);
            wp_die();
        }
        $query = new WP_Query(array(
            'name' => 'pro-' . $selectedPlan,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1
        ));
        if ($query->have_posts()) { 
            $product_id  = $query->posts[0]->ID;
            $cart = WC()->cart;
            $cart->add_to_cart($product_id);
            // Redirect to the checkout page
            wp_send_json_success(array('redirect' => wc_get_checkout_url()));
            wp_die();
        } else {
            wp_send_json_error('Piano non valido: ' . $selectedPlan);
            wp_die();
        }
    }
    
    // Send success response
    wp_send_json_success('User profile updated successfully');
    wp_die();
}

function salva_informazioni_utente($user_id) {
    // Get current user
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        wp_send_json_error('User not logged in');
        wp_die();
    }

    // Check nonce for security
    //check_ajax_referer('nonce_save_profilation_data', 'nonce');
    /*if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_save_profilation_data')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
        wp_die();
    }*/

    $profilazione_effettuata = get_completamento_informazioni_utente($user_id);
    if ($profilazione_effettuata) {
        wp_send_json_error('Profilazione già effettuata');
        wp_die();
    }

    // Validate and sanitize inputs
    if (isset($_POST['nickname']) && $_POST['nickname'] != '') {
        set_user_nickname($user_id, sanitize_text_field($_POST['nickname']));
    }

    if (isset($_POST['universita']) && $_POST['universita'] != '') {
        set_user_universita($user_id, sanitize_text_field($_POST['universita']));
    }

    if (isset($_POST['corsoDiLaurea']) && $_POST['corsoDiLaurea'] != '') {
        set_user_corso_di_laurea($user_id, sanitize_text_field($_POST['corsoDiLaurea']));
    }

    if (isset($_POST['anno']) && $_POST['anno'] != '') {
        set_user_anno_iscrizione($user_id, sanitize_text_field($_POST['anno']));
    }

    if (isset($_POST['linguaExtra']) && $_POST['linguaExtra'] != '') {
        set_user_lingue_extra($user_id, sanitize_text_field($_POST['linguaExtra']));
    }

    // Send success response
    wp_send_json_success('User profile updated successfully');
    wp_die();
}
add_action('wp_ajax_salva_dati_profilazione_utente', 'salva_informazioni_utente');
add_action('wp_ajax_nopriv_salva_dati_profilazione_utente', 'salva_informazioni_utente');


function verifica_nickname($nickname) {
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        wp_send_json_error('User not logged in');
        wp_die();
    }

    // Check nonce for security

    check_ajax_referer( 'nonce_verifica_nickname', 'nonce');

    /*if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_verifica_nickname')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }*/

    $nickname = sanitize_text_field($_POST['nickname']);
    if (!valida_stringa($nickname, 3, 20 , '/^[a-zA-Z0-9À-ÿ\s\-\@\.]{3,20}$/')) {
        wp_send_json_error('Nickname non valido');
        wp_die();
    }
    $users = get_users(array(
        'meta_key' => 'nickname',
        'meta_value' => $nickname,
        'fields' => 'ID'
    ));
    
    if (!empty($users)) {
        wp_send_json_error('Nickname già esistente');
        wp_die();
    }

    wp_send_json_success('Nickname disponibile');
    wp_die();
}
add_action('wp_ajax_verifica_nickname', 'verifica_nickname');
add_action('wp_ajax_nopriv_verifica_nickname', 'verifica_nickname');