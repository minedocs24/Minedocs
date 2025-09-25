<?php
/**
 * Gestione del reset password
 */

// Aggiungi l'azione AJAX per gestire il reset password
add_action('wp_ajax_handle_password_reset', 'handle_password_reset');
add_action('wp_ajax_nopriv_handle_password_reset', 'handle_password_reset');

function handle_password_reset() {
    // Verifica il nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'password_reset_nonce')) {
        wp_send_json_error(array('message' => 'Errore di sicurezza. Riprova.'));
    }

    // Verifica se l'email è stata inviata
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        wp_send_json_error(array('message' => 'Inserisci un indirizzo email valido.'));
    }

    $email = sanitize_email($_POST['email']);
    $user = get_user_by('email', $email);

    if (!$user) {
        wp_send_json_error(array('message' => 'Nessun utente trovato con questo indirizzo email.'));
    }

    // Genera una chiave di reset
    $key = get_password_reset_key($user);
    if (is_wp_error($key)) {
        wp_send_json_error(array('message' => 'Errore nella generazione della chiave di reset. Riprova più tardi.'));
    }

    // Recupera il nome utente
    $user_info = get_user_meta($user->ID, 'first_name', true);

    // Prepara l'email
    $reset_link = network_site_url("reset-password/?key=$key&login=" . rawurlencode($user->user_login), 'login');

    $subject = 'Reset Password - ' . get_bloginfo('name');
    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/email-reset-password.php');
    $body = ob_get_clean();
    
    // Invia l'email
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    $sent = wp_mail($user->user_email, $subject, $body, $headers);

    if ($sent) {
        wp_send_json_success(array('message' => 'Ti abbiamo inviato una email con le istruzioni per reimpostare la password.'));
    } else {
        wp_send_json_error(array('message' => 'Errore nell\'invio dell\'email. Riprova più tardi.'));
    }
}

// Aggiungi l'azione AJAX per gestire il reset effettivo della password
add_action('wp_ajax_handle_new_password', 'handle_new_password');
add_action('wp_ajax_nopriv_handle_new_password', 'handle_new_password');

function handle_new_password() {
    // Verifica il nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'new_password_nonce')) {
        wp_send_json_error(array('message' => 'Errore di sicurezza. Riprova.'));
    }

    // Verifica i parametri necessari
    if (!isset($_POST['key']) || !isset($_POST['login']) || !isset($_POST['new_password'])) {
        wp_send_json_error(array('message' => 'Parametri mancanti. Riprova.'));
    }

    $key = sanitize_text_field($_POST['key']);
    $login = sanitize_text_field($_POST['login']);
    $new_password = $_POST['new_password'];

    // Verifica la chiave di reset
    $user = check_password_reset_key($key, $login);
    if (is_wp_error($user)) {
        wp_send_json_error(array('message' => 'Il link di reset password non è valido o è scaduto.'));
    }

    if(!verifica_complessita_password($new_password)){
        wp_send_json_error(['message' => 'La password non rispetta i requisiti minimi di complessità. <br>'. PASSWORD_COMPLEXITY_MESSAGE]);
        wp_die();
    }

    // Reimposta la password
    reset_password($user, $new_password);

    // Invalida la chiave di reset
    delete_user_meta($user->ID, 'password_reset_key');
    delete_user_meta($user->ID, 'password_reset_time');

    wp_send_json_success(array('message' => 'Password reimpostata con successo. Verrai reindirizzato alla pagina di login.'));
}

// Aggiungi il file al functions.php del tema
function include_password_reset_handler() {
    require_once get_stylesheet_directory() . '/inc/password-reset-handler.php';
}
add_action('after_setup_theme', 'include_password_reset_handler'); 