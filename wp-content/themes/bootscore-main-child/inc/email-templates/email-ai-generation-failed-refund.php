<?php
/**
 * Email: notifica generazione fallita e rimborso punti
 * Variabili attese: $user (WP_User), $points_refunded (int), $job (array)
 */

if (!isset($user) || !isset($points_refunded)) {
    return;
}

$user_name = get_user_meta($user->ID, 'first_name', true) ?: $user->display_name;
$subject = esc_html(get_bloginfo('name')) . ' - Rimborso punti per generazione non riuscita';

$body = '';
$body .= '<p>Ciao ' . esc_html($user_name) . ',</p>';
$body .= '<p>Spiacenti, la generazione del riassunto che hai richiesto non è andata a buon fine e il processo è stato interrotto.</p>';
$body .= '<p>Ti abbiamo riaccreditato <strong>' . intval($points_refunded) . ' punti</strong> sul tuo account. Ci scusiamo per l\'inconveniente.</p>';
$body .= '<p>Puoi riprovare a generare il riassunto in qualsiasi momento dalla pagina <a href="' . esc_url(home_url('/studia-con-ai/')) . '">Studia con AI</a>.</p>';
$body .= '<p>Se hai bisogno di assistenza, rispondi a questa email o visita la nostra <a href="' . esc_url(home_url('/contatti/')) . '">pagina di supporto</a>.</p>';
$body .= '<p>Grazie per la pazienza,</p>';
$body .= '<p><strong>' . esc_html(get_bloginfo('name')) . '</strong></p>';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
));


