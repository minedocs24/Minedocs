<?php
/**
 * Template per l'email di cambio password
 */

// Verifica che le variabili necessarie siano definite
if (!isset($user) || !isset($new_pass)) {
    return;
}
error_log("Awe");
error_log(print_r($user, true));
$user_name = get_user_meta($user['ID'], 'first_name', true);
$subject = esc_html(get_bloginfo('name')) . ' - Password Modificata';

$body = '
    <p>Ciao ' . esc_html($user_name) . ',</p>

    <p>La password del tuo account è stata modificata con successo.</p>

    <div class="alert alert-success">
        <p>La tua nuova password è stata impostata correttamente.</p>
    </div>

    <p>Se non hai effettuato tu questa modifica, ti preghiamo di contattarci immediatamente.</p>

    <p>Per accedere al tuo account, visita il nostro sito:</p>

    <p style="text-align: center;">
        <a href="' . esc_url(home_url('/login/')) . '" class="button">Accedi al tuo Account</a>
    </p>

    <p>Per motivi di sicurezza, ti consigliamo di:</p>
    <ul>
        <li>Non condividere mai la tua password con altri</li>
        <li>Utilizzare una password unica per il tuo account</li>
        <li>Cambiare regolarmente la tua password</li>
    </ul>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
)); 