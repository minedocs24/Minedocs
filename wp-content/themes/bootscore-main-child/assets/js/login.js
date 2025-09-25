jQuery(document).ready(function($) {
    $('button[type="submit"]').on('click', function(event) {
        var isValid = true;

        if ($('#login-email-field').val().trim() === '') {
            $('#login-email-error').show();
            isValid = false;
        } else {
            $('#login-email-error').hide();
        }

        if ($('#login-password-field').val().trim() === '') {
            $('#login-password-error').show();
            isValid = false;
        } else {
            $('#login-password-error').hide();
        }

        if (!isValid) {
            event.preventDefault();
        }
    });


    var n_tentativi = env_login.login_attempts;
    n_tentativi = parseInt(n_tentativi, 10) || 0;
    //console.logog('n_tentativi: ' + n_tentativi);
    //console.log('recaptcha_tentativi: ' + env_login.recaptcha_tentativi);
    if (n_tentativi>env_login.recaptcha_tentativi) {
        $('#login-captcha').attr('hidden', false);
    }


    $('#loginform').on('submit', function(e) {

        $('#icon-loading-login').attr('hidden', false);

        e.preventDefault();
        
        //console.log('GRECAPTCHA: ');
        //console.log(grecaptcha);

        recaptchaResponse = grecaptcha.getResponse(login_recaptcha_widget);  // Ottieni la risposta del reCAPTCHA
        //console.log(recaptchaResponse);


        var data = {
            action: 'verifica_login',
            email:  $('#login-email-field').val(),
            password: $('#login-password-field').val(),
            verifica_email_nonce: env_login.verifica_email_nonce,
            recaptchaResponse: recaptchaResponse
        };

        //console.log(data);

        $.ajax({
            type: 'POST',
            url: env_login.ajax_url,
            data: data,
            success: function(response) {
                //console.log(response);
                if (response.success) {
                    $('#loginform').off('submit').submit();
                } else {
                    //console.log('error');
                    //console.log(response);
                    error_code = response.data.error_code;
                    email = response.data.email;
                    numero_tentativi = response.data.numero_tentativi;
                    n_tentativi = numero_tentativi;
                    if (n_tentativi>env_login.recaptcha_tentativi) {
                        $('#login-captcha').attr('hidden', false);
                    }
                    
                    if (error_code == 'email_not_verified') {
                        modal_email_non_verificata(email);
                    } else {
                        showCustomAlert('Errore', response.data.message, 'btn-danger bg-danger');
                    }
                }
            },
            error: function(xhr, status, error) {
                //console.log('error');
                showCustomAlert('Errore', error, 'btn-danger bg-danger');
            },
            complete: function() {
                grecaptcha.reset(login_recaptcha_widget);
                $('#icon-loading-login').attr('hidden', true);
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Pulsante mostra pwd
    console.log('login.js');
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