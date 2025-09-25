document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    var modal = document.getElementById("billingDataModal");
    // var modalPrelievo = document.getElementById("prelievoModal");

    // Get the button that opens the modal
    var btn = document.getElementById("edit-billing-data-button");
    // var btnPrelievo = document.getElementById("prelievoButton");

    // Get the <span> element that closes the modal
    var spanPrelievo = document.getElementById("close-prelievo");

    // When the user clicks the button, open the modal 
    btn.onclick = function() {
        modal.style.display = "block";
    }
    // btnPrelievo.onclick = function() {
    //     modalPrelievo.style.display = "block";
    // }

    // When the user clicks on <span> (x), close the modal
    // spanPrelievo.onclick = function() {
    //     modalPrelievo.style.display = "none";
    // }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            resetBillingData();
        }
        // else if (event.target == modalPrelievo) {
        //     modalPrelievo.style.display = "none";
        // }
    }

    // Mostra il campo input e il pulsante di salvataggio
    document.getElementById('edit-paying-method-button').addEventListener('click', function() {
        document.getElementById('paypal_email_display').style.display = 'none';
        document.getElementById('email-input').style.display = 'block';
        document.getElementById('edit-paying-method-button').style.display = 'none';
        document.getElementById('save-paying-method-button').style.display = 'inline-block';
        document.getElementById('cancel-paying-method-button').style.display = 'inline-block';
    });

    // Pulsante annulla modifica mail PayPal
    document.getElementById('cancel-paying-method-button').addEventListener('click', function() {
        document.getElementById('paypal_email_display').style.display = 'block';
        document.getElementById('email-input').style.display = 'none';
        document.getElementById('edit-paying-method-button').style.display = 'inline-block';
        document.getElementById('save-paying-method-button').style.display = 'none';
        document.getElementById('cancel-paying-method-button').style.display = 'none';
        const paypalEmailDisplay = document.getElementById("paypal_email_display").innerText;
        if (paypalEmailDisplay !== "Nessun conto PayPal collegato.") {//c'è già un'email di paypal
            document.getElementById("email-input").value = paypalEmailDisplay;
        } else {
            document.getElementById("email-input").value = "";
        }
    });
});

function resetBillingData() {
    // funzione che resetta i campi del popup modal
    jQuery(document).ready(function($) {
        // var load = $('#icon-loading-edit-billing-data');
        // load.prop('hidden', false);
        // chiamata ajax per ottenere i dati di fatturazione dell'utente
        jQuery.ajax({
            url: env_profilo_utente_guadagni.ajax_url,
            type: 'POST',
            data: {
                action: 'get_billing_data',
                nonce: env_profilo_utente_guadagni.nonce_getBillingData,
            },
            success: function(response) {
                if(response.success){
                    // se la chiamata ha successo, carico i dati nel popup
                    document.getElementById("billing_first_name").value = response.data.billing_first_name;
                    document.getElementById("billing_last_name").value = response.data.billing_last_name;
                    document.getElementById("billing_address").value = response.data.billing_address_1;
                    document.getElementById("billing_address_num").value = response.data.billing_address_num;
                    document.getElementById("billing_city").value = response.data.billing_city;
                    // document.getElementById("billing-province").value = response.data.billing_province;
                    document.getElementById("billing_postcode").value = response.data.billing_postcode;
                    document.getElementById("billing_country").value = response.data.billing_country;
                    document.getElementById("billing_phone").value = response.data.billing_phone;
                    // document.getElementById("billing_email").value = response.data.billing_email;
                    document.getElementById("codice_fiscale").value = response.data.codice_fiscale;
                } else {
                    // se la chiamata non ha successo, mostro un popup di errore
                    showCustomAlert("Impossibile ottenere i dati", "C'è stato un problema con il caricamento dei tuoi dati di fatturazione. Riprova più tardi.","bg-warning btn-warning");
                }
                // nascondo l'icona di caricamento
                // load.prop('hidden', true);
            }
        });
    });
}

function cancelBillingInfo() {
    document.getElementById('billingDataModal').style.display = 'none';
    resetBillingData();
}

function isValidCodiceFiscale(cf) {
    const cfRegex = /^[A-Z]{6}[0-9LMNPQRSTUV]{2}[A-EHLMPR-T][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z]$/i;
    return cfRegex.test(cf);
}

function isValidPartitaIVA(piva) {
    const pivaRegex = /^[0-9]{11}$/;
    return pivaRegex.test(piva);
}

function validateBillingData(billingData) {
    let isValid = true;
    for (const key in billingData) {
        // console.log(key);
        const input = document.getElementById(key);
        const error_label = document.getElementById(key + "_error");
        if (!billingData[key]) {
            input.classList.add('is-invalid');
            error_label.removeAttribute('hidden');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            // console.log(error_label);
            error_label.setAttribute('hidden', true);
        }
    }
    return isValid;
}

function saveBillingInfo(callback){
// funzione che prende i dati inseriti dall'utente all'interno del popup modal e li salva nel database associandoli all'utente
    var billingData = {
        'billing_first_name': document.getElementById('billing_first_name').value,
        'billing_last_name': document.getElementById('billing_last_name').value,
        'billing_address': document.getElementById('billing_address').value,
        'billing_address_num': document.getElementById('billing_address_num').value,
        'billing_city': document.getElementById('billing_city').value,
        // 'billing_province': document.getElementById('billing-province').value,
        'billing_postcode': document.getElementById('billing_postcode').value,
        'billing_country': document.getElementById('billing_country').value,
        'billing_phone': document.getElementById('billing_phone').value,
        'codice_fiscale': document.getElementById('codice_fiscale').value,
    };
    //console.log(billingData);

    if (!validateBillingData(billingData)) {
        //console.log("Missing data");
        return;
    }

    //Applichiamo controlli su codice fiscale e partita iva solo se il paese è l'Italia
    if (billingData.billing_country === 'IT' && !isValidCodiceFiscale(billingData.codice_fiscale) && !isValidPartitaIVA(billingData.codice_fiscale)) {
        showCustomAlert("Errore", "Per favore, inserisci un Codice Fiscale o un numero di Partita IVA valido.", "bg-warning btn-warning");
        return;
    }

    // Ensure jQuery is loaded and the DOM is ready
    jQuery(document).ready(function($) {
        var load = $('#icon-loading-edit-billing-data');

        load.prop('hidden', false);

        // chiamata ajax per salvare i dati nel database
        jQuery.ajax({
            url: callback,
            type: 'POST',
            data: {
                action: 'save_billing_data',
                billingData: billingData,
                nonce: env_profilo_utente_guadagni.nonce_saveBillingData,
            },
            success: function(response) {
                if(response.success){
                    // se la chiamata ha successo, chiudo il popup modal
                    document.getElementById("billingDataModal").style.display = "none";
                    // aggiorno i dati visualizzati nella pagina
                    document.getElementById("billing-last-name-first-name-display").innerHTML = billingData.billing_last_name + " " + billingData.billing_first_name;
                    document.getElementById("billing-info-display").innerHTML = billingData.billing_address + ", " + billingData.billing_address_num + ", " + billingData.billing_city /* + " (" + billingData.billing_province + ") " */ + ", " + billingData.billing_postcode + ", " + billingData.billing_country;
                    document.getElementById("billing-phone-display").innerHTML = billingData.billing_phone;
                    // document.getElementById("billing-email-display").innerHTML = billingData.billing_email;
                    document.getElementById("codice-fiscale-display").innerHTML = billingData.codice_fiscale;
                    // popup di conferma aggiornamento dati
                    showCustomAlert("Dati aggiornati con successo!", "I tuoi dati di fatturazione sono stati modificati correttamente.","bg-success btn-success");
                } else {
                    var errorMessage = response.data.message || "C'è stato un problema con l'aggiornamento dei tuoi dati. Riprova più tardi.";    
                    showCustomAlert("Impossibile aggiornare i dati", errorMessage,"bg-warning btn-warning");
                }
                // nascondo l'icona di caricamento
                load.prop('hidden', true);
            }
        });
    });
}


// funzione per prelevare il saldo quando l'utente clicca sul bottone "Preleva saldo" e: l'importo del saldo è superiore a 10 euro, e sono compilati tutti i campi di fatturazione dell'utente, cioè nessuno dei campi è vuoto. Nel caso in cui fossero presenti campi vuoti, viene visualizzato un messaggio di errore che invita l'utente a completare i dati di fatturazione in basso alla pagina.
function prelevaSaldo(callback, saldoRichiesto){
    // funzione che preleva il saldo dell'utente
    // Ensure jQuery is loaded and the DOM is ready

    
    jQuery(document).ready(function($) {
        var load = $('#icon-loading-withdraw-balance');
        load.prop('hidden', false);
    
        // chiamata ajax per prelevare il saldo
        jQuery.ajax({
            url: callback,
            type: 'POST',
            data: {
                action: 'preleva_saldo',
                saldoRichiesto: saldoRichiesto,
                nonce: env_profilo_utente_guadagni.nonce_withdrawBalance,
            },
            success: function(response) {
                //console.log(response);
                var modal = document.getElementById("prelievoModal");
                // modal.style.display = "none";
                var modalEl = document.getElementById('prelievoModal');
                if (window.bootstrap && bootstrap.Modal && modalEl) {
                  var bsModal = bootstrap.Modal.getInstance(modalEl);
                  if (bsModal) { bsModal.hide(); }
                }
                if(response.success){
                    jQuery('#saldo_utente').html(response.data.saldo_utente_formatted);
                    // se la chiamata ha successo, mostro un popup di conferma
                    showCustomAlert("Saldo prelevato con successo!", "Il tuo saldo è stato prelevato correttamente.","bg-success btn-success");
                    // aggiorno il saldo visualizzato nella pagina
                    // document.getElementById("saldo_utente").innerHTML = "€ " + response.data.saldo_utente.toFixed(2);
                    
                } else {
                    // se la chiamata non ha successo, mostro un popup di errore
                    showCustomAlert("Impossibile prelevare il saldo", response.data.message ,"bg-warning btn-warning");
                }
                // nascondo l'icona di caricamento
                load.prop('hidden', true);
            }
        });
    });
}


// funzione per aggiornare l'email di PayPal dell'utente
function updatePaypalInfo(callback) {
    // Ottieni il valore del nuovo email
    const paypalEmail = document.getElementById('email-input').value;

    if (!paypalEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(paypalEmail)) {
        showCustomAlert("Errore", "Per favore, inserisci un'email di PayPal valida.", "bg-warning btn-warning");
        return;
    }

    // Ensure jQuery is loaded and the DOM is ready
    jQuery(document).ready(function($) {
        var load = $('#icon-loading-update-paypal');
        load.prop('hidden', false);

        // chiamata ajax per salvare i dati PayPal nel database
        jQuery.ajax({
            url: callback,
            type: 'POST',
            data: {
                action: 'update_paypal_info',
                paypal_email: paypalEmail,
                nonce: env_profilo_utente_guadagni.nonce_updatePaypalInfo,
            },
            success: function(response) {
                if(response.success){
                    // se la chiamata ha successo, chiudo il popup modal
                    const paypalModal = document.getElementById("paypalModal");
                    if (paypalModal) {
                        paypalModal.style.display = "none";
                    }
                    // aggiorno i dati visualizzati nella pagina
                    let email = response.data.paypal_email;

                    document.getElementById("paypal_email_display").innerHTML = paypalEmail;
                    // document.getElementById('paypal_email_display').textContent = paypalEmail;
                    // Nascondi il campo input e mostra il testo aggiornato
                    document.getElementById('paypal_email_display').style.display = 'block';
                    document.getElementById('email-input').style.display = 'none';
                    document.getElementById('edit-paying-method-button').style.display = 'inline-block';
                    document.getElementById('save-paying-method-button').style.display = 'none';
                    document.getElementById('cancel-paying-method-button').style.display = 'none';


                        jQuery('#change-paypal-email-box').html('<p class="text-muted mb-0">Mail PayPal in approvazione. Controlla la tua casella e conferma che sei stato tu.</p>');
                    

                    // popup di conferma aggiornamento dati
                    showCustomAlert("Richiesta di modifica Dati PayPal inviata con successo!", "Hai ricevuto una mail in cui ti verrà chiesto di confermare la tua nuova email di PayPal.","bg-success btn-success");
                } else {    
                    showCustomAlert("Impossibile aggiornare i dati", "C'è stato un problema con l'aggiornamento della tua email di PayPal. Riprova più tardi.","bg-warning btn-warning");
                }
                // nascondo l'icona di caricamento
                load.prop('hidden', true);
            }
        });
    });
}