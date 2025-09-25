<?php
// Funzione per la registrazione dell'utente
function custom_user_registration() {
    // Verifica il token reCAPTCHA
    error_log('verifica recaptcha');
    // Ottieni il token reCAPTCHA
    if(!isset($_POST['recaptcha_response']) || empty($_POST['recaptcha_response'])){
        error_log('recaptcha_response non settato');
        wp_send_json_error(['message' => 'Errore: reCAPTCHA non valido.']);
    }
    $recaptcha_response = $_POST['recaptcha_response'];
    error_log('recaptcha_response: '.$recaptcha_response);
    
    // Verifica la risposta reCAPTCHA
    $recaptcha_secret = GOOGLE_RECAPTCHA_SECRET_KEY_REGISTER;
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $response_keys = json_decode($response, true);

    // Se il reCAPTCHA non è valido, interrompi l'esecuzione
    if (!$response_keys["success"]) {
        wp_send_json_error(['message' => 'Errore: reCAPTCHA non valido.']);
    }
    error_log(print_r($_POST, true));
    error_log(print_r($response_keys, true)); 

    error_log('custom_user_registration');
    if (isset($_POST['action']) && $_POST['action'] == 'register_user') {
        // Recupera i dati inviati dal modulo
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];  

        // Validazione dei dati
        if(!valida_nome_cognome($first_name)){
            wp_send_json_error(['message' => 'Nome non valido.']);
            wp_die();
        }
        if(!valida_nome_cognome($last_name)){
            wp_send_json_error(['message' => 'Cognome non valido.']);
            wp_die();
        }
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Email non valida.']);
            wp_die();
        }
        if (!verifica_email_valida($email)) {
           wp_send_json_error(['message' => 'Email non valida.']);
           wp_die();
        }
        // La password viene validata nella funzione registra_utente

        // La mail subisce un ulteriore controllo nella funzione registra_utente per fare in modo che 
        // non ci siano utenti con lo stesso indirizzo email

        try {
            registra_utente($email, $password, $first_name, $last_name);
            wp_send_json_success(['message' => 'Registrazione completata con successo.']);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
            
        }
        

    }

    wp_die();
}
add_action('wp_ajax_register_user', 'custom_user_registration');
add_action('wp_ajax_nopriv_register_user', 'custom_user_registration');

function registra_utente($email, $password, $first_name, $last_name, $auth_type = 'standard') {
    // Controlla se l'email è già registrata
    if (email_exists($email)) {
        throw new Exception('email_exists');
    }
    
    if(!verifica_complessita_password($password)){
        throw new Exception('password_non_valida');
    }

    // Crea il nuovo utente
    $user_id = wp_create_user($email, $password, $email);

    set_email_verificata($user_id, $auth_type === 'google');
    set_email_confirmation_token($user_id, bin2hex(random_bytes(16)));
    set_privacy_policy_accettata($user_id, true);
    set_data_accettazione_privacy_policy($user_id, current_time('mysql'));
    set_paypal_email_confermata($user_id, true); //altrimenti non può impostarla


    if (is_wp_error($user_id)) {
        throw new Exception('Errore durante la registrazione.');
    }

    // Aggiorna nome e cognome
    wp_update_user([
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'completamento_informazioni_utente' => false
    ]);

    $user = new WP_User($user_id);
    $user->set_role('customer');

    // Logga l'utente
    //wp_set_auth_cookie($user_id);

    // Invia la mail di verifica
    if ($auth_type === 'standard') {//Se si registra con Google la mail di verifica non serve
        send_verification_email($user_id);
    }



}

// Mostra il metabox nella pagina di modifica dell'utente per il completamento delle informazioni utente
function mostra_metabox_completamento_informazioni_utente($user) {
    $completamento_informazioni_utente = get_user_meta($user->ID, 'completamento_informazioni_utente', true);
    ?>
    <h3>Completamento delle informazioni utente</h3>
    <table class="form-table">
        <tr>
            <th><label for="completamento_informazioni_utente">Completamento delle informazioni utente</label></th>
            <td>
                <label for="completamento_informazioni_utente">
                    <input type="checkbox" name="completamento_informazioni_utente" id="completamento_informazioni_utente" value="1" <?php checked($completamento_informazioni_utente, '1'); ?>>
                    Completato
                </label>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'mostra_metabox_completamento_informazioni_utente');
add_action('edit_user_profile', 'mostra_metabox_completamento_informazioni_utente');

// Salva il valore del metabox quando il profilo utente viene aggiornato
function salva_completamento_informazioni_utente($user_id) {
    if (isset($_POST['completamento_informazioni_utente'])) {
        update_user_meta($user_id, 'completamento_informazioni_utente', '1');
    } else {
        update_user_meta($user_id, 'completamento_informazioni_utente', '0');
    }    
}
add_action('personal_options_update', 'salva_completamento_informazioni_utente');
add_action('edit_user_profile_update', 'salva_completamento_informazioni_utente');

// Aggiungi una funzione AJAX per impostare il flag "completamento_informazioni_utente" quando hai completato la profilazione post registrazione
function imposta_completamento_informazioni() {
    // Verifica se l'utente è loggato
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Utente non autenticato.']);
        wp_die();
    }
    // Verifica il nonce per la sicurezza
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_registration_completed')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    $user_id = get_current_user_id();
    if (!has_user_nickname($user_id) || !has_user_nome_istituto($user_id) || !has_user_nome_corso_di_laurea($user_id) || 
    !has_user_anno_iscrizione($user_id) || !has_user_lingue_extra($user_id)) {
        wp_send_json_error(['message' => 'Compila tutte le informazioni richieste.']);
    }

    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();

    error_log('imposta_completamento_informazioni: ' . $user_id);

    error_log('Attuale valore: '.get_user_meta($user_id, 'completamento_informazioni_utente', true));


    // Imposta il flag "completamento_informazioni_utente" a '1'
    update_user_meta($user_id, 'completamento_informazioni_utente', 1);

    error_log('Nuovo valore: '.get_user_meta($user_id, 'completamento_informazioni_utente', true));

    // Risposta di successo
    wp_send_json_success(['message' => 'Informazioni completate con successo.']);
}
add_action('wp_ajax_imposta_completamento_informazioni', 'imposta_completamento_informazioni');
add_action('wp_ajax_nopriv_imposta_completamento_informazioni', 'imposta_completamento_informazioni');


// Reindirizza la pagina di login di WordPress alla pagina /login
function custom_login_redirect() {
    wp_redirect(home_url('/login'));
    exit();
}
//add_action('login_redirect', 'custom_login_redirect');




// Step 1: Aggiungi il pulsante "Accedi con Google" alla pagina di login
function google_login_url() {
    $client_id = GOOGLE_CLIENT_ID_LOGIN; // Sostituisci con il client ID di Google
    $redirect_uri = GOOGLE_REDIRECT_URI_LOGIN; // URL di callback locale    
    $state = wp_create_nonce('google_login'); // Protezione CSRF
    //$scope = 'email profile https://www.googleapis.com/auth/user.birthday.read https://www.googleapis.com/auth/user.addresses.read https://www.googleapis.com/auth/user.phonenumbers.read'; // Scopo di accesso
    $scope = 'email profile'; // Scopo di accesso
    $auth_url = "https://accounts.google.com/o/oauth2/auth?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}&state={$state}";
    
    return $auth_url;
}


// Step 2: Gestisci il callback di Google dopo il login

if (isset($_GET['code']) && isset($_GET['state']) ) { //&& wp_verify_nonce($_GET['state'], 'google_login')
    $client_id = GOOGLE_CLIENT_ID_LOGIN;
    $client_secret = GOOGLE_CLIENT_SECRET_LOGIN;
    $redirect_uri = GOOGLE_REDIRECT_URI_LOGIN; // URL di callback locale
    $code = $_GET['code'];
    
    // Richiedi il token di accesso da Google
    $response = wp_remote_post('https://oauth2.googleapis.com/token', [
        'body' => [
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code',
        ],
    ]);

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['access_token'])) {
        // Richiedi i dati dell'utente con l'access token
        $user_info_response = wp_remote_get('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $body['access_token']);
        $user_info = json_decode(wp_remote_retrieve_body($user_info_response), true);
        
        // error_log(print_r($user_info_response, true));
        // error_log(print_r($user_info, true));

        
        error_log("con parentesi: ".print_r($user_info['email'], true));
        $email = $user_info['email'];
        if (isset($email)) {
            // Verifica se l'email è registrata in WordPress
            $user = get_user_by('email', $email);
            // error_log(print_r($user, true));
            if ($user) {
                // Esegui il login dell'utente
                wp_set_auth_cookie($user->ID);
                //vai in home page ma non usare wp_redirect

            } else {
                $nome = $user_info['given_name'];
                if (isset($user_info['family_name'])) {
                    $cognome = $user_info['family_name'];
                } else {
                    $cognome = '';
                }                
                $password = generate_strong_password(16);
                registra_utente($email, $password, $nome, $cognome, "google");
                $user = get_user_by('email', $email);
                
                wp_set_auth_cookie($user->ID);

            }
            // Un utente potrebbe registrarsi con l'email e successivamente collegare l'account Google
            set_account_google_collegato($user->ID, true);
            wp_redirect(home_url());
            exit;
        }
    }
}


// Step 3: Aggiungi il pulsante "Accedi con Apple" alla pagina di login
/*function apple_login_url() {
    $client_id = 'com.example.client'; // Sostituisci con il client ID di Apple
    $redirect_uri = 'https://da7c-151-53-250-157.ngrok-free.app/wp1/login'; // URL di callback locale
    $state = wp_create_nonce('apple_login'); // Protezione CSRF
    $scope = 'email name'; // Scopo di accesso
    $auth_url = "https://appleid.apple.com/auth/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}&state={$state}";
    
    return $auth_url;
}*/


// GOOGLE RECAPTCHA
// $page può essere 'login' o 'register'
function aggiungi_recaptcha_al_form($page) {
    if ($page === 'login') {
        $site_key = GOOGLE_RECAPTCHA_SITE_KEY_LOGIN;
        echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
        //echo '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallbackLogin&render=explicit" async defer></script>';
    } else {
        $site_key = GOOGLE_RECAPTCHA_SITE_KEY_REGISTER;
        echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
        //echo '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallbackRegister&render=explicit" async defer></script>';
    }    
}

add_action('wp_enqueue_scripts', 'script_recaptcha');

function script_recaptcha() {
    wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit', array(), null, false);

    wp_add_inline_script('recaptcha', "
        var login_recaptcha_widget; // Definisci la variabile nel contesto globale
        var register_recaptcha_widget; // Definisci la variabile nel contesto globale

        function onloadCallback() {
            if (document.getElementById('login-captcha')) {
                onloadCallbackLogin();
            }
            if (document.getElementById('register-captcha')) {
                onloadCallbackRegister();
            }
        }

        function onloadCallbackLogin() {
            //console.log('onloadCallbackLogin');
            var loginCaptchaElement = document.getElementById('login-captcha');
            loginCaptchaElement.innerHTML = ''; // Assicurati che l'elemento sia vuoto
            login_recaptcha_widget = grecaptcha.render(loginCaptchaElement, {
                'sitekey': '" . esc_js(GOOGLE_RECAPTCHA_SITE_KEY_LOGIN) . "'
            });
        }

        function onloadCallbackRegister() {
            //console.log('onloadCallbackRegister');
            var registerCaptchaElement = document.getElementById('register-captcha');
            registerCaptchaElement.innerHTML = ''; // Assicurati che l'elemento sia vuoto
            register_recaptcha_widget = grecaptcha.render(registerCaptchaElement, {
                'sitekey': '" . esc_js(GOOGLE_RECAPTCHA_SITE_KEY_REGISTER) . "'
            });
        }
    ", 'before');
}


function ajax_verifica_login() {
    error_log("Verifica login");
    check_ajax_referer('verifica_email', 'verifica_email_nonce');
    
    
    //$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    $captchaResponse = isset($_POST['recaptchaResponse']) ? sanitize_text_field($_POST['recaptchaResponse']) : false;

    $rememberme = isset($_POST['rememberme']) ? true : false;

    error_log(print_r($captchaResponse, true));

    $username = isset($_POST['email']) ? $_POST['email'] : '';

    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $username = sanitize_email($username);
    } else {
        $username = sanitize_text_field($username);
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $numero_tentativi = get_login_attempts($ip);
    // $numero_tentativi = isset($_SESSION['login_attempts']) ? intval($_SESSION['login_attempts']) : 0;

    if(ABILITA_CAPTCHA && $numero_tentativi > NUMERO_TENTATIVI_LOGIN_PRIMA_DI_CAPTCHA){ //TODO
        if(!$captchaResponse){
            error_log('recaptcha_response non settato');
            wp_send_json_error(['message' => 'Errore: reCAPTCHA non valido.']);
        }
        
        // Verifica la risposta reCAPTCHA
        $recaptcha_secret = GOOGLE_RECAPTCHA_SECRET_KEY_LOGIN;
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$captchaResponse");
        $response_keys = json_decode($response, true);
    
        error_log(print_r($response_keys, true));
        // Se il reCAPTCHA non è valido, interrompi l'esecuzione
        if (!$response_keys["success"]) {
            wp_send_json_error(['message' => 'Errore: reCAPTCHA non valido.']);
        }
    }


    $user = null;
    error_log('USERNAME PRIMA DI PLAYLIST: '. $username);
    $user = apply_filters('effettua_verifiche_utente', $user, $username, $password);
    // error_log("Utente: " . print_r($user, true));
    if (is_wp_error($user)) {
        $numero_tentativi++;
        registra_tentativo_login_fallito($username);
        wp_send_json_error(array('message' => $user->get_error_message(), 'error_code' => $user->get_error_code(), 'email' => $username, 'numero_tentativi' => $numero_tentativi));
    } else {
        resetta_tentativi_login();
        wp_send_json_success(array('message' => 'Login riuscito', 'user_id' => $user->ID));
    }
}
add_action('wp_ajax_verifica_login', 'ajax_verifica_login');
add_action('wp_ajax_nopriv_verifica_login', 'ajax_verifica_login');





// Funzione AJAX per il reinvio della mail di verifica
function reinvia_verifica_email() {
    // Verifica il nonce per la sicurezza
    check_ajax_referer( 'resend_email_nonce', 'resend_email_nonce' );
    
    // Recupera l'email dai parametri della chiamata AJAX
    if (!isset($_POST['email'])) {
        wp_send_json_error(['message' => 'Email non fornita.']);
        wp_die();
    }

    $email = sanitize_email($_POST['email']);
    if (!valida_email($email)) {
        wp_send_json_error(['message' => 'Email non valida.']);
        wp_die();
    }

    // Ottieni l'ID dell'utente dall'email
    $user = get_user_by('email', $email);
    if (!$user) {
        wp_send_json_error(['message' => 'Errore 5001.']);
        wp_die();
    }

    $user_id = $user->ID;

    if (get_email_verificata($user_id)) {
        wp_send_json_error(['message' => 'Errore 5000.']);
        wp_die();
    }

    // Check sul numero di invii
    $current_time = time();
    $limit = REINVIA_MAIL_NUMERO_MASSIMO_INVII; // Numero massimo di reinvii consentiti
    $time_window = REINVIA_MAIL_TEMPO_DI_BLOCCO; 

    $attempts = get_transient("send_verification_mail_attempts_$email");
    if ($attempts === false) {
        $attempts = [];
    }

    // Filtra i tentativi vecchi
    $attempts = array_filter($attempts, function ($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });

    if (count($attempts) >= $limit) {
        wp_send_json_error(array('message' => 'too_many_attempts'));
    }

    // Aggiunge l'attuale tentativo
    $attempts[] = $current_time;

    // Salva nuovamente i tentativi aggiornati
    set_transient("send_verification_mail_attempts_$email", $attempts, $time_window);

    // Genera un nuovo token di conferma email
    set_email_confirmation_token($user_id, bin2hex(random_bytes(16)));
    // Invia la mail di verifica
    send_verification_email($user_id);

    // Risposta di successo
    wp_send_json_success(['message' => 'Email di verifica inviata con successo.']);
}
add_action('wp_ajax_reinvia_verifica_email', 'reinvia_verifica_email');
add_action('wp_ajax_nopriv_reinvia_verifica_email', 'reinvia_verifica_email');


function generate_strong_password($length = 16) {
    $password = '';
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!?_.&';
    $characters_length = strlen($characters);
    $password .= $characters[rand(0, 25)]; // Lowercase letter
    $password .= $characters[rand(26, 51)]; // Uppercase letter
    $password .= $characters[rand(52, 61)]; // Number
    $password .= $characters[rand(62, 66)]; // Special character

    for ($i = 4; $i < $length; $i++) {
        $password .= $characters[rand(0, $characters_length - 1)];
    }

    return str_shuffle($password);
}

$password = generate_strong_password();