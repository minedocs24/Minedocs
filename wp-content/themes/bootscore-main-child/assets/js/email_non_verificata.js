
function modal_email_non_verificata(email, action) {
    // Create the modal HTML
    var modalHTML = '';
    if (action === 'registrazione') {
        modalHTML = `
            <div class="modal fade" id="emailVerificationModal" tabindex="-1" aria-labelledby="emailVerificationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                <div class="modal-content">

                <div class="modal-header">
                <h5 class="modal-title" id="emailVerificationModalLabel">🎉 Registrazione completata! 🎉</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                Grazie per esserti registrato! 🎊<br>
                Per favore <strong>verifica la tua email</strong> per poter accedere.<br>
                Ti abbiamo inviato un'email con un link per la verifica. Se non trovi l'email, controlla la cartella dello spam.<br>
                Potrai richiedere un'altra email di verifica cliccando sul bottone qui sotto.
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="resendEmailButton">
                    <div id="icon-loading-resend-email" class="btn-loader mx-2" hidden style="display: inline-block;">
                        <span class="spinner-border spinner-border-sm"></span>
                     </div>Reinvia mail</button>
                <button type="button" class="btn btn-primary" id="logoutVerifiedMailButton" data-bs-dismiss="modal">OK</button>
                </div>
                </div>
                </div>
            </div>
            `;
    } else {
        modalHTML = `
        <div class="modal fade" id="emailVerificationModal" tabindex="-1" aria-labelledby="emailVerificationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="emailVerificationModalLabel">Email non verificata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                Per favore <strong>verifica la tua email</strong> per poter accedere.<br>
                Ti abbiamo inviato un'email con un link per la verifica. Se non trovi l'email, controlla la cartella dello spam.<br>
                Potrai richiedere un'altra email di verifica cliccando sul bottone qui sotto.
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="resendEmailButton">
                    <div id="icon-loading-resend-email" class="btn-loader mx-2" hidden style="display: inline-block;">
                        <span class="spinner-border spinner-border-sm"></span>
                     </div>Reinvia mail</button>
                <button type="button" class="btn btn-primary" id="logoutVerifiedMailButton" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
            </div>
        </div>
        `;
    }
    // Append the modal to the body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    // Show the modal
    const emailVerificationModal = new bootstrap.Modal(document.getElementById('emailVerificationModal'));
    emailVerificationModal.show();

    // Add event listener to the resend email button
    document.getElementById('resendEmailButton').addEventListener('click', function() {
        resend_verification_email(email);
    });

    // Logout user
    document.getElementById('logoutVerifiedMailButton').addEventListener('click', function() {
        window.location.reload();
    });

    // Reload the page when the modal is closed
    document.getElementById('emailVerificationModal').addEventListener('hidden.bs.modal', function () {
        window.location.reload();
    });

}

function modal_email_verificata() {
    // Create the modal HTML
    var modalHTML = `
        <div class="modal fade" id="emailVerifiedModal" tabindex="-1" aria-labelledby="emailVerifiedModalLabel" aria-hidden="true">
            <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="emailVerifiedModalLabel">Email Verificata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                La tua email è stata verificata con successo! 🎉<br>
                Ora puoi accedere al tuo account.
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
            </div>
        </div>
    `;
    // Append the modal to the body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    // Show the modal
    const emailVerifiedModal = new bootstrap.Modal(document.getElementById('emailVerifiedModal'));
    emailVerifiedModal.show();
}



//TODO: Implementare la funzione modal_invalid_token

function modal_invalid_token(email) {
    // Create the modal HTML
    var modalHTML = `
        <div class="modal fade" id="invalidTokenModal" tabindex="-1" aria-labelledby="invalidTokenModalLabel" aria-hidden="true">
            <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="invalidTokenModalLabel">Link di verifica non valido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                Il link per la verifica dell'indirizzo email non è valido o è scaduto. Per favore richiedi un nuovo link.
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
            </div>
        </div>
    `;
    // Append the modal to the body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    // Show the modal
    const invalidTokenModal = new bootstrap.Modal(document.getElementById('invalidTokenModal'));
    invalidTokenModal.show();

    document.getElementById('resendEmailButton').addEventListener('click', function() {
        resend_verification_email(email);
    });
}


function resend_verification_email(email) {
    const userEmail = email;
    // Show the loading spinner
    document.getElementById('icon-loading-resend-email').removeAttribute('hidden');

    // Make the AJAX call to resend the verification email
    jQuery.ajax({
        url: env_email_non_verificata.ajaxurl,
        type: 'POST',
        data: {
            action: 'reinvia_verifica_email',
            email: userEmail,
            resend_email_nonce: env_email_non_verificata.resend_email_nonce
        },
        success: function (response) {
            console.log(response);
            if (response.success) {
                showCustomAlert('Email inviata', 'Abbiamo inviato un\'email con il link di verifica. Controlla la tua casella di posta.', 'btn-success bg-success');
            } else if (response.data.message === 'too_many_attempts') {
                showCustomAlert('Errore', 'Ti è già stata inviata una mail con il link di verifica. Controlla la tua casella di posta. Assicurati che non sia nella sezione spam.', 'btn-danger bg-danger');
            } else{
                showCustomAlert('Errore', 'Si è verificato un errore durante l\'invio dell\'email. Riprova più tardi.', 'btn-danger bg-danger');
            }
        },
        error: function (xhr, status, error) {
            showCustomAlert('Errore', 'Si è verificato un errore. Riprova più tardi.', 'btn-danger bg-danger');
        },
        complete: function () {
            // Hide the loading spinner
            document.getElementById('icon-loading-resend-email').setAttribute('hidden', true);
            const emailVerificationModal = bootstrap.Modal.getInstance(document.getElementById('emailVerificationModal'));
            emailVerificationModal.hide();
        }
    });
}