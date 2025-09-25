function caricaPulsantiFatturazione(){
    // Rimuovo eventuali eventi click precedenti
    jQuery('.btn-link-request-invoice').off('click');
    jQuery('#confirm-send-invoice').off('click');
    jQuery('#confirm-send-invoice-to-buyer').off('click');
    jQuery('.btn-link-send-invoice-to-customer').off('click');

    jQuery('.btn-link-request-invoice').on('click', function(e) {
        e.preventDefault();

        $status = jQuery(this).data('status');
        if ($status == 'non_richiesta') {
            
            var order_id = jQuery(this).data('order-id');
            var product_name = jQuery(this).data('product-name');
            var billing_info = jQuery(this).data('billing-info');
            
            jQuery('#modal-order-id').text(order_id);

            jQuery.ajax({
                url: env_fatturazione_venditori.ajax_url,
                type: 'POST',
                data: {
                action: 'get_order_details_for_invoicing',
                get_order_details_nonce: env_fatturazione_venditori.get_order_details_nonce,
                order_id: order_id
                },
                beforeSend: function() {
                    jQuery('#product-names-table')
                    jQuery('#confirm-send-invoice').prop('disabled', true);
                    jQuery('#billing-first-name').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-last-name').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-address-1').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-address-2').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-city').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-postcode').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-country').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-email').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-phone').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                    jQuery('#billing-codice-fiscale').html('<span class="placeholder placeholder-lg col-12 bg-secondary"></span>');
                },

                complete: function() {
                    var allFieldsFilled = true;
                    jQuery('#fatturazioneVenditoriModal .required-field').each(function() {
                        if (jQuery(this).text().trim() === '') {
                            allFieldsFilled = false;
                            return false;
                        }
                    });
                    if (allFieldsFilled) {
                        jQuery('#confirm-send-invoice').prop('disabled', false);
                    } else {
                        jQuery('#confirm-send-invoice').prop('disabled', true);
                        showCustomAlert('Attenzione', 'Attenzione: alcuni campi necessari alla fatturazione non sono compilati. Vai nelle impostazioni del tuo profilo ed aggiorna i dati di fatturazione.', 'bg-warning btn-warning');
                    }

                    //jQuery('#confirm-send-invoice').prop('disabled', false);
                },

                success: function(response) {
                if (response.success) {
                    console.log(response);
                    var product_names = response.data.product_names;
                    var billing_info = response.data.billing_info;
                    //console.log(billing_info);

                    jQuery('#product-names-table').empty();
                    product_names.forEach(function(name) {           
                        jQuery('#product-names-table').append('<tr><td>' + (product_names.indexOf(name) + 1) + '</td><td>' + name + '</td></tr>');
                    
                    });
                    
                    jQuery('#billing-first-name').text(billing_info.nome);
                    jQuery('#billing-last-name').text(billing_info.cognome);
                    jQuery('#billing-address-1').text(billing_info.indirizzo);
                    jQuery('#billing-address-2').text(billing_info.civico);
                    jQuery('#billing-city').text(billing_info.città);
                    jQuery('#billing-postcode').text(billing_info.cap);
                    jQuery('#billing-country').text(billing_info.paese);
                    jQuery('#billing-email').text(billing_info.email);
                    jQuery('#billing-phone').text(billing_info.telefono);
                    jQuery('#billing-codice-fiscale').text(billing_info.codice_fiscale);

                    jQuery('#confirm-send-invoice').data('order-id', order_id);
                    
                } else {
                    showCustomAlert('Errore', 'Errore durante il recupero dei dettagli dell\'ordine.', 'bg-danger btn-danger');
                }
                },
                error: function(){
                    console.log("errore");
                }

            });

            jQuery('#fatturazioneVenditoriModal').modal('show');
        }
    });

    jQuery('#confirm-send-invoice').on('click', function() {
        var order_id = jQuery(this).data('order-id');
        
        jQuery.ajax({
            url: env_fatturazione_venditori.ajax_url,
            type: 'POST',
            data: {
                action: 'send_invoice_request',
                nonce: env_fatturazione_venditori.nonce,
                order_id: order_id,
            
            },
            beforeSend: function() {
                jQuery('#loading-conferma-richiesta-fattura').show();
            },

            complete: function() {
                jQuery('#loading-conferma-richiesta-fattura').hide();
            },
            success: function(response) {
                if (response.success) {
                    
                    jQuery('#badge-fatt-richiesta-' + order_id).remove();
                    jQuery('#badge-fatt-caricata-' + order_id).remove();
                    var badge = '<span id="badge-fatt-richiesta-' + order_id + '" class="badge badge-pill badge-success rounded-5" style="background-color: orange; position: absolute; bottom: -5px; right: -5px;"><img src="'+env_fatturazione_venditori.richiesta_img+'" alt="Apri" width="10" height="10" /></span>';
                    jQuery('button[data-order-id="' + order_id + '"]').attr('tooltip', 'Richiesta di fattura inviata al venditore').data('status','richiesta').parent().append(badge);
                    showCustomAlert('Richiesta inviata con successo', 'Abbiamo inviato la tua richiesta di fatturazione al venditore. Riceverai una risposta al più presto.', 'bg-success btn-success');
                    jQuery('#fatturazioneVenditoriModal').modal('hide');
                } else {
                    showCustomAlert('Errore', 'Si è verificato un errore durante l\'invio della richiesta di fatturazione. Riprova più tardi.', 'bg-danger btn-danger');
                }
            }
        });
    });

    
    //-------------VENDITORE----------------

    jQuery('#confirm-send-invoice-to-buyer').on('click', function() {
        var order_id = jQuery(this).data('order-id');
        
        jQuery.ajax({
            url: env_fatturazione_venditori.ajax_url,
            type: 'POST',
            data: {
                action: 'send_invoice_to_buyer',
                nonce: env_fatturazione_venditori.send_invoice_to_buyer_nonce,
                order_id: order_id,
            },
            beforeSend: function() {
                jQuery('#loading-conferma-invio-fattura').show();
            },
            complete: function() {
                jQuery('#loading-conferma-invio-fattura').hide();
            },

            success: function(response) {
                if (response.success) {
                    showCustomAlert('Fattura inviata', 'Grazie per la tua conferma. Avviseremo l\'acquirente che hai provveduto ad emettere la fattura.', 'bg-success btn-success');
                    jQuery('#venditoreFatturazioneModal').modal('hide');
                    cambia_pulsante(order_id, 'caricata');
                    aggiorna_numero_fatture_richieste();
                } else {
                    showCustomAlert('Errore', 'Si è verificato un errore durante la conferma di emissione Riprova più tardi.', 'bg-danger btn-danger');
                }
            }
        });
    });

    jQuery('.btn-link-send-invoice-to-customer').on('click', function(e) {
        e.preventDefault();
        jQuery('#venditoreFatturazioneModal').modal('show');
        var order_id = jQuery(this).data('order-id');
        
        jQuery.ajax({
            url: env_fatturazione_venditori.ajax_url,
            type: 'POST',
            data: {
                action: 'get_buyer_billing_details',
                nonce: env_fatturazione_venditori.get_order_details_nonce_venditore,
                order_id: order_id,
            },
            success: function(response) {
                if (response.success) {
                    var billing_info = response.data.billing_info;
                    var product_names = response.data.product_names;
                    var gross_earnings = response.data.dettagli_importi.guadagno_lordo;

                    jQuery('#buyer-billing-first-name').text(billing_info.nome);
                    jQuery('#buyer-billing-last-name').text(billing_info.cognome);
                    jQuery('#buyer-billing-address-1').text(billing_info.indirizzo);
                    jQuery('#buyer-billing-address-2').text(billing_info.civico);
                    jQuery('#buyer-billing-city').text(billing_info.città);
                    jQuery('#buyer-billing-postcode').text(billing_info.cap);
                    jQuery('#buyer-billing-country').text(billing_info.paese);
                    jQuery('#buyer-billing-email').text(billing_info.email);
                    jQuery('#buyer-billing-phone').text(billing_info.telefono);
                    jQuery('#buyer-billing-codice-fiscale').text(billing_info.codice_fiscale);

                    jQuery('#buyer-product-names-table').empty();
                    product_names.forEach(function(name, index) {
                        jQuery('#buyer-product-names-table').append('<tr><td>' + (index + 1) + '</td><td>' + name + '</td></tr>');
                    });

                    jQuery('#gross-earnings').text(gross_earnings.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' }));

                    jQuery('#confirm-send-invoice-to-buyer').data('order-id', order_id);
                    
                } else {
                    showCustomAlert('Errore', 'Errore durante il recupero dei dettagli di fatturazione dell\'acquirente.', 'bg-danger btn-danger');
                }
            },
            error: function (){
                console.log("errore");
            }
        });
    });
}

jQuery(document).ready(function($) {



    caricaPulsantiFatturazione();

    function aggiorna_numero_fatture_richieste() {

        const tableBody = document.getElementById('order-table-body');
        const rows = Array.from(tableBody.getElementsByTagName('tr'));
        const alertBox = document.querySelector('.alert-box');
        const alert = alertBox.querySelector('.alert');
        const richieste = rows.filter(row => row.querySelector('td:last-child').textContent === 'richiesta').length;
        if (richieste > 0) {
            alert.textContent = `Hai ${richieste} richieste di fatturazione.`;
            alertBox.style.display = 'block';
        } else {
            alertBox.style.display = 'none';
        }
    }

    function cambia_pulsante(order_id, nuovo_stato) {
        
        const tableBody = document.getElementById('order-table-body');
        const rows = Array.from(tableBody.getElementsByTagName('tr'));
        const row = rows.find(row => row.getAttribute('data-order-id') == order_id);
        if (row) {
            const cell = row.querySelector('td:last-child');
            let button;
            if (nuovo_stato === 'non_richiesta') {
            button = '<button class="rounded-3 btn btn-sm btn-secondary" disabled tooltip="Fattura non richiesta">Fattura non richiesta</button>';
            } else if (nuovo_stato === 'richiesta') {
            button = '<button class="rounded-3 btn btn-sm btn-warning btn-link-send-invoice-to-customer" tooltip="L\'acquirente ha richiesto la fattura. Clicca qui per leggere i dettagli e per confermare l\'invio della fattura." data-order-id="' + order_id + '">Fattura richiesta</button>';
            } else if (nuovo_stato === 'caricata') {
            button = '<button class="rounded-3 btn btn-sm btn-success" disabled tooltip="Fattura inviata">Fattura inviata</button>';
            }
            cell.innerHTML = button;
        }
    }

});