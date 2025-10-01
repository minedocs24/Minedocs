<?php
$subject = 'Richiesta di emissione fattura';
$body = '
<div style="text-align: left;">
    <p>Gentile venditore,</p>
    <p>Hai ricevuto una richiesta di emissione di fattura/ricevuta. Ti chiediamo di emettere una fattura per l\'ordine #' . $order_id . ' con i seguenti dettagli:</p>

    <h3 style="color: #2d3748; margin-top: 20px;">Riepilogo acquisti:</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tbody>';
        foreach ($product_names as $product_name) {
            $body .= '<tr><td style="padding: 8px; border-bottom: 1px solid #e1e4e8;">' . esc_html($product_name) . '</td></tr>';
        }
        $body .= '
        </tbody>
    </table>

    <h3 style="color: #2d3748; margin-top: 20px;">Totale da fatturare:</h3>
    <p style="font-size: 1.2em; font-weight: bold;">' . $dettagli_importi['guadagno_lordo'] . '</p>

    <h3 style="color: #2d3748; margin-top: 20px;">Riepilogo dati di fatturazione:</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tbody>';
        foreach ($billing_info as $key => $value) {
            $body .= '<tr>
                <td style="padding: 8px; border-bottom: 1px solid #e1e4e8; width: 30%; color: #6c757d;">' . esc_html(ucfirst(str_replace('_', ' ', $key))) . ':</td>
                <td style="padding: 8px; border-bottom: 1px solid #e1e4e8;">' . esc_html($value) . '</td>
            </tr>';
        }
        $body .= '
        </tbody>
    </table>

    <h3 style="color: #2d3748; margin-top: 20px;">Dettagli importi:</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th style="padding: 8px; border-bottom: 2px solid #e1e4e8; text-align: left; color: #6c757d;">Guadagno vendita</th>
                <th style="padding: 8px; border-bottom: 2px solid #e1e4e8; text-align: left; color: #6c757d;">Commissione di vendita</th>
                <th style="padding: 8px; border-bottom: 2px solid #e1e4e8; text-align: left; color: #6c757d;">Guadagno accreditato</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e1e4e8;">' . $dettagli_importi['guadagno_lordo'] . '</td>
                <td style="padding: 8px; border-bottom: 1px solid #e1e4e8;">' . $dettagli_importi['commissione'] . '</td>
                <td style="padding: 8px; border-bottom: 1px solid #e1e4e8;">' . $dettagli_importi['guadagno_netto'] . '</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 20px;">Ricorda che potrai prelevare il tuo saldo tramite ricarica PayPal direttamente dal tuo profilo.</p>

    <p style="margin-top: 20px;">Grazie per la collaborazione.</p>
    <p>Cordiali saluti,</p>
    <p>Il team di Minedocs</p>
</div>
';

// include(get_stylesheet_directory() . '/inc/email-templates/minedocs-email-header.php');
get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
));
