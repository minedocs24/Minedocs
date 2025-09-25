<?php

/**
 * Template Name: Login
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();

?>
<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/login/tipo-con-maracas.svg"
    class="cucaracha" style="filter: drop-shadow(1px 1px 3px #0000004d); ">
<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/login/foglia.svg"
    class="foglia-di-fico" style="filter: drop-shadow(1px 1px 3px #0000004d);">
<div class="container container-login position-relative">

    <form class="access-main-wrapper" name="loginform" id="loginform"
        action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
        <div class="container">

            <div class="row variable-gutters justify-content-center pt-4 pt-xl-5">

                <div class="col-lg-5 access-mobile-bg mt-5">
                    <div class="access-login">

                        <div class="access-login-header mb-5">
                            <h1 class="access-login-title text-center">Accedi o Registrati</h1>
                            <p class="text-center">Smetti di cercare la formula magica per studiare... è qui dentro!</p>
                        </div>

                        <div class="access-login-form">
                            <div id="login-fields">
                                <div class="form-group">
                                    <input type="text" name="log" id="login-email-field" class="input form-control" value=""
                                        size="20" autocapitalize="off" aria-describedby="access-form"
                                        placeholder="Inserisci la tua mail">
                                    <small id="login-email-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="pwd" id="login-password-field" class="input form-control"
                                        value="" size="20" aria-describedby="access-form"
                                        placeholder="Inserisci la tua password">
                                    <small id="login-password-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
                                </div>
                            </div>

                            <div id="register-fields" style="display: none;">
                                <div class="form-group">
                                    <input type="text" name="first_name" id="register-first-name" class="input form-control" value=""
                                        placeholder="Nome">
                                    <small id="first-name-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="last_name" id="register-last-name" class="input form-control" value=""
                                        placeholder="Cognome">
                                    <small id="last-name-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" id="register-email" class="input form-control" value=""
                                        placeholder="Email">
                                    <small id="email-error" class="text-danger" style="display: none;">Email non valida.</small>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" id="register-password" class="input form-control" value=""
                                        placeholder="Password">
                                    <small id="password-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
                                    <div id="password-strength-bar" class="progress" style="display: none;">
                                        <div id="password-strength-bar-inner" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="confirm_pwd" id="register-confirm-password" class="input form-control" value=""
                                        placeholder="Ripeti la password">
                                    <small id="confirm-password-error" class="text-danger" style="display: none;">Le password non corrispondono.</small>
                                </div>
                            </div>

                            <div class="row variable-gutters">
                                <div class="col-lg-6 mb-4">
                                    <div class="form-check text-left">
                                        <input name="rememberme2" type="checkbox" id="rememberme2" value="forever"
                                            class="form-check-input" />
                                        <label for="rememberme2" class="form-check-label">
                                            <?php esc_html_e('Remember Me'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4 d-flex justify-content-end">
                                    <p class="text-underline">
                                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"
                                            aria-label="<?php _e('Lost your password?'); ?>">
                                            <?php _e('Lost your password?'); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div id="register-link" class="text-center mt-4">
                                <p>Non hai un account? <button type="button" id="show-register-fields" class="btn btn-link">Registrati</button>
                                </p>
                            </div>
                            <div id="login-button" class="row variable-gutters justify-content-center">
                                <button type="submit"
                                    class="btn btn-outline-primary w-75 mb-3 d-flex align-items-center justify-content-center mx-auto"
                                    name="login" value="Accedi">
                                    <?php _e("Accedi", "minedocs"); ?>
                                </button>
                            </div>
                            <div id="register-button" class="row variable-gutters justify-content-center" style="display: none;">
                                <button type="button" id="register-submit"
                                    class="btn btn-outline-primary w-75 mb-3 d-flex align-items-center justify-content-center mx-auto">
                                    <?php _e("Registrati", "minedocs"); ?>
                                </button>
                            </div>
                            <div id="social-buttons" class="text-center mt-5">
                                <p>Oppure</p>

                                <button type="button" onclick="location.href='<?php echo google_login_url(); ?>';"
                                    class="btn btn-outline-dark w-75 d-flex align-items-center justify-content-center mx-auto">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/login/logo-google.svg"
                                        alt="Google" width="20" class="me-2">
                                    Continua con Google
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
get_template_part( 'template-parts/points-packs/bottom-decoration' );
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const registerFields = document.getElementById('register-fields');
        const loginFields = document.getElementById('login-fields');
        const showRegisterButton = document.getElementById('show-register-fields');
        const registerButton = document.getElementById('register-button');
        const loginButton = document.getElementById('login-button');
        const registerSubmit = document.getElementById('register-submit');
        const registerLink = document.getElementById('register-link');

        const emailField = document.getElementById('register-email');
        const passwordField = document.getElementById('register-password');
        const confirmPasswordField = document.getElementById('register-confirm-password');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthBarInner = document.getElementById('password-strength-bar-inner');
        const firstNameField = document.getElementById('register-first-name');
        const lastNameField = document.getElementById('register-last-name');
        const loginEmailField = document.getElementById('login-email-field');
        const loginPasswordField = document.getElementById('login-password-field');

        const emailError = document.getElementById('email-error');
        const passwordError = document.getElementById('password-error');
        const confirmPasswordError = document.getElementById('confirm-password-error');
        const firstNameError = document.getElementById('first-name-error');
        const lastNameError = document.getElementById('last-name-error');
        const loginEmailError = document.getElementById('login-email-error');
        const loginPasswordError = document.getElementById('login-password-error');

        const messageContainer = document.createElement('div');
        messageContainer.id = 'message-container';
        messageContainer.style.textAlign = 'center';
        messageContainer.style.marginBottom = '20px';
        document.querySelector('.access-login-header').appendChild(messageContainer);

        registerLink.addEventListener('click', function (event) {
            if (event.target && event.target.id === 'show-register-fields') {
                if (loginFields.style.display === 'none') {
                    loginFields.style.display = 'block';
                    registerFields.style.display = 'none';
                    loginButton.style.display = 'block';
                    registerButton.style.display = 'none';
                    showRegisterButton.innerText = 'Registrati';
                    registerLink.querySelector('p').innerHTML = 'Non hai un account? <button type="button" id="show-register-fields" class="btn btn-link">Registrati</button>';
                } else {
                    loginFields.style.display = 'none';
                    registerFields.style.display = 'block';
                    loginButton.style.display = 'none';
                    registerButton.style.display = 'block';
                    showRegisterButton.innerText = 'Accedi';
                    registerLink.querySelector('p').innerHTML = 'Hai già un account? <button type="button" id="show-register-fields" class="btn btn-link">Accedi</button>';
                }
            }
        });

        registerSubmit.addEventListener('click', function () {
            const email = emailField.value;
            const password = passwordField.value;
            const confirmPassword = confirmPasswordField.value;
            const firstName = firstNameField.value;
            const lastName = lastNameField.value;

            let hasError = false;

            // Controllo campi vuoti
            if (!firstName) {
                firstNameError.style.display = 'block';
                firstNameField.classList.add('is-invalid');
                hasError = true;
            } else {
                firstNameError.style.display = 'none';
                firstNameField.classList.remove('is-invalid');
            }

            if (!lastName) {
                lastNameError.style.display = 'block';
                lastNameField.classList.add('is-invalid');
                hasError = true;
            } else {
                lastNameError.style.display = 'none';
                lastNameField.classList.remove('is-invalid');
            }

            if (!email) {
                emailError.style.display = 'block';
                emailField.classList.add('is-invalid');
                hasError = true;
            } else {
                emailError.style.display = 'none';
                emailField.classList.remove('is-invalid');
            }

            if (!password) {
                passwordError.style.display = 'block';
                passwordField.classList.add('is-invalid');
                hasError = true;
            } else {
                passwordError.style.display = 'none';
                passwordField.classList.remove('is-invalid');
            }

            if (!confirmPassword) {
                confirmPasswordError.style.display = 'block';
                confirmPasswordField.classList.add('is-invalid');
                hasError = true;
            } else {
                confirmPasswordError.style.display = 'none';
                confirmPasswordField.classList.remove('is-invalid');
            }

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
            if (strength < 50) {
                passwordError.style.display = 'block';
                passwordField.classList.add('is-invalid');
                passwordError.innerText = 'La password è troppo debole.';
                hasError = true;
            } else {
                passwordError.style.display = 'none';
                passwordField.classList.remove('is-invalid');
            }

            if (hasError) {
                return;
            }

            // Mostra la rotellina di caricamento
            registerSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registrazione in corso...';
            registerSubmit.disabled = true;

            // Invia i dati via AJAX
            const data = new FormData();
            data.append('action', 'register_user');
            data.append('first_name', firstName);
            data.append('last_name', lastName);
            data.append('email', email);
            data.append('password', password);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageContainer.style.color = 'green';
                    messageContainer.innerText = 'Registrazione completata con successo!';
                    // Redirect to the user registration page
                    window.location.href = '<?php echo site_url('wp1/registrazione-utente'); ?>';
                } else {
                    messageContainer.style.color = 'red';
                    messageContainer.innerText = data.data;
                    registerSubmit.innerHTML = 'Registrati';
                    registerSubmit.disabled = false;
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                messageContainer.style.color = 'red';
                messageContainer.innerText = 'Si è verificato un errore. Riprova.';
                registerSubmit.innerHTML = 'Registrati';
                registerSubmit.disabled = false;
            });
        });

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

            // Abilita/disabilita il pulsante di registrazione in base alla forza della password
            registerSubmit.disabled = strength < 50;
        });

        // Abilita/disabilita il pulsante di registrazione in base alla compilazione dei campi
        const fields = [firstNameField, lastNameField, emailField, passwordField, confirmPasswordField];
        fields.forEach(field => {
            field.addEventListener('input', function () {
                const allFieldsFilled = fields.every(f => f.value.trim() !== '');
                const passwordStrength = calculatePasswordStrength(passwordField.value);
                registerSubmit.disabled = !(allFieldsFilled && passwordStrength >= 50);
            });
        });
    });
</script>


<style>
    .container-login {
        margin-top:50px;
        margin-bottom:100px;
    }

    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }

    .fade-out {
        animation: fadeOut 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(10px);
        }
    }

    #submit-div,
    #social-buttons,
    #register-link {
        transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out;
    }

    #submit-div[hidden],
    #social-buttons[hidden],
    #register-link[hidden] {
        opacity: 0;
        visibility: hidden;
    }

    #submit-div:not([hidden]),
    #social-buttons:not([hidden]),
    #register-link:not([hidden]) {
        opacity: 1;
        visibility: visible;
    }

    .cucaracha {
        position: fixed; 
        top: 35%; 
        left: 5%;
        transform:  scale(1.5) scaleX(-1) rotate(-20deg); /* Added scaleX(-1) to reflect horizontally */
        transition: transform 0.5s ease;
        z-index: -1;
    }

    .foglia-di-fico {
        position: fixed; 
        top: 15%; 
        right: 5%;
        transform: scale(1.5) scaleX(-1); /* Added scaleX(-1) to reflect horizontally */
        
        transition: transform 0.5s ease;
        z-index: -1;

    }

    @media (max-width: 1200px) {
        .cucaracha {
            top: 35%; 
            left: 5%;
            transform: scale(1.5) scaleX(-1) rotate(-20deg); /* Added scaleX(-1) to reflect horizontally */
        }

        .foglia-di-fico {
            top: 15%; 
            right: 5%;
            transform: scale(1.5) scaleX(-1); /* Added scaleX(-1) to reflect horizontally */
        }
    }

    @media (max-width: 992px) {
        .cucaracha {
            top: 35%; 
            left: 0%;
            transform: scale(1.4) scaleX(-1) rotate(-20deg); /* Added scaleX(-1) to reflect horizontally */
        }

        .foglia-di-fico {
            top: 15%; 
            right: 0%;
            transform: scale(1.4) scaleX(-1); /* Added scaleX(-1) to reflect horizontally */
        }
    }

    @media (max-width: 768px) {
        .cucaracha {
            top: 30%; 
            left: -10%;
            transform: scale(1.1) scaleX(-1) rotate(-20deg); /* Added scaleX(-1) to reflect horizontally */
        }

        .foglia-di-fico {
            top: 15%; 
            right: -10%;
            transform: scale(1.1) scaleX(-1); /* Added scaleX(-1) to reflect horizontally */
        }
    }

    @media (max-width: 576px) {
        .cucaracha {
            top: 30%; 
            left: -15%;
            transform: scale(0.8) scaleX(-1) rotate(-20deg); /* Added scaleX(-1) to reflect horizontally */
        }

        .foglia-di-fico {
            top: 15%; 
            right: -15%;
            transform: scale(0.8) scaleX(-1); /* Added scaleX(-1) to reflect horizontally */
        }
    }

    #password-strength-bar {
        margin-top: 10px;
        height: 10px;
    }

    #password-strength-bar-inner {
        height: 100%;
    }

</style>


<?php




// Step 3: Aggiungi il pulsante "Accedi con Apple" alla pagina di login
/*function apple_login_url() {
    $client_id = 'com.example.client'; // Sostituisci con il client ID di Apple
    $redirect_uri = 'https://da7c-151-53-250-157.ngrok-free.app/wp1/login'; // URL di callback locale
    $state = wp_create_nonce('apple_login'); // Protezione CSRF
    $scope = 'email name'; // Scopo di accesso
    $auth_url = "https://appleid.apple.com/auth/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}&state={$state}";
    
    return $auth_url;
}*/

get_footer( );