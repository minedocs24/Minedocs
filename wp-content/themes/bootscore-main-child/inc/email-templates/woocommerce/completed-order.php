<?php
/**
 * Template per l'email di ordine completato
 */

// Verifica che le variabili necessarie siano definite
if (!isset($order) || !isset($user_name)) {
    return;
}

$subject = esc_html(get_bloginfo('name')) . ' - Ordine Completato';

$body = '
    <p>Ciao ' . esc_html($user_name) . ',</p>

    <p>Il tuo ordine è stato completato con successo!</p>

    <div class="alert alert-success">
        <p>Grazie per aver acquistato su ' . esc_html(get_bloginfo('name')) . '.</p>
    </div>

    <p>Dettagli dell\'ordine:</p>
    <ul>
        <li>Numero ordine: ' . $order->get_order_number() . '</li>
        <li>Data: ' . $order->get_date_created()->date_i18n('d/m/Y H:i') . '</li>
        <li>Totale: ' . $order->get_formatted_order_total() . '</li>
    </ul>

    <h3>Prodotti acquistati:</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #ccc;">Prodotto</th>
                <th style="text-align: center; border-bottom: 1px solid #ccc;">Quantità</th>
                <th style="text-align: right; border-bottom: 1px solid #ccc;">Totale</th>
            </tr>
        </thead>
        <tbody>';

        foreach ( $order->get_items() as $item ) :
            $product = $item->get_product();
            $body .=
            '<tr>
                <td>' . $item->get_name() . '</td>
                <td style="text-align: center;">' . $item->get_quantity() . '</td>
                <td style="text-align: right;">' . wc_price($item->get_total() + $item->get_total_tax()). '</td>
            </tr>';
        endforeach;
        $body .= '
        </tbody>
    </table>

    <p>Puoi visualizzare i dettagli completi del tuo ordine nel tuo account:</p>

    <p style="text-align: center;">
        <a href="' . esc_url(PROFILO_UTENTE_MOVIMENTI) . '" class="button">Visualizza Ordine</a>
    </p>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
)); 