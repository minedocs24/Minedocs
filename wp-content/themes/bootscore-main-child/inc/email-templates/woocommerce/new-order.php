<?php
/**
 * Template per l'email di nuovo ordine
 */

// Verifica che le variabili necessarie siano definite
if (!isset($order) || !isset($user_name)) {
    return;
}

$subject = esc_html(get_bloginfo('name')) . ' - Nuovo Ordine Ricevuto';

$body = '
    <p>Ciao ' . esc_html($user_name) . ',</p>

    <p>Grazie per il tuo ordine!</p>

    <div class="alert alert-success">
        <p>Il tuo ordine è stato ricevuto e sarà elaborato al più presto.</p>
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