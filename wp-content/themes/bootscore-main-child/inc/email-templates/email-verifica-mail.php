<?php

$subject = 'Verifica la tua email - ' . get_bloginfo('name');

$body = '
    <p>Ciao ' . esc_html($user_info) . ',</p>
    <p>Grazie per esserti registrato su ' . get_bloginfo('name') .'! Per favore, clicca sul seguente link per verificare il tuo indirizzo email.</p>
    <p><a href="' . $verification_link . '" class="button">Verifica Email</a></p>
    <p>Grazie,<br>Il team di ' . esc_html(get_bloginfo('name')) . '</p>
';


get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
));
