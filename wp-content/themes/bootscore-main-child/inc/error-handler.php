<?php

function handle_critical_error($error_details = array()) {
    // Ottieni gli amministratori
    $admins = get_users(array('role' => 'administrator'));

    $error = error_get_last();
    $error_details['error'] = $error;
    // Prepara i dettagli dell'errore
    $error_details['user_id'] = get_current_user_id();

    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {

        // Ottieni le ultime 20 righe del debug.log
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';

        if (file_exists($debug_log_path)) {
            $lines = file($debug_log_path);
            $last_lines = array_slice($lines, -20);
            $error_details['debug_log'] = implode("\n", $last_lines);
        }

        $subject = 'MineDocs - Errore critico sul sito ';
        ob_start();
        include(get_stylesheet_directory() . '/inc/email-templates/email-errore-critico.php');
        $message = ob_get_clean();
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        // Invia email a tutti gli amministratori
        foreach ($admins as $admin) {
            $error_details['admin_email'] = $admin->user_email;
            wp_mail($admin->user_email, $subject, $message, $headers);
        }

        // Reindirizza l'utente a una pagina di errore personalizzata
        if (!headers_sent()) {
            wp_redirect(home_url('/error-page'));
            exit;
        } else {
            echo '<script>window.location.href="' . esc_url(home_url('/error-page')) . '";</script>';
            exit;
        }
    }
}