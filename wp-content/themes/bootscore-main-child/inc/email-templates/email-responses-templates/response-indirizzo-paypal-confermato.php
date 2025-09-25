<?php
$args = array(
    'subject' => 'Conferma indirizzo PayPal',
    'body' => '
    <div style="text-align: center;">
        <h2 style="color: #28a745; margin-bottom: 20px;">Indirizzo PayPal Confermato</h2>
        <p>Il tuo indirizzo PayPal è stato confermato con successo.</p>
        <p>Ora puoi procedere con il prelievo del tuo saldo.</p>
        <a href="' . home_url('/profilo-utente/') . '" class="button">Vai al tuo profilo</a>
    </div>
    '
);

include(get_stylesheet_directory() . '/inc/email-templates/minedocs-email-header.php');
?>
