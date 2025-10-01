<?php
$args = array(
    'subject' => 'Link Scaduto',
    'body' => '
    <div style="text-align: center;">
        <h2 style="color: #dc3545; margin-bottom: 20px;">Link Scaduto</h2>
        <p>Il link di conferma è scaduto.</p>
        <p>Per motivi di sicurezza, i link di conferma sono validi solo per 24 ore.</p>
        <p>Puoi richiedere una nuova email di conferma dalla sezione "Impostazioni" del tuo profilo.</p>
        <a href="' . home_url('/profilo-utente/') . '" class="button">Vai al tuo profilo</a>
    </div>
    '
);

include(get_stylesheet_directory() . '/inc/email-templates/minedocs-email-header.php');
