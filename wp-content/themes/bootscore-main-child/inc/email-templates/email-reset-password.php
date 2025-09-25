<?php
/**
 * Template per l'email di reset password
 */

// Verifica che le variabili necessarie siano definite
if (!isset($user) || !isset($reset_link)) {
    return;
}

$user_name = get_user_meta($user->ID, 'first_name', true) ?: $user->display_name;
$subject = esc_html(get_bloginfo('name')) . ' - Reset Password';

$body = '
    <p>Ciao ' . esc_html($user_name) . ',</p>

    <p>Abbiamo ricevuto una richiesta di reset della password per il tuo account.</p>

    <div class="alert alert-info">
        <p>Se non hai richiesto tu il reset della password, puoi ignorare questa email.</p>
    </div>

    <p>Per reimpostare la tua password, clicca sul pulsante qui sotto:</p>

    <p style="text-align: center;">
        <a href="' . esc_url($reset_link) . '" class="button">Reimposta Password</a>
    </p>

    <p>Il link di reset scadrà tra 24 ore per motivi di sicurezza.</p>

    <p>Se il pulsante non funziona, puoi copiare e incollare il seguente link nel tuo browser:</p>
    <p style="word-break: break-all;">' . esc_url($reset_link) . '</p>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
));
