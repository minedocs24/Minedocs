<?php
/**
 * Template per l'email di ordine in elaborazione
 */

// Verifica che le variabili necessarie siano definite
if (!isset($order) || !isset($user_name)) {
    return;
}

$subject = esc_html(get_bloginfo('name')) . ' - Ordine in Elaborazione';

$body = '
    <p>Ciao ' . esc_html($user_name) . ',</p>

    <p>Il tuo ordine è stato ricevuto e sta venendo elaborato.</p>

    <div class="alert alert-info">
        <p>Stiamo lavorando al tuo ordine e lo completeremo al più presto.</p>
    </div>

    <p>Dettagli dell\'ordine:</p>
    <ul>
        <li>Numero ordine: ' . $order->get_order_number() . '</li>
        <li>Data: ' . $order->get_date_created()->date_i18n('d/m/Y H:i') . '</li>
        <li>Totale: ' . $order->get_formatted_order_total() . '</li>
    </ul>

    <p>Puoi visualizzare i dettagli completi del tuo ordine nel tuo account:</p>

    <p style="text-align: center;">
        <a href="' . esc_url(PROFILO_UTENTE_MOVIMENTI) . '" class="button">Visualizza Ordine</a>
    </p>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
)); 