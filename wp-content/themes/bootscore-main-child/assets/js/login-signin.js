document.addEventListener('DOMContentLoaded', function () {
    const registerSubmit = document.getElementById('register-submit');
    const registerSubmitLoading = document.getElementById('icon-loading-signin');
    const registerSubmitText = document.getElementById('register-button-text');

    const emailField = document.getElementById('register-email');
    const passwordField = document.getElementById('register-password');
    const confirmPasswordField = document.getElementById('register-confirm-password');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthBarInner = document.getElementById('password-strength-bar-inner');
    const firstNameField = document.getElementById('register-first-name');
    const lastNameField = document.getElementById('register-last-name');
    const privacyPolicyCheckbox = document.getElementById('accept-privacy-policy');

    const emailError = document.getElementById('email-error');
    const duplicateEmailError = document.getElementById('duplicated-email-error');
    const passwordError = document.getElementById('password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');
    const firstNameError = document.getElementById('first-name-error');
    const lastNameError = document.getElementById('last-name-error');
    const privacyPolicyError = document.getElementById('privacy-policy-error');
    const captchaError = document.getElementById('captcha-error');

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

    registerSubmit.addEventListener('click', function () {
        const email = emailField.value;
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        const firstName = firstNameField.value;
        const lastName = lastNameField.value;
        duplicateEmailError.style.display = 'none';
        emailField.classList.remove('is-invalid');
        let hasError = false;

        // Controlla che tutti i campi siano compilati
        const fieldsToCheck = [
            { field: firstNameField, error: firstNameError },
            { field: lastNameField, error: lastNameError },
            { field: emailField, error: emailError },
            { field: passwordField, error: passwordError },
            { field: confirmPasswordField, error: confirmPasswordError }
        ];

        fieldsToCheck.forEach(({ field, error }) => {
            if (!field.value.trim()) {
            error.style.display = 'block';
            field.classList.add('is-invalid');
            hasError = true;
            } else {
            error.style.display = 'none';
            field.classList.remove('is-invalid');
            }
        });

        // Controllo email valida
        if (!validateEmail(email)) {
            emailError.style.display = 'block';
            emailField.classList.add('is-invalid');
            hasError = true;
        } else {
            emailError.style.display = 'none';
            emailField.classList.remove('is-invalid');
        }

        // Controllo password corrispondenti
        if (password !== confirmPassword) {
            confirmPasswordError.style.display = 'block';
            confirmPasswordField.classList.add('is-invalid');
            hasError = true;
        } else {
            confirmPasswordError.style.display = 'none';
            confirmPasswordField.classList.remove('is-invalid');
        }

        // Controllo lunghezza password
        if (password.length > 32) {
            passwordError.style.display = 'block';
            passwordField.classList.add('is-invalid');
            passwordError.innerText = 'La password non può superare i 32 caratteri.';
            hasError = true;
        } else {
            passwordError.style.display = 'none';
            passwordField.classList.remove('is-invalid');
        }

        // Controllo forza password
        const strength = calculatePasswordStrength(password);
        if (strength < 100) {
            passwordError.style.display = 'block';
            passwordField.classList.add('is-invalid');
            passwordError.innerText = 'La password è troppo debole.';
            hasError = true;
        } else {
            passwordError.style.display = 'none';
            passwordField.classList.remove('is-invalid');
        }

        // Controllo accettazione privacy policy
        if (!privacyPolicyCheckbox.checked) {
            privacyPolicyError.style.display = 'block';
            hasError = true;
        } else {
            privacyPolicyError.style.display = 'none';
        }

        // Recupera il valore del token reCAPTCHA
        var recaptchaResponse = grecaptcha.getResponse(register_recaptcha_widget);  // Ottieni la risposta del reCAPTCHA
        // Verifica se la risposta è presente
        if (recaptchaResponse.length === 0) {
            captchaError.style.display = 'block';
            hasError = true;
        } else {
            captchaError.style.display = 'none';
        }  

        if (hasError) {
            return;
        }

        // Mostra la rotellina di caricamento
        registerSubmitLoading.removeAttribute('hidden');
        registerSubmitText.innerHTML = 'Registrazione in corso...';
        // registerSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registrazione in corso...';
        registerSubmit.disabled = true;


   
        // Ensure jQuery is loaded and the DOM is ready
        jQuery(document).ready(function($) {
            jQuery.ajax({
                url: env_login_signin.ajax_url,
                type: 'POST',
                data: {
                    action: 'register_user',
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    password: password,
                    recaptcha_response: recaptchaResponse
                },
                success: function(response) {
                if (response.success) {
                    // Redirect to the user registration page
                    //window.location.href = env_login_signin.redirect_url;
                    modal_email_non_verificata(email, 'registrazione');
                } else {
                    if (response.data.message === 'email_exists') {
                        duplicateEmailError.style.display = 'block';
                        emailField.classList.add('is-invalid');
                    } else {
                        emailField.classList.add('is-invalid');
                    }
                    registerSubmit.disabled = false;
                    showCustomAlert('Errore', 'Si è verificato un errore durante la registrazione. Verifica i tuoi dati e riprova.', 'btn-danger bg-danger');
                }
                },
                error: function(xhr, status, error) {
                    console.error('Errore:', error);
                    // registerSubmit.innerHTML = 'Registrati';
                    registerSubmit.disabled = false;
                },
                complete: function() {
                    registerSubmitLoading.setAttribute('hidden', true);
                    registerSubmitText.innerHTML = 'Registrati';
                    grecaptcha.reset(register_recaptcha_widget);
                }
            });
        });
    });
    
    // Mostra la barra di complessità mentre l'utente digita
    passwordField.addEventListener('input', function () {
        const password = passwordField.value;
        const strength = calculatePasswordStrength(password);

        strengthBar.style.display = 'block';
        strengthBarInner.style.width = strength + '%';
        strengthBarInner.setAttribute('aria-valuenow', strength);

        // Cambia il colore della barra in base alla forza
        if (strength <= 25) {
            strengthBarInner.className = 'progress-bar bg-danger';
        } else if (strength <= 50) {
            strengthBarInner.className = 'progress-bar bg-warning';
        } else if (strength <= 75) {
            strengthBarInner.className = 'progress-bar bg-info';
        } else {
            strengthBarInner.className = 'progress-bar bg-success';
        }

    });

    // Gestisce i pallini sulla complessità della pwd inserita mentre viene digitata
    passwordField.addEventListener('input', function () {
        const lengthRequirement = document.getElementById('length-requirement');
        const uppercaseRequirement = document.getElementById('uppercase-requirement');
        const specialRequirement = document.getElementById('special-requirement');
        const numberRequirement = document.getElementById('number-requirement');
        const password = passwordField.value;

        // Controllo lunghezza
        if (password.length >= 8) {
            lengthRequirement.classList.add('valid');
        } else {
            lengthRequirement.classList.remove('valid');
        }

        // Controllo lettera maiuscola
        if (/[A-Z]/.test(password)) {
            uppercaseRequirement.classList.add('valid');
        } else {
            uppercaseRequirement.classList.remove('valid');
        }

        // Controllo carattere speciale
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            specialRequirement.classList.add('valid');
        } else {
            specialRequirement.classList.remove('valid');
        }    

        // Controllo numero
        if (/[0-9]/.test(password)) {
            numberRequirement.classList.add('valid');
        } else {
            numberRequirement.classList.remove('valid');
        }
    });

    // Pulsante mostra pwd per sezione login
    var pwd = document.getElementById('login-password-field');
    var pwdBtn = document.getElementById('login-showpwd');
    pwdBtn.addEventListener('click', function () {
        if (pwd.type === 'password') {
            pwd.type = 'text';
            pwdBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            pwd.type = 'password';
            pwdBtn.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });
});


function showLoginSection(){
    document.getElementById('register-fields').style.display = 'none';
    document.getElementById('login-fields').style.display = 'block';
}

function showRegisterSection(){
    document.getElementById('register-fields').style.display = 'block';
    document.getElementById('login-fields').style.display = 'none';
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Funzione per calcolare la complessità della password
function calculatePasswordStrength(password) {
    let strength = 0;

    // Controlla la lunghezza della password
    if (password.length >= 8) strength += 25;

    // Controlla se contiene numeri
    if (/[0-9]/.test(password)) strength += 25;

    // Controlla se contiene lettere maiuscole
    if (/[A-Z]/.test(password)) strength += 25;

    // Controlla se contiene caratteri speciali
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 25;

    return strength;
}

