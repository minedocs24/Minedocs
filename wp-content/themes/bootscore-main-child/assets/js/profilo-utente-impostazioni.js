document.addEventListener("DOMContentLoaded", function() {
    // Pulsanti mostra pwd
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const passwordField = document.getElementById(targetId);
            const isPassword = passwordField.getAttribute('type') === 'password';

            passwordField.setAttribute('type', isPassword ? 'text' : 'password');
            button.innerHTML = isPassword
                ? '<i class="fas fa-eye-slash"></i>'
                : '<i class="fas fa-eye"></i>';
        });
    });
    // ----------------------------


    // Gestione pulsante Modifica Dati
    const form = document.getElementById("user-form");
    const inputs = form.querySelectorAll("input, select");
    
    const submitButton = form.querySelector(".edit-button");
    // Rileva cambiamenti nei campi
    inputs.forEach(input => {
        input.addEventListener("input", () => {
            submitButton.disabled = false;
            submitButton.classList.remove("disabled");
        });
    });
    //---------------------------

    // Gestione sezione Cambia Password
    const passwordForm = document.getElementById("password-change-form");
    const currentPasswordInput = passwordForm.querySelector("#current_password");
    const newPasswordInput = passwordForm.querySelector("#new_password");
    const confirmPasswordInput = passwordForm.querySelector("#confirm_password");
    const changePwdButton = passwordForm.querySelector("#change-password-button");

    function checkInputs() {
        if (currentPasswordInput.value && newPasswordInput.value && confirmPasswordInput.value) {
            changePwdButton.disabled = false;
            changePwdButton.classList.remove("disabled");
        } else {
            changePwdButton.disabled = true;
            changePwdButton.classList.add("disabled");
        }
    }
    
    // Rileva cambiamenti nei campi
    [currentPasswordInput, newPasswordInput, confirmPasswordInput].forEach(input => {
        input.addEventListener("input", checkInputs);
    });

    // Inizializza lo stato del pulsante
    checkInputs();
    //---------------------------
});

// Abilita il tasto edit se i campi sono stati modificati
function enableSubmitButton() {
    const submitButton = document.getElementById("edit-button");
    submitButton.disabled = false;
    submitButton.classList.remove("disabled");
}

function editUserFields(){
    var nome = document.getElementById('nome').value;
    var cognome = document.getElementById('cognome').value;
    var telefono = document.getElementById('telefono').value;
    var universita = document.getElementById('nome_istituto_select').value;
    var corsoDiLaurea = document.getElementById('nome_corso_di_laurea_select').value;
    var lingua = document.getElementById('lingua_select').value;
    var nazione = document.getElementById('nazione_select').value;

    // Ensure jQuery is loaded and the DOM is ready
    jQuery(document).ready(function($) {
        var load = $('#icon-loading-download');
        var button = $('#edit-button');

        load.prop('hidden', false);
        
        // Invia i dati via AJAX con jquery
        jQuery.ajax({
            url: env_profilo_utente_impostazioni.ajax_url,
            type: 'POST',
            data: {
                action: 'update_user_data',
                nome: nome,
                cognome: cognome,
                telefono: telefono,
                universita: universita,
                corsoDiLaurea: corsoDiLaurea,
                lingua: lingua,
                nazione: nazione,
                nonce: env_profilo_utente_impostazioni.nonce_editUserFields
            },
            success: function(response) {
                //console.log(response);
                if(response.success){
                    showCustomAlert("Dati aggiornati con successo!", "I tuoi dati anagrafici sono stati modificati correttamente.","bg-success btn-success");
                    button.prop('disabled', true);
                } else {
                    showCustomAlert("Impossibile aggiornare i dati", "C'è stato un problema con l'aggiornamento dei tuoi dati. Riprova più tardi.","bg-warning btn-warning");
                }
                load.prop('hidden', true);
            },
            error: function(error) {
                console.error("Errore:", error);
                showCustomAlert("Errore", "C'è stato un problema con la comunicazione con il server. Riprova più tardi.","bg-danger btn-danger");
                // showBootstrapAlert("C'è stato un problema con la comunicazione con il server. Riprova più tardi.", "danger");
            }
        });
    });
}

function disableAutomaticRenew(){
    jQuery(document).ready(function($) {
        var load = $('#icon-loading-disable-renew');
        load.prop('hidden', false);

        var reenableSection = $('#reenable-cancel');
        var disableRenewButton = $('#disable-renew');
        
        // Invia i dati via AJAX con jquery
        jQuery.ajax({
            url: env_profilo_utente_impostazioni.ajax_url,
            type: 'POST',
            data: {
                action: 'handle_suspend_subscription',
                nonce: env_profilo_utente_impostazioni.nonce_disableAutomaticRenew
            },
            success: function(response) {
                //console.log(response);
                if(response.success){
                    showCustomAlert("Rinnovo automatico disattivato", "Il rinnovo automatico è stato disattivato con successo.","bg-success btn-success");
                    hideSection('disable-renew');
                    showSection('reenable-cancel');
                } else {
                    showCustomAlert("Impossibile disattivare il rinnovo automatico", "C'è stato un problema con la disattivazione del rinnovo automatico. Riprova più tardi.","bg-warning btn-warning");
                    console.log(response);
                }
                load.prop('hidden', true);
            },
            error: function(error) {
                console.error("Errore:", error);
                showCustomAlert("Errore", "Errore durante l'aggiornamento dei dati.","bg-danger btn-danger");
            }
        });
    });
}


function enableAutomaticRenew(){
    jQuery(document).ready(function($) {
        var load = $('#icon-loading-enable-renew');
        load.prop('hidden', false);
        var reenableSection = $('#reenable-cancel');
        var disableRenewButton = $('#disable-renew');

        jQuery.ajax({
            url: env_profilo_utente_impostazioni.ajax_url,
            type: 'POST',
            data: {
                action: 'handle_resume_subscription',
                nonce: env_profilo_utente_impostazioni.nonce_enableAutomaticRenew
            },
            success: function(response) {
                //console.log(response);
                if(response.success){
                    showCustomAlert("Rinnovo automatico attivato", "Il rinnovo automatico è stato attivato con successo.","bg-success btn-success");
                    // disableRenewButton.removeClass('d-none');
                    // reenableSection.addClass('d-none');
                    hideSection('reenable-cancel');
                    showSection('disable-renew');
                } else {
                    showCustomAlert("Impossibile attivare il rinnovo automatico", "C'è stato un problema con l'attivazione del rinnovo automatico. Riprova più tardi.","bg-warning btn-warning");
                    //console.log(response);
                }
                load.prop('hidden', true);
            },
            error: function(error) {
                console.error("Errore:", error);
                showCustomAlert("Errore", "Errore durante l'attivazione del rinnovo automatico.","bg-danger btn-danger");
            }
        });
    });
}

function cancelAutomaticRenew() {
    jQuery(document).ready(function($) {
        var load = $('#icon-loading-cancel-renew');
        load.prop('hidden', false);
        var reenableSection = $('#reenable-cancel');
        var choosePlan = $('#pro-plan-choose');

        jQuery.ajax({
            url: env_profilo_utente_impostazioni.ajax_url,
            type: 'POST',
            data: {
                action: 'handle_cancel_subscription',
                nonce: env_profilo_utente_impostazioni.nonce_cancelAutomaticRenew
            },
            success: function(response) {
                //console.log(response);
                if(response.success){
                    showCustomAlert("Rinnovo automatico cancellato", "Il rinnovo automatico è stato cancellato con successo.","bg-success btn-success");
                    // choosePlan.removeClass('d-none');
                    // reenableSection.addClass('d-none');
                    hideSection('reenable-cancel');
                    showSection('pro-plan-choose');
                } else {
                    showCustomAlert("Impossibile cancellare il rinnovo automatico", "C'è stato un problema con la cancellazione del rinnovo automatico. Riprova più tardi.","bg-warning btn-warning");
                    //console.log(response);
                }
                load.prop('hidden', true);
            },
            error: function(error) {
                console.error("Errore:", error);
                showCustomAlert("Errore", "Errore durante la cancellazione del rinnovo automatico.","bg-danger btn-danger");
            }
        });
    });
}

function showSection(sectionId) {
    var section = jQuery('#' + sectionId);
    // section.removeClass('fade-out hide').addClass('fade-in show');
    section.removeClass('d-none');
    section.addClass('d-visible');
}

function hideSection(sectionId) {
    var section = jQuery('#' + sectionId);
    // section.removeClass('fade-in show').addClass('fade-out hide');
    section.removeClass('d-visible');
    section.addClass('d-none');
}

function changePassword() {
    const currentPassword = jQuery('#current_password').val();
    const newPassword = jQuery('#new_password').val();
    const confirmPassword = jQuery('#confirm_password').val();

    if (newPassword !== confirmPassword) {
        showCustomAlert("Errore", "I campi 'Nuova password' e 'Conferma nuova password' non corrispondono.", "bg-danger", "btn-danger");
        return;
    }

    const data = {
        action: 'change_user_password',
        security: env_profilo_utente_impostazioni.nonce_changePassword,
        current_password: currentPassword,
        new_password: newPassword,
    };

    jQuery('#icon-loading-change-password').prop('hidden', false);
    // Reset dei campi
    jQuery('#current_password').val('');
    jQuery('#new_password').val('');
    jQuery('#confirm_password').val('');

    jQuery.ajax({
        url: env_profilo_utente_impostazioni.ajax_url,
        type: 'POST',
        data: data,
        success: function(response) {
            jQuery('#icon-loading-change-password').prop('hidden', true);
            if (response.success) {
                // showCustomAlert("Password cambiata con successo!", "Congratulazioni, la tua password è stata modificata correttamente.","bg-success","btn-success");
                window.location.href = `${window.location.origin}${window.location.pathname}?chg_pwd=true`;
            } else {
                showCustomAlert("Errore", response.data.message, "bg-danger", "btn-danger");
            }
        },
        error: function() {
            jQuery('#icon-loading-change-password').prop('hidden', true);
            showCustomAlert("Errore", "Si è verificato un errore durante il cambio della password.", "bg-danger", "btn-danger");
        }
    });
}

function deleteAccount() {
    const data = {
        action: 'delete_user_account',
        nonce: env_profilo_utente_impostazioni.nonce_deleteAccount,
    };

    jQuery('#icon-loading-delete-account').prop('hidden', false);

    jQuery.ajax({
        url: env_profilo_utente_impostazioni.ajax_url,
        type: 'POST',
        data: data,
        success: function(response) {
            jQuery('#icon-loading-delete-account').prop('hidden', true);
            if (response.success) {
                showCustomAlert("Account eliminato con successo!", "Il tuo account è stato eliminato correttamente.","bg-success","btn-success");
                // Reindirizza alla home page o a un'altra pagina

                window.location.href = env_profilo_utente_impostazioni.home_url;
            } else {
                showCustomAlert("Errore", response.data, "bg-danger", "btn-danger");
            }
        },
        error: function() {
            jQuery('#icon-loading-delete-account').prop('hidden', true);
            showCustomAlert("Errore", "Si è verificato un errore durante l'eliminazione dell'account.", "bg-danger", "btn-danger");
        }
    });
}

function openDeleteAccountModal() {
    // Rimuovi eventuali modali già esistenti
    const existingModal = document.getElementById('deleteAccountModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Crea il contenuto HTML della modal
    const modalHTML = `
        <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteAccountModalLabel">Conferma Eliminazione Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Sei sicuro di voler eliminare il tuo account? Questa azione è irreversibile e comporterà la perdita di tutti i tuoi dati.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelDeleteButton">Annulla</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">
                            <div id="icon-loading-delete-account" class="btn-loader mx-2" hidden style="display: inline-block;">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                            Elimina Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Aggiungi la modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Inizializza la modal con Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();

    // Aggiungi il listener per confermare l'eliminazione dell'account
    document.getElementById('confirmDeleteButton').addEventListener('click', function () {
        deleteAccount();
    });

    // Aggiungi il listener per il pulsante Annulla
    document.getElementById('cancelDeleteButton').addEventListener('click', function () {
        modal.hide();
    });
}

// function showBootstrapAlert(message, type) {
//     const alertHTML = `
//         <div class="alert alert-${type} alert-dismissible fade show" role="alert">
//             ${message}
//             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//         </div>
//     `;
//     document.body.insertAdjacentHTML('beforeend', alertHTML);
// }