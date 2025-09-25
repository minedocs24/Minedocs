<?php

$subject = esc_html(get_bloginfo('name')) . ' - Errore Critico Segnalato';

$body = '
    <p>Si è verificato un errore critico sul sito.</p>
    
    <h3>Dettagli dell\'errore:</h3>
    <ul>
        <li><strong>Data e ora:</strong> ' . date('Y-m-d H:i:s') . '</li>
        <li><strong>URL:</strong> ' . esc_url($_SERVER['REQUEST_URI']) . '</li>
        <li><strong>IP:</strong> ' . $_SERVER['REMOTE_ADDR'] . '</li>
        <li><strong>User Agent:</strong> ' . $_SERVER['HTTP_USER_AGENT'] . '</li>
    </ul>';

if (isset($error_details['user_id'])) {
    $body .= '<p><strong>Utente:</strong> ' . esc_html($error_details['user_id']) . '</p>';
}

if (isset($error_details['error'])) {
    $body .= '
    <h3>Errore:</h3>
    <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;">' . 
    esc_html($error_details['error']['message']) . 
    '</pre>';
}

if (isset($error_details['debug_log'])) {
    $body .= '
    <h3>Estratto del log di debug:</h3>
    <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;">' . 
    esc_html($error_details['debug_log']) . 
    '</pre>';
}

$body .= '
    <p>Per maggiori dettagli, controlla il file di debug completo.</p>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
)); 