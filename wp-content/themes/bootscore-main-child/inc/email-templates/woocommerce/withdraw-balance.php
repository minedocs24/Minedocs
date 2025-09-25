<?php
/**
 * Template per l'email di conferma prelievo saldo
 */

// Verifica che le variabili necessarie siano definite
if (!isset($user_name) || !isset($amount)) {
    return;
}

$subject = esc_html(get_bloginfo('name')) . ' - Conferma Prelievo Saldo';

$body = '
    <p>Ciao ' . esc_html($user_name) . ',</p>

    <p>Il tuo prelievo è stato elaborato con successo!</p>

    <div class="alert alert-success">
        <p>Il pagamento di €' . number_format($amount, 2, ',', '.') . ' è stato inviato al tuo account PayPal (' . $paypal_email . ').</p>
    </div>

    <p>Dettagli del prelievo:</p>
    <ul>
        <li>Importo: €' . number_format($amount, 2, ',', '.') . '</li>
        <li>Data: ' . date_i18n('d/m/Y H:i') . '</li>
        <li>Metodo di pagamento: PayPal</li>
    </ul>

    <div class="alert alert-info">
        <p>Il pagamento potrebbe richiedere fino a 24 ore per essere visibile nel tuo account PayPal.</p>
    </div>

    <p>Puoi visualizzare tutti i tuoi movimenti nel tuo account:</p>

    <p style="text-align: center;">
        <a href="' . esc_url(PROFILO_UTENTE_MOVIMENTI) . '" class="button">Visualizza Movimenti</a>
    </p>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
)); 