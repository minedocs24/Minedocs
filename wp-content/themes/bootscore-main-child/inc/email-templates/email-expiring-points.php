<?php
/**
 * Template per l'email dei punti in scadenza
 */

// Verifica che le variabili necessarie siano definite
if (!isset($user) || !isset($points_expiring) || !isset($expiring_date)) {
    return;
}

$user_name = get_user_meta($user->ID, 'first_name', true) ?: $user->display_name;
$subject = esc_html(get_bloginfo('name')) . ' - I tuoi punti stanno per scadere';

$body = '
    <p>Ciao ' . esc_html($user_name) . ',</p>
    
    <p>Ti scriviamo per informarti che hai <strong>' . $points_expiring . ' punti</strong> che scadranno il <strong>' . date_i18n('d F Y', strtotime($expiring_date)) . '</strong>.</p>

    <div class="alert alert-warning">
        <p><strong>Non lasciare che i tuoi punti vadano sprecati!</strong><br>
        Utilizzali prima della scadenza per accedere a contenuti esclusivi.</p>
    </div>

    <p style="text-align: center;">
        <a href="' . esc_url(home_url()) . '" class="button">Usa i tuoi punti ora</a>
    </p>

    <p>Se hai bisogno di assistenza o hai domande sui tuoi punti, non esitare a contattarci.</p>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
)); 