<?php

function enqueue_fatturazione_venditori_scripts() {
   
    wp_enqueue_script('fatturazione-venditori', get_stylesheet_directory_uri() . '/assets/js/fatturazione-venditori.js', array('jquery'), null, true);
    wp_localize_script('fatturazione-venditori', 'env_fatturazione_venditori', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fatturazione_venditori_nonce'),
        'get_order_details_nonce' => wp_create_nonce('get_order_details_nonce'),
        'richiesta_img' => get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/time.svg',
        'get_order_details_nonce_venditore' => wp_create_nonce('get_order_details_nonce_venditore'),
        'send_invoice_to_buyer_nonce' => wp_create_nonce('send_invoice_to_buyer_nonce')
    ));

}

add_action('wp_enqueue_scripts', 'enqueue_fatturazione_venditori_scripts', 20);

function mostra_pulsante_richiesta_fattura($order_hid, $venditore_id) {

    $order_id = get_order_id_by_hash($order_hid);
    if (!$order_id) {
        return; // Se non troviamo l'ID dell'ordine, non fare nulla
    }

    $order = wc_get_order($order_id);
    $stato_fattura = get_richiesta_fattura_venditore($order_id);

    $badge = '';
    $tooltip = '';
    
    

    if ($stato_fattura == 'non_richiesta') {
        $tooltip = 'Richiedi fattura al venditore';
        
    } elseif ($stato_fattura == 'richiesta') {
        $tooltip = 'Richiesta di fattura inviata al venditore';
        $badge = '<span id="badge-fatt-richiesta-'.$order_hid.'" class="badge badge-pill badge-success rounded-5" style="background-color: orange; position: absolute; bottom: -5px; right: -5px; "><img src="' . get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/time.svg" alt="Apri" width="10" height="10" /></span>';
    } elseif ($stato_fattura == 'caricata') {
        $tooltip = 'Il venditore ha comunicato di aver emesso ed inviato la fattura.';
        $badge = '<span id="badge-fatt-caricata-'.$order_hid.'" class="badge badge-pill badge-success rounded-5" style="background-color: #28a745; position: absolute; bottom: -5px; right: -5px; "><img src="' . get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/tick.svg" alt="Apri" width="10" height="10" /></span>';
    } elseif ($stato_fattura == 'non_richiedibile_venditore_cancellato') {
        $tooltip = 'Il venditore non è più attivo. Non è possibile richiedere la fattura.';
        $badge = '<span id="badge-fatt-non-richiesta-'.$order_hid.'" class="badge badge-pill badge-success rounded-5" style="background-color: #dc3545; position: absolute; bottom: -5px; right: -5px; "><img src="' . get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/cross.svg" alt="Apri" width="10" height="10" /></span>';
    }
    else {
        $tooltip = 'Richiedi fattura al venditore';
    }


    ?>
<div style="position: relative; display: inline-block;">
    <button href="" tooltip="<?php echo $tooltip; ?>" target="_blank" class="btn-actions btn-link-request-invoice"
        data-order-id="<?php echo $order_hid; ?>" data-venditore-id="<?php echo $venditore_id; ?>"
        data-status="<?php echo $stato_fattura; ?>"
        style="position: relative;">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/invoice.svg"
            alt="Apri" width="16" height="16" />
    </button>
    <?php echo $badge; ?>
</div>

<?php

}


function mostra_pulsante_richiesta_fattura_php($order_hid, $venditore_id) {

    $order_id = get_order_id_by_hash($order_hid);
    if (!$order_id) {
        return ''; // Se non troviamo l'ID dell'ordine, restituiamo una stringa vuota
    }

    $order = wc_get_order($order_id);
    $stato_fattura = get_richiesta_fattura_venditore($order_id);

    $badge = '';
    $tooltip = '';

    if ($stato_fattura == 'non_richiesta') {
        $tooltip = 'Richiedi fattura al venditore';
    } elseif ($stato_fattura == 'richiesta') {
        $tooltip = 'Richiesta di fattura inviata al venditore';
        $badge = '<span id="badge-fatt-richiesta-' . $order_hid . '" class="badge badge-pill badge-success rounded-5" style="background-color: orange; position: absolute; bottom: -5px; right: -5px;"><img src="' . get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/time.svg" alt="Apri" width="10" height="10" /></span>';
    } elseif ($stato_fattura == 'caricata') {
        $tooltip = 'Il venditore ha comunicato di aver emesso ed inviato la fattura.';
        $badge = '<span id="badge-fatt-caricata-' . $order_hid . '" class="badge badge-pill badge-success rounded-5" style="background-color: #28a745; position: absolute; bottom: -5px; right: -5px;"><img src="' . get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/tick.svg" alt="Apri" width="10" height="10" /></span>';
    } elseif ($stato_fattura == 'non_richiedibile_venditore_cancellato') {
        $tooltip = 'Il venditore non è più attivo. Non è possibile richiedere la fattura.';
        $badge = '<span id="badge-fatt-non-richiesta-' . $order_hid . '" class="badge badge-pill badge-success rounded-5" style="background-color: #dc3545; position: absolute; bottom: -5px; right: -5px;"><img src="' . get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/cross.svg" alt="Apri" width="10" height="10" /></span>';
    } 
    
    else {
        $tooltip = 'Richiedi fattura al venditore';
    }

    $button_html = '<div style="position: relative; display: inline-block;">';
    $button_html .= '<button href="" tooltip="' . $tooltip . '" target="_blank" class="btn-actions btn-link-request-invoice"';
    $button_html .= ' data-order-id="' . $order_hid . '" data-venditore-id="' . $venditore_id . '"';
    $button_html .= ' data-status="' . $stato_fattura . '" style="position: relative;">';
    $button_html .= '<img src="' . get_stylesheet_directory_uri() . '/assets/img/user/sezione-documenti-caricati/invoice.svg" alt="Apri" width="16" height="16" />';
    $button_html .= '</button>';
    $button_html .= $badge;
    $button_html .= '</div>';

    return $button_html;
}



add_action('wp_footer', 'add_modal_for_fatturazione_venditori');

function add_modal_for_fatturazione_venditori() {
    ?>
<div class="modal fade" id="fatturazioneVenditoriModal" tabindex="-1" role="dialog" aria-labelledby="fatturazioneVenditoriModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fatturazioneVenditoriModalLabel">Conferma invio dati fatturazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="alert-send-invoice" role="alert">
                    Per richiedere la fattura, invieremo i tuoi dati di fatturazione
                </div>

                <div>
                    <h5>Riepilogo acquisti:</h5>
                    
                <table class="table table-sm table-riepilogo-prodotti">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome articolo</th>
                        </tr>
                    </thead>
                    <tbody id="product-names-table">
                        <!-- I nomi dei prodotti verranno inseriti qui tramite JavaScript -->
                    </tbody>
                </table>
                </div>
                <hr>
                <div>
                    <h5>Riepilogo dati di fatturazione:</h5>
                    <table class="table table-sm table-riepilogo-fatturazione">
                        <tbody>
                            <tr>
                                <td>Nome</td>
                                <td id="billing-first-name" class="placeholder-wave required-field">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Cognome</td>
                                <td id="billing-last-name" class="placeholder-wave required-field">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Indirizzo</td>
                                <td>
                                    <span id="billing-address-1" class="placeholder-wave required-field"><span class="placeholder placeholder-lg col-12 bg-secondary"></span></span>
                                    <span id="billing-address-2" class="placeholder-wave"><span class="placeholder placeholder-lg col-12 bg-secondary"></span></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Città</td>
                                <td id="billing-city" class="placeholder-wave required-field">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>CAP</td>
                                <td id="billing-postcode" class="placeholder-wave required-field">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Paese</td>
                                <td id="billing-country" class="placeholder-wave required-field">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td id="billing-email" class="placeholder-wave required-field">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Telefono</td>
                                <td id="billing-phone" class="placeholder-wave">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Codice fiscale</td>
                                <td id="billing-codice-fiscale" class="placeholder-wave required-field">
                                    <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>Questi dati saranno inviati al venditore. Confermi?</p>
                <small>Nota: il venditore potrebbe richiedere ulteriori informazioni per emettere la fattura. Minedocs non è responsabile per l'emissione della fattura da parte del venditore.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" id="confirm-send-invoice">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;" id="loading-conferma-richiesta-fattura"></span>
                    Conferma
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    .table-riepilogo-fatturazione td {
        font-weight: bold;
    }
.table-riepilogo-fatturazione td {
    font-weight: normal;
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
}

.table-riepilogo-fatturazione tr:last-child td {
    border-bottom: none;
}

.table-riepilogo-fatturazione {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
}

.table-riepilogo-fatturazione td:first-child {
    width: 30%;
    color: #6c757d;
}

.table-riepilogo-fatturazione td:last-child {
    width: 70%;
}

.table-riepilogo-prodotti {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
}

.table-riepilogo-prodotti th {
    color: #6c757d;
}

.table-riepilogo-prodotti td {
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
}

.table-riepilogo-prodotti tr:last-child td {
    border-bottom: none;
}


</style>
<?php
}


add_action('wp_footer', 'add_modal_for_venditore_fatturazione');

function add_modal_for_venditore_fatturazione() {
    ?>
    <div class="modal fade" id="venditoreFatturazioneModal" tabindex="-1" role="dialog" aria-labelledby="venditoreFatturazioneModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="venditoreFatturazioneModalLabel">Richiesta di emissione fattura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" id="alert-invoice-request" role="alert">
                        L'acquirente ha richiesto l'emissione della fattura. Di seguito i dati di fatturazione dell'acquirente.
                    </div>

                    <div>
                        <h5>Riepilogo dati di fatturazione:</h5>
                        <table class="table table-sm table-riepilogo-fatturazione">
                            <tbody>
                                <tr>
                                    <td>Nome</td>
                                    <td id="buyer-billing-first-name" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Cognome</td>
                                    <td id="buyer-billing-last-name" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Indirizzo</td>
                                    <td>
                                        <span id="buyer-billing-address-1" class="placeholder-wave"><span class="placeholder placeholder-lg col-12 bg-secondary"></span></span>
                                        <span id="buyer-billing-address-2" class="placeholder-wave"><span class="placeholder placeholder-lg col-12 bg-secondary"></span></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Città</td>
                                    <td id="buyer-billing-city" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CAP</td>
                                    <td id="buyer-billing-postcode" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Paese</td>
                                    <td id="buyer-billing-country" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td id="buyer-billing-email" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Telefono</td>
                                    <td id="buyer-billing-phone" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Codice fiscale</td>
                                    <td id="buyer-billing-codice-fiscale" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <h5>Riepilogo acquisti:</h5>
                        <table class="table table-sm table-riepilogo-prodotti">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome articolo</th>
                                </tr>
                            </thead>
                            <tbody id="buyer-product-names-table">
                                <!-- I nomi dei prodotti verranno inseriti qui tramite JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <div>
                        <h5>Importo da fatturare:</h5>
                        <table class="table table-sm table-riepilogo-importi">
                            <tbody>
                                <tr>
                                    <td>Totale</td>
                                    <td id="gross-earnings" class="placeholder-wave">
                                        <span class="placeholder placeholder-lg col-12 bg-secondary"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p>Confermi l'emissione e l'invio della fattura all'acquirente?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="confirm-send-invoice-to-buyer">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;" id="loading-conferma-invio-fattura"></span>    
                        Conferma l'emissione
                    </button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .table-riepilogo-fatturazione td {
            font-weight: bold;
        }
        .table-riepilogo-fatturazione td {
            font-weight: normal;
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }

        .table-riepilogo-fatturazione tr:last-child td {
            border-bottom: none;
        }

        .table-riepilogo-fatturazione {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table-riepilogo-fatturazione td:first-child {
            width: 30%;
            color: #6c757d;
        }

        .table-riepilogo-fatturazione td:last-child {
            width: 70%;
        }
    </style>
    <?php
}

add_action('wp_ajax_send_invoice_request', 'send_invoice_request');
add_action('wp_ajax_nopriv_send_invoice_request', 'send_invoice_request');

function mostra_pulsante_fattura_venditore($order_hid) {
    error_log('Mostra pulsante fattura venditore: ' . $order_hid);
    $order_id = get_order_id_by_hash($order_hid);
    error_log('AAAA?OOOOO'. $order_id);
    $stato_fattura = get_richiesta_fattura_venditore($order_id);
    $tooltip = '';

    if ($stato_fattura == 'non_richiesta') {
        $tooltip = 'Fattura non richiesta';
        echo '<button class="rounded-3 btn btn-sm btn-secondary" disabled tooltip="' . $tooltip . '">Fattura non richiesta</button>';
    } elseif ($stato_fattura == 'richiesta') {
        $tooltip = 'L\'acquirente ha richiesto la fattura. Clicca qui per leggere i dettagli e per confermare l\'invio della fattura.';
        echo '<button class="rounded-3 btn btn-sm btn-warning btn-link-send-invoice-to-customer" tooltip="' . $tooltip . '" data-order-id="'.$order_hid.'" >Fattura richiesta</button>';
    } elseif ($stato_fattura == 'caricata') {
        $tooltip = 'Fattura inviata';
        echo '<button class="rounded-3 btn btn-sm btn-success" disabled tooltip="' . $tooltip . '">Fattura inviata</button>';
    } elseif ($stato_fattura == 'non_richiedibile_venditore_cancellato') {
        $tooltip = 'Il venditore non è più attivo. Non è possibile richiedere la fattura.';
        echo '<button class="rounded-3 btn btn-sm btn-secondary" disabled tooltip="' . $tooltip . '">Fattura non richiesta</button>';
    } 
    else {
        $tooltip = 'Fattura non richiesta';
        echo '<button class="rounded-3 btn btn-sm btn-secondary" disabled tooltip="' . $tooltip . '">Fattura non richiesta</button>';
    }
}

function send_invoice_request() {

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Utente non loggato.'));
        return;
    }

    check_ajax_referer('fatturazione_venditori_nonce', 'nonce');

    $order_hid = sanitize_text_field($_POST['order_id']);

    $order_id = get_order_id_by_hash($order_hid);
    

    $details = get_order_details_for_invoicing($order_hid);


    if (!$details) {
        wp_send_json_error(array('message' => 'Ordine non trovato.'));
        return;
    }

    $order = wc_get_order($order_id);

    $billing_info = $details['billing_info'];
    $product_names = $details['product_names'];
    $dettagli_importi = $details['dettagli_importi'];
    $venditore_id = $details['venditore_info']['venditore_id'];
    $venditore_email = $details['venditore_info']['venditore_email'];

    $to = $venditore_email;


    $subject = 'Richiesta di emissione fattura per l\'ordine #' . $order_id;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/richiesta-fattura-venditore.php');
    $message = ob_get_clean();


    error_log('Sending email to ' . $to);
    $mail_sent = wp_mail($to, $subject, $message, $headers);

    if (!$mail_sent) {
        wp_send_json_error(array('message' => 'Errore nell\'invio della richiesta di fattura.'));
        return;
    }

    set_richiesta_fattura_venditore($order_id, 'richiesta');
    wp_send_json_success(array('message' => 'Richiesta di fattura inviata con successo.'));
}

add_action('wp_ajax_get_order_details_for_invoicing', 'get_order_details_for_invoicing_json');
add_action('wp_ajax_nopriv_get_order_details_for_invoicing', 'get_order_details_for_invoicing_json');

function get_order_details_for_invoicing_json() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Utente non loggato.'));
        return;
    }
    
    check_ajax_referer('get_order_details_nonce', 'get_order_details_nonce');

    error_log('Getting order details for invoicing');
    error_log('Order ID: ' . $_POST['order_id']);
    $order_hid = sanitize_text_field($_POST['order_id']);

    $details = get_order_details_for_invoicing($order_hid);

    if (!$details) {
        wp_send_json_error(array('message' => 'Dettagli dell\'ordine non trovati.'));
        return;
    }

    wp_send_json_success($details);
}

function get_order_details_for_invoicing($order_hid) {
    $order_id = get_order_id_by_hash($order_hid);
    if (!$order_id) {
        return false; // Se non troviamo l'ID dell'ordine, non fare nulla
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return false;
    }

    $items = $order->get_items();
    $product_names = array();
    foreach ($items as $item) {
        $product_names[] = $item->get_name();
    }


    $author_id = get_id_venditore($order_id);
    $author_email = get_user($author_id)->user_email;

    $venditore_info = array(
        'venditore_id' => $author_id,
        'venditore_email' => $author_email
    );

    $guadagno_lordo = get_guadagno_lordo_venditore($order_id);
    $guadagno_netto = get_guadagno_netto_venditore($order_id);
    $commissione = get_commissione_minedocs($order_id);

    $dettagli_importi = array(
        'guadagno_lordo' => number_format($guadagno_lordo, 2, ',', '.') . ' €',
        'guadagno_netto' => number_format($guadagno_netto, 2, ',', '.') . ' €',
        'commissione' => number_format($commissione, 2, ',', '.') . ' €'
    );

    $billing_info = array(
        'nome' => $order->get_billing_first_name(),
        'cognome' => $order->get_billing_last_name(),
        'indirizzo' => $order->get_billing_address_1(),
        'civico' => $order->get_billing_address_2(),
        'città' => $order->get_billing_city(),
        'cap' => $order->get_billing_postcode(),
        'paese' => $order->get_billing_country(),
        'email' => $order->get_billing_email(),
        'telefono' => $order->get_billing_phone(),
        'codice_fiscale' => $order->get_meta('_billing_codice_fiscale')
    );

    return array(
        'product_names' => $product_names,
        'billing_info' => $billing_info,
        'dettagli_importi' => $dettagli_importi,
        'venditore_info' => $venditore_info
    );
}


add_action('wp_ajax_get_buyer_billing_details', 'get_buyer_billing_details');
add_action('wp_ajax_nopriv_get_buyer_billing_details', 'get_buyer_billing_details');

function get_buyer_billing_details() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Utente non loggato.'));
        return;
    }
    
    check_ajax_referer('get_order_details_nonce_venditore', 'nonce');

    error_log('Getting buyer billing details');

    $order_hid = sanitize_text_field($_POST['order_id']);

    $order_id = get_order_id_by_hash($order_hid);
    if (!$order_id) {
        wp_send_json_error(array('message' => 'Ordine non trovato.'));
        return;
    }
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error(array('message' => 'Ordine non trovato.'));
        return;
    }

    $details = get_order_details_for_invoicing($order_hid);

    if (!$details) {
        wp_send_json_error(array('message' => 'Dettagli dell\'ordine non trovati.'));
        return;
    }

    wp_send_json_success($details);
}

add_action('wp_ajax_send_invoice_to_buyer', 'send_invoice_to_buyer');
add_action('wp_ajax_nopriv_send_invoice_to_buyer', 'send_invoice_to_buyer');

function send_invoice_to_buyer() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Utente non loggato.'));
        return;
    }
    
    check_ajax_referer('send_invoice_to_buyer_nonce', 'nonce');

    $order_hid = sanitize_text_field($_POST['order_id']);

    $order_id = get_order_id_by_hash($order_hid);
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error(array('message' => 'Ordine non trovato.'));
        return;
    }

    set_richiesta_fattura_venditore($order_id, 'caricata');
    wp_send_json_success(array('message' => 'Fattura inviata con successo.'));
}


