<?php

$subject = esc_html(get_bloginfo('name')) . ' - Conferma il tuo indirizzo PayPal';

$body = '
    <p>Ciao ' . esc_html($user_firstName) . ',</p>
    <p>Hai aggiornato il tuo indirizzo PayPal a: <br><strong>' . $paypal_email . '</strong></p>
    <p>Clicca sul link qui sotto per confermare il tuo indirizzo PayPal:</p>
    <a href="' . $confirm_url . '" class="button">Conferma</a>
    <p>Se non hai richiesto questa modifica, ignora questa mail.</p>    
    <p>Grazie,<br>Il team di ' . esc_html(get_bloginfo('name')) . '</p>
';


get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
));
