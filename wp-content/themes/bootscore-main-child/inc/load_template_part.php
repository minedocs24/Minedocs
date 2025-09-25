<?php
// Funzione che gestisce la chiamata ajax get_template_part_code che restituisce il codice di un template part
function get_template_part_code() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }

    // Sanitizza e valida l'input
    $template_part = sanitize_text_field($_POST['template_part']);
    $args = isset($_POST['args']) ? array_map('sanitize_text_field', $_POST['args']) : [];

    // Inizia l'output buffering
    ob_start();

    // Carica il template part con gli argomenti
    get_template_part($template_part, null, $args);

    // Ottieni il contenuto del buffer e pulisci il buffer
    $output = ob_get_clean();

    // Restituisci il codice del template part
    wp_send_json_success(['template_code' => $output]);
}
// add_action('wp_ajax_get_template_part_code', 'get_template_part_code');
// add_action('wp_ajax_nopriv_get_template_part_code', 'get_template_part_code');

// Funzione che mette in coda lo script del file load_template_part.js
function enqueue_load_template_part_script() {
    wp_enqueue_script(
        'load-template-part',
        get_stylesheet_directory_uri() . '/assets/js/load_template_part.js',
        ['jquery'],
        null,
        true
    );

    // Localizza lo script per passare l'URL dell'admin-ajax.php
    wp_localize_script('load-template-part', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_load_template_part_script');