<?php
$args = array(
    'subject' => 'Token Non Valido',
    'body' => '
    <div style="text-align: center;">
        <h2 style="color: #dc3545; margin-bottom: 20px;">Token Non Valido</h2>
        <p>Il token di conferma non è valido.</p>
        <p>Questo potrebbe essere dovuto a un link già utilizzato o modificato.</p>
        <p>Puoi richiedere una nuova email di conferma dalla sezione "Impostazioni" del tuo profilo.</p>
        <a href="' . home_url('/profilo-utente/') . '" class="button">Vai al tuo profilo</a>
    </div>
    '
);

include(get_stylesheet_directory() . '/inc/email-templates/minedocs-email-header.php');
