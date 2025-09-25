
jQuery(document).ready(function($) {
    $('#downloadButton').on('click', function(e) {
        e.preventDefault();
        
        var _product_id = $(this).data('product-id');

        $(this).prop('disabled', true);

        var button = $(this);
        var img = $('#img-cloud-download');
        var load = $('#icon-loading-download');

        img.hide();
        load.prop('hidden', false);
        
    
        // Disabilita il pulsante e mostra il loader
        

        $.ajax({
            url: env_call_download.ajaxurl, // Usa l'url localizzato
            type: 'POST',
            data: {
                action: 'controllo_download', // Nome della tua azione Ajax
                product_id: _product_id,
                nonce: env_call_download.nonce // Usa il nonce per verificare
            },
            success: function(response) {
                if (response.success) {

                    // Se il controllo è passato, effettua il download con il link generato da WooCommerce

                    window.location.href = response.data.download_url;
                    var extra_msg = response.data.message ? response.data.message : "";

                    // Show Bootstrap modal
                    var modal = $('<div class="modal fade" tabindex="-1">' +
                        '<div class="modal-dialog">' +
                            '<div class="modal-content">' +
                                '<div class="modal-header">' +
                                    '<h5 class="modal-title">Download completato</h5>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                                '</div>' +
                                '<div class="modal-body">' +
                                    '<p>Il download è stato completato con successo.</p>' +
                                '</div>' +
                                '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Chiudi</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>');
                    // Add event listener for modal hidden event
                    modal.on('hidden.bs.modal', function() {
                        setTimeout(function() {
                            location.reload();
                        }, 2000); // Delay the reload to allow the download to start
                    });

                    // Show the modal
                    modal.modal('show');

                    // Mostra il popup di successo
                    //showCustomAlert("Download effettuato!", "Grazie per aver scaricato il file! Ricordati di lasciare una recensione quando lo avrai analizzato. " + extra_msg, "bg-success btn-success")
                } else {

                    error_code = response.data.error_code ? response.data.error_code : "";

                    console.log("Error code: " + error_code);

                    switch (error_code) {
                        case 'no_enough_points_pro':
                            // Mostra il popup di errore
                            showCustomAlert(
                                "Non hai abbastanza punti per effettuare il download",
                                "<div style='text-align: center;'>Non hai abbastanza punti Pro? Nessun problema!<br>" +
                                "<a href=\"" + env_call_download.ricarica_punti_pro_url + "\" class=\"btn btn-warning mt-2\">Effettua subito una ricarica</a></div>",
                                "bg-warning btn-warning"
                            );
                            break;
                        case 'no_subscription':
                            // Mostra il popup di errore
                            showCustomAlert(
                                "Non hai una sottoscrizione Pro attiva",
                                "<div style='text-align: center;'>Per usufruire di tutti i vantaggi del Pro,<br>" +
                                "<a href=\"" + env_call_download.sottoscrizione_pro_url + "\" class=\"btn btn-warning mt-2\">Sottoscrivi il tuo abbonamento Pro!</a></div>",
                                "bg-warning btn-warning"
                            );
                            break;
                        default:
                            // Mostra il popup di errore
                            showCustomAlert("Non è stato possibile effettuare il download", response.data.message, "bg-warning btn-warning");
                            break;
                    }

                    // Mostra il popup di errore
                    //showCustomAlert("Non è stato possibile effettuare il download", response.data.message, "bg-warning btn-warning");
                    //alert('Errore: ' + response.data.message);
                }
                button.prop('disabled', false);
                img.show();
                load.prop('hidden', true);
                update_points_in_page();
            },
            error: function(xhr, status, error) {
                showCustomAlert('Si è verificato un errore durante la richiesta: ' + error, 'bg-danger btn-danger');
                button.prop('disabled', false);
            }
        });

        

    });
});
