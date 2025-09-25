<?php
$args = array(
    'subject' => 'Modifica indirizzo PayPal',
    'body' => '
    <div style="text-align: center;">
        <h2 style="color: #dc3545; margin-bottom: 20px;">Modifica Indirizzo PayPal</h2>
        <p>La modifica del tuo indirizzo PayPal è stata impedita.</p>
        <p>Se desideri modificare il tuo indirizzo PayPal, puoi farlo dalla sezione "Impostazioni" del tuo profilo.</p>
        <a href="' . home_url('/profilo-utente/') . '" class="button">Vai al tuo profilo</a>
    </div>
    '
);

include(get_stylesheet_directory() . '/inc/email-templates/minedocs-email-header.php');
?> 