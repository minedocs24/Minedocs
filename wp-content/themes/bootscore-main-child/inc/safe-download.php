<?php

// Aggiungi questo codice nel file functions.php o in un plugin personalizzato

function custom_download_file($download_file, $nonce, $key) {

    error_log("key: ".$key);

    // Controlla che i parametri 'file_id' e 'nonce' siano presenti
    if (!isset($download_file) || !isset($nonce)) {
        
        wp_die('Parametri mancanti. ');
    }

    $file_id = intval($download_file);


    // Verifica che il nonce sia valido
    if (!wp_verify_nonce($nonce, 'download_file_' . $file_id.$key)) {
        wp_die('Accesso non autorizzato.');
    }

    

    // Ottieni il percorso del file basato sull'ID (es. meta o post meta)
    $file_path = wp_get_attachment_url( $file_id ); //  get_post_meta($file_id, '_file_path', true); // Supponiamo che tu stia salvando il percorso in post meta
    error_log($file_path);
    //$file_path = "wp-content/uploads/2024/09/Crittografia.pdf";
    $file_path = str_replace(site_url(  ).'/', '', $file_path);
    error_log($file_path);
    if (!$file_path || !file_exists($file_path)) {
        wp_die('File non trovato.');
    }


    // Invia gli header per il download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    flush(); // Flush buffer per evitare interruzioni
    readfile($file_path); // Leggi il file ed esegui il download
    exit;
}

// Registra l'endpoint per il download
add_action('init', function () {
    add_rewrite_rule('^safe-download-file/([0-9]+)/([A-Za-z0-9]+)/?', 'index.php?download_file=$matches[1]&sec=$matches[2]', 'top');
});

// Aggiungi query var per il download
add_filter('query_vars', function ($vars) {
    $vars[] = 'download_file';
    $vars[] = 'sec';

    return $vars;
});

// Esegui la funzione custom_download_file quando il parametro download_file è presente
add_action('template_redirect', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $key="";
    if (isset($_SESSION['nonce_safe_download'])){
        $key = $_SESSION['nonce_safe_download'];
        unset($_SESSION['nonce_safe_download']);
    }
    $nonce = get_query_var('sec');
    $download_file = get_query_var('download_file');
    if ($download_file) {

        custom_download_file($download_file, $nonce, $key);
    }
});


function create_safe_download_link($file_id) {
    $key = '';#bin2hex(random_bytes(8));
    $nonce = wp_create_nonce('download_file_' . $file_id.$key);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['nonce_safe_download'] = $key;
    $download_url = home_url('/safe-download-file/' . $file_id . '/' . $nonce);

    return $download_url;
}

