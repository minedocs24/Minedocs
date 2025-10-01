<?php

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('profilo-utente-css', get_stylesheet_directory_uri() . '/assets/css/style-profilo-utente.css');
});



add_action('wp_ajax_update_user_data', 'update_user_data');
add_action('wp_ajax_nopriv_update_user_data', 'update_user_data');

// Aggiorna i dati dell'utente quando clicca su "Modifica dati" da sezione Profilo Utente - Impostazioni
function update_user_data() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    // throw new Exception('test');
    check_ajax_referer('nonce_edit_user_fields', 'nonce');

    // Verifica che tutti i campi richiesti siano presenti (lingua, nazione e telefono sono facoltativi, dunque esclusi dal controllo)
    $required_fields = ['nome', 'cognome', 'universita', 'corsoDiLaurea'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            wp_send_json_error(['message' => "Il campo {$field} è obbligatorio."]);
            wp_die();
        }
    }

    // Verifica che i campi facoltativi siano stati passati nella POST
    $optional_fields = ['telefono', 'lingua', 'nazione'];
    foreach ($optional_fields as $field) {
        if (!isset($_POST[$field])) {
            $_POST[$field] = ''; // Imposta un valore vuoto se non è stato passato
        }
    }

    // Sanitizza e valida l'input
    $nome = sanitize_text_field($_POST['nome']);
    $cognome = sanitize_text_field($_POST['cognome']);
    $telefono = sanitize_text_field($_POST['telefono']);
    $universita = sanitize_text_field($_POST['universita']);
    $corso = sanitize_text_field($_POST['corsoDiLaurea']);
    $lingua = sanitize_text_field($_POST['lingua']);
    $nazione = sanitize_text_field($_POST['nazione']);

    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();
    
    // Aggiorna i campi utente
    set_user_first_name($user_id, $nome);
    set_user_last_name($user_id, $cognome);
    set_user_billing_phone($user_id, $telefono);
    set_user_universita($user_id, $universita);
    set_user_corso_di_laurea($user_id, $corso);
    set_user_lingua($user_id, $lingua);
    set_user_nazione($user_id, $nazione);
    
    wp_send_json_success(['message' => 'Dati aggiornati con successo!']);
}

// funzione che Gestisce la chiamata ajax save_billing_data che si occupa di aggiornare i dati di fatturazione dell'utente
function save_billing_data() {
    error_log("save_billing_data");
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    // check_ajax_referer('nonce_save_billing_data', 'nonce');

    // Verifica che tutti i campi richiesti siano presenti
    $required_fields = ['billing_first_name', 'billing_last_name', 'billing_phone', 'billing_country', 'billing_address', 'billing_address_num', 'billing_city', 'billing_postcode', 'codice_fiscale'];
    foreach ($required_fields as $field) {
        if (!isset($_POST['billingData'][$field]) || empty($_POST['billingData'][$field])) {
            wp_send_json_error(['message' => "Tutti i campi sono obbligatori."]);
            wp_die();
        }
    }

    $billingData = $_POST['billingData'];
    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();
    
    // Aggiorna i campi utente
    set_user_billing_first_name($user_id, sanitize_text_field($billingData['billing_first_name']));
    set_user_billing_last_name($user_id, sanitize_text_field($billingData['billing_last_name']));
    set_user_billing_phone($user_id, sanitize_text_field($billingData['billing_phone']));
    set_user_billing_country($user_id, sanitize_text_field($billingData['billing_country']));
    set_user_billing_address_1($user_id, sanitize_text_field($billingData['billing_address']));
    set_user_billing_address_2($user_id, sanitize_text_field($billingData['billing_address_num']));
    set_user_billing_city($user_id, sanitize_text_field($billingData['billing_city']));
    set_user_billing_postcode($user_id, sanitize_text_field($billingData['billing_postcode']));
    set_user_billing_codice_fiscale($user_id, sanitize_text_field($billingData['codice_fiscale']));
    
    wp_send_json_success(['message' => 'Dati di fatturazione aggiornati con successo!']);
}
add_action('wp_ajax_save_billing_data', 'save_billing_data');
add_action('wp_ajax_nopriv_save_billing_data', 'save_billing_data');

add_action('update_user_meta', function($meta_id, $user_id, $meta_key, $meta_value) {
    
    $lista_campi = array('billing_first_name', 'billing_last_name', 'billing_phone', 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'codice_fiscale');
    
    if (in_array($meta_key, $lista_campi)) {

        error_log("update_user_meta: " . $meta_key . " - " . $meta_value);

        $key_stato_fattura = get_richiesta_fattura_venditore_key();

        $customer_orders = wc_get_orders(array(
            'customer_id' => $user_id,
            'status' => array('processing', 'completed', 'on-hold'),
            'meta_query' => array(
                array(
                    'key' => $key_stato_fattura,
                    'value' => 'non_richiesta',
                    'compare' => '='
                )
            )
        ));

        foreach ($customer_orders as $order) {
            error_log("update_user_meta: order_id: " . $order->get_id());

            switch($meta_key) {
                case 'billing_first_name':
                    $order->set_billing_first_name($meta_value);
                    break;
                case 'billing_last_name':
                    $order->set_billing_last_name($meta_value);
                    break;
                case 'billing_phone':
                    $order->set_billing_phone($meta_value);
                    break;
                case 'billing_country':
                    $order->set_billing_country($meta_value);
                    break;
                case 'billing_address_1':
                    $order->set_billing_address_1($meta_value);
                    break;
                case 'billing_address_2':
                    $order->set_billing_address_2($meta_value);
                    break;
                case 'billing_city':
                    $order->set_billing_city($meta_value);
                    break;
                case 'billing_postcode':
                    $order->set_billing_postcode($meta_value);
                    break;
                case 'codice_fiscale':
                    $order->update_meta_data('_billing_codice_fiscale', $meta_value);
                    break;
            }
            $order->save();
        }
    }

}, 10, 4);

// funzione che restituisce tutti i dati di fatturazione dell'utente corrente
function get_user_billing_data() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();
    // Ottieni i dati di fatturazione dell'utente
    $first_name = get_user_meta($user_id, 'billing_first_name', true);
    $last_name = get_user_meta($user_id, 'billing_last_name', true);
    $billing_phone = get_user_meta($user_id, 'billing_phone', true);
    $billing_country = get_user_meta($user_id, 'billing_country', true);
    $billing_address_1 = get_user_meta($user_id, 'billing_address_1', true);
    $billing_address_num = get_user_meta($user_id, 'billing_address_2', true);
    $billing_city = get_user_meta($user_id, 'billing_city', true);
    $billing_postcode = get_user_meta($user_id, 'billing_postcode', true);
    $codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
    
    //restituisce un array con i dati di fatturazione dell'utente
    return ([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'billing_phone' => $billing_phone,
        'billing_country' => $billing_country,
        'billing_address_1' => $billing_address_1,
        'billing_address_num' => $billing_address_num,
        'billing_city' => $billing_city,
        'billing_postcode' => $billing_postcode,
        'codice_fiscale' => $codice_fiscale
    ]);
}


// fuzione che gestisce la chiamata ajax get_billing_data che si occupa di richiamare la funzione get_user_billing_data e restituire i dati di fatturazione dell'utente corrente
function get_billing_data() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    check_ajax_referer('nonce_get_billing_data', 'nonce');

    // Ottieni i dati di fatturazione dell'utente
    $billing_data = get_user_billing_data();
    // wp_send_json_success($billing_data);
    wp_send_json_success([
        'billing_first_name' => $billing_data['first_name'],
        'billing_last_name' => $billing_data['last_name'],
        'billing_address_1' => $billing_data['billing_address_1'],
        'billing_address_num' => $billing_data['billing_address_num'],
        'billing_city' => $billing_data['billing_city'],
        'billing_postcode' => $billing_data['billing_postcode'],
        'billing_country' => $billing_data['billing_country'],
        'billing_phone' => $billing_data['billing_phone'],
        'codice_fiscale' => $billing_data['codice_fiscale']
    ]);
}
add_action('wp_ajax_get_billing_data', 'get_billing_data');
add_action('wp_ajax_nopriv_get_billing_data', 'get_billing_data');

function aggiungi_saldo_venditore($product_id, $amount, $data_vendor_log){
    // Ottieni l'ID dell'autore del prodotto
    $product = wc_get_product($product_id);
    $vendor_id = get_post($product_id)->post_author;
    //$vendor_id = $product->get_post_data()->post_author;
    // Ottieni il saldo attuale del venditore
    //$saldo_venditore = get_user_meta($vendor_id, 'saldo_utente', true);
    // Aggiorna il saldo del venditore
    //if($saldo_venditore == ''){
    //    $saldo_venditore = 0;
    //}
    //$saldo_venditore += $amount;


    ricarica_wallet($vendor_id, $amount);
    //update_user_meta($vendor_id, 'saldo_utente', $saldo_venditore);
    // Aggiungi il log
    $data_vendor_log['user_id'] = $vendor_id;
    do_action('prodotto_venduto', $vendor_id, $data_vendor_log);
}

// funzione che gestisce la chiamata ajax preleva_saldo che si occupa di prelevare il saldo dell'utente solo quando il saldo dell'utente è superiore a 10 euro e i dati di fatturazione dell'utente sono completi
function preleva_saldo() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    check_ajax_referer('nonce_withdraw_balance', 'nonce');

    if (!isset($_POST['saldoRichiesto']) || empty($_POST['saldoRichiesto'])) {
        wp_send_json_error(['message' => 'Saldo richiesto non valido.']);
    }
    if (!is_numeric($_POST['saldoRichiesto']) || !is_float((float)$_POST['saldoRichiesto'])) {
        wp_send_json_error(['message' => 'Il saldo richiesto deve essere un valore numerico valido.']);
    }
    $saldo_richiesto = floatval($_POST['saldoRichiesto']);
    if ($saldo_richiesto > 1000000) { // Limite massimo per il saldo richiesto
        wp_send_json_error(['message' => 'Il saldo richiesto è troppo grande.']);
    }
    if ($saldo_richiesto < 0) { // Verifica che il saldo richiesto non sia negativo
        wp_send_json_error(['message' => 'Il saldo richiesto non può essere negativo.']);
    }
    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();
    // Ottieni il saldo dell'utente
    $saldo_utente = ottieni_totale_prelevabile($user_id);
    // Ottieni i dati di fatturazione dell'utente
    $billing_data = get_user_billing_data($user_id);
    $first_name = $billing_data['first_name'];
    $last_name = $billing_data['last_name'];
    $billing_address_1 = $billing_data['billing_address_1'];
    $billing_address_num = $billing_data['billing_address_num'];
    $billing_city = $billing_data['billing_city'];
    $billing_postcode = $billing_data['billing_postcode'];
    $billing_country = $billing_data['billing_country'];
    $billing_phone = $billing_data['billing_phone'];
    $codice_fiscale = $billing_data['codice_fiscale'];
    $paypal_email = get_user_meta($user_id, 'paypal_email', true);
    $paypal_email_verified = get_user_meta($user_id, 'paypal_email_confermata', true);
    // Verifica se il saldo dell'utente è superiore a 10 euro e se i dati di fatturazione dell'utente sono completi
    if (!empty($paypal_email)) {
        if ($paypal_email_verified == '1') {
            if ($saldo_utente >= SALDO_MINIMO_PRELEVABILE) {
                if ($saldo_richiesto <= $saldo_utente) {
                    if( $saldo_richiesto >= SALDO_MINIMO_PRELEVABILE){
                        // Verifica se i dati di fatturazione dell'utente sono completi
                        if (!empty($first_name) && !empty($last_name) && !empty($billing_address_1) && !empty($billing_postcode) && !empty($billing_city) && !empty($billing_country) && !empty($codice_fiscale) && !empty($billing_phone) && !empty($billing_address_num)) {
                            // Aggiorna il saldo dell'utente sottraendo il saldo richiesto da quello attuale
                            try {
                                preleva_crediti($user_id, $saldo_richiesto);

                                // Aggiungere il saldo prelevato al conto dell'utente tramite paypal
                                effettua_pagamento_paypal($saldo_richiesto, $paypal_email);

                                $data = array(
                                    'paypal_email' => $paypal_email
                                );

                                do_action('saldo_prelevato', $user_id, $saldo_richiesto, $data);

                                // Invia una mail di conferma al venditore
                                $user_name = get_user_meta($user_id, 'first_name', true) ?: get_userdata($user_id)->display_name;
                                $amount = $saldo_richiesto;
                                ob_start();
                                include(get_stylesheet_directory() . '/inc/email-templates/woocommerce/withdraw-balance.php');
                                $message = ob_get_clean();
                                $headers = array('Content-Type: text/html; charset=UTF-8');
                                wp_mail(get_userdata($user_id)->user_email, $subject, $message, $headers);

                                $saldo_utente = ottieni_totale_prelevabile($user_id);
                                $saldo_utente_formatted = '€ '. number_format($saldo_utente, 2, ',', '.');

                                wp_send_json_success(['message' => 'Saldo prelevato con successo!', 'saldo_utente' => $saldo_utente, 'saldo_utente_formatted' => $saldo_utente_formatted]);
                            } catch (Exception $e) {
                                wp_send_json_error(['message' => 'Errore durante il prelievo del saldo.']);
                            }
                        } else {
                            wp_send_json_error(['message' => 'Dati di fatturazione incompleti.']);
                        }
                    } else {
                        wp_send_json_error(['message' => 'Il saldo minimo prelevabile è di ' . SALDO_MINIMO_PRELEVABILE . ' euro.']);
                    }
                } else {
                    wp_send_json_error(['message' => 'Non disponi del saldo richiesto.']);
                }
            } else {
                wp_send_json_error(['message' => 'Saldo insufficiente.']);
            }
        } else {
            wp_send_json_error(['message' => 'Email PayPal non confermata. Per favore controlla la tua casella di posta e verifica il tuo indirizzo di pagamento PayPal, o aggiorna il tuo indirizzo di pagamento.']);
        }
    } else {
        wp_send_json_error(['message' => 'Email PayPal non impostata.']);
    }

}
add_action('wp_ajax_preleva_saldo', 'preleva_saldo');
add_action('wp_ajax_nopriv_preleva_saldo', 'preleva_saldo');


//Funzione che gestisce la chiamata ajax update_paypal_info che si occupa di aggiornare i dati di paypal dell'utente
function update_paypal_info() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    #check_ajax_referer('nonce_update_paypal_info', 'nonce');

    if (!isset($_POST['paypal_email']) || empty($_POST['paypal_email'])) {
        wp_send_json_error(['message' => 'Email PayPal non valida.']);
    }   

    // Decodifica l'email PayPal prima di sanitizzarla
    $paypal_email = urldecode($_POST['paypal_email']);
    
    // Verifica se l'email è valida e non usa e getta
    if (!verifica_email_valida($paypal_email)) {
        wp_send_json_error(['message' => 'Email PayPal non valida.']);
    }

    $paypal_email = sanitize_email($paypal_email);

    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();


    
    //$expiration = get_user_meta($user_id, 'approval_paypal_expiration', true);
    $expiration = get_user_approval_paypal_expiration($user_id);
    error_log("Expiration: " . $expiration);
    if ($expiration) {
        error_log("Token esistente: " . $expiration);
        if (time() > $expiration) {
            error_log("Token scaduto, reimpostazione email PayPal.");
            set_paypal_email_confermata($user_id, 1);
            delete_user_approval_paypal_expiration($user_id);
            delete_user_paypal_email_temporary($user_id);
        } else {
            error_log("Token non scaduto, email PayPal temporanea: " . $paypal_email);
            $paypal_email_temporary = get_user_paypal_email_temporary($user_id);
            if ($paypal_email_temporary) {
                error_log("Email PayPal temporanea: " . $paypal_email_temporary);
                wp_send_json_error(['message' => 'Email PayPal già in attesa di approvazione.']);
                wp_die();
            }
            if ($paypal_email_temporary == $paypal_email) {
                error_log("Email PayPal temporanea uguale a quella nuova: " . $paypal_email_temporary);
                wp_send_json_error(['message' => 'Email PayPal già in attesa di approvazione.']);
                wp_die();
            }
            error_log("Email PayPal temporanea diversa da quella nuova: " . $paypal_email_temporary);
        }
    }




    // Imposta come temporanea la nuova mail di paypal

    set_user_paypal_email_temporary($user_id, $paypal_email);

    set_paypal_email_confermata($user_id, 0);
    
    send_PayPal_confirmation_email($user_id, $paypal_email);

    wp_send_json_success(['message' => 'Dati di PayPal aggiornati con successo!', 'paypal_email' => $paypal_email]);
}
add_action('wp_ajax_update_paypal_info', 'update_paypal_info');
add_action('wp_ajax_nopriv_update_paypal_info', 'update_paypal_info');


function effettua_pagamento_paypal($saldo_richiesto, $paypal_user_email) {
    // Credenziali PayPal (sostituisci con i tuoi dati)
    $client_id = get_option('paypal_client_id');
    $client_secret = get_option('paypal_client_secret');
    $api_base = PAYPAL_API_BASE_URL; 

    $email = $paypal_user_email; // mail di prova: 'sb-wylhs34653246@personal.example.com'
    $amount = $saldo_richiesto; // formato esempio: '10.00'
    $currency = 'EUR';

    $access_token = get_access_token($client_id, $client_secret, $api_base);
    error_log("Access Token: " . $access_token);
    if (!$access_token) {
        error_log("Errore: impossibile ottenere l'access token.");
        return;
    }

    $sender_batch_id = uniqid("batch_"); // Genera un ID univoco

    $payout_data = [
        "sender_batch_header" => [
            "sender_batch_id" => $sender_batch_id,
            "email_subject" => "Hai ricevuto un pagamento!"
        ],
        "items" => [
            [
                "recipient_type" => "EMAIL",
                "amount" => ["value" => $amount, "currency" => $currency],
                "receiver" => $email,
                "note" => "Prelievo richiesto",
                "sender_item_id" => "item_1"
            ]
        ]
    ];

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer $access_token"
    ];

    $ch = curl_init($api_base . "/v1/payments/payouts");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payout_data));

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    // error_log("Risposta: " . print_r($responseData, true));
    return;
}

function get_access_token($client_id, $client_secret, $api_base) {
    $url = $api_base . "/v1/oauth2/token";
    $auth = base64_encode("$client_id:$client_secret");
    error_log("Auth: " . $auth);
    $headers = [
        "Authorization: Basic $auth",
        "Content-Type: application/x-www-form-urlencoded"
    ];

    $data = "grant_type=client_credentials";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    return $responseData['access_token'] ?? null;
}

add_action('get_template_part', function ($slug, $name, $data) {

    

    if ($slug === 'template-parts/profilo-utente/sidebar-new') {
        wp_enqueue_script('sidebar-js', get_stylesheet_directory_uri() . '/assets/js/sidebar-profilo-utente.js', array('jquery'), null, true);
    }

}, 10, 3);

function send_PayPal_confirmation_email($user_id, $paypal_email) {
    error_log("send_PayPal_confirmation_email");
    
    // genera un token di 32 caratteri
    $token = bin2hex(random_bytes(32));
    $expiration = time() + 24 * 60 * 60; // 24 hours from now
    $user_info = get_userdata($user_id);
    $user_email = $user_info->user_email;
    $user_firstName = get_user_meta($user_id, 'first_name', true);

    // salva il token nel database come meta dell'utente
    update_user_meta($user_id, 'approval_paypal_nonce', $token);
    update_user_meta($user_id, 'approval_paypal_expiration', $expiration);

    $user_hid = get_user_hash_id($user_id);

    

    $confirm_url = add_query_arg([
        'action' => 'confirm_paypal_email',
        'user_id' => $user_hid,
        'token' => $token,
        'confirm' => 'yes'
    ], home_url());
    error_log("Confirm URL: " . $confirm_url);

    $reject_url = add_query_arg([
        'action' => 'confirm_paypal_email',
        'user_id' => $user_hid,
        'token' => $token,
        'confirm' => 'no'
    ], home_url());

    $subject = 'MineDocs - Conferma il tuo indirizzo PayPal';
    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/email-conferma-indirizzo-paypal.php');
    $message = ob_get_clean();
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    wp_mail($user_email, $subject, $message, $headers);
}

add_action('init', function() {
    if (isset($_GET['action']) && $_GET['action'] === 'confirm_paypal_email') {
        $user_hid = sanitize_text_field($_GET['user_id']);
        $token = sanitize_text_field($_GET['token']);
        $confirm = sanitize_text_field($_GET['confirm']);

        $user_id = get_user_id_by_hash($user_hid);

        if(!$user_id) {
            include(get_stylesheet_directory() . '/inc/email-templates/email-responses-templates/response-utente-non-valido.php');
            exit;
        }

        $user_token = get_user_meta($user_id, 'approval_paypal_nonce', true);
        $expiration = get_user_meta($user_id, 'approval_paypal_expiration', true);
        error_log("User token: " . $user_token);
        error_log("Token: " . $token);
        error_log("Expiration: " . $expiration);
        error_log("Time: " . time());
        if (time() > $expiration) {
            set_paypal_email_confermata($user_id, 1);
            delete_user_meta( $user_id, 'approval_paypal_nonce');
            delete_user_meta( $user_id, 'approval_paypal_expiration');
            delete_user_meta( $user_id, 'paypal_email_temporary');
            include(get_stylesheet_directory() . '/inc/email-templates/email-responses-templates/response-link-scaduto.php');
            exit;
        }

        if ($user_token != $token) {
            include(get_stylesheet_directory() . '/inc/email-templates/email-responses-templates/response-token-non-valido.php');
            exit;
        }

        if ($confirm === 'yes') {
            set_paypal_email_confermata($user_id, 1);
            include(get_stylesheet_directory() . '/inc/email-templates/email-responses-templates/response-indirizzo-paypal-confermato.php');
        } else {
            set_paypal_email_confermata($user_id, 0);
            include(get_stylesheet_directory() . '/inc/email-templates/email-responses-templates/response-indirizzo-paypal-rifiutato.php');
        }
        // Invalida il token
        delete_user_meta( $user_id, 'approval_paypal_nonce');
        delete_user_meta( $user_id, 'approval_paypal_expiration');
        $new_paypal_email = get_user_meta($user_id, 'paypal_email_temporary', true);
        update_user_meta($user_id, 'paypal_email', $new_paypal_email);
        delete_user_meta($user_id, 'paypal_email_temporary');
        exit;
    }
});

// Funzione che elimina l'account corrente e tutti i post associati
function delete_user_account() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
    }
    check_ajax_referer('nonce_delete_account', 'nonce');

    // Verifica se l'utente è un amministratore
    if (current_user_can('administrator')) {
        wp_send_json_error(['message' => 'Gli amministratori non possono eliminare il proprio account.']);
    }

    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();
    error_log("User ID: " . $user_id);
    // Imposta tutti i post associati all'utente come draft e aggiorna il metadato stato_prodotto
    $user_posts_query = new WP_Query([
        'author' => $user_id,
        'post_type' => 'product',
        'posts_per_page' => -1
    ]);
    $user_posts = $user_posts_query->get_posts();
    error_log(print_r($user_posts, true));

    foreach ($user_posts as $post) {
        wp_update_post([
            'ID' => $post->ID,
            'post_status' => 'draft',
            'post_author' => 1 
        ]);
        imposta_stato_prodotto($post->ID, 'eliminato_cancellazione_utente');
    }

    do_action('eliminazione_utente', $user_id);

    // // Elimina l'account dell'utente
    // require_once(ABSPATH . 'wp-admin/includes/user.php');
    // wp_delete_user($user_id);//TODO controllare che faccia logout

    wp_send_json_success(['message' => 'Account e post eliminati con successo!']);
}
add_action('wp_ajax_delete_user_account', 'delete_user_account');
add_action('wp_ajax_nopriv_delete_user_account', 'delete_user_account');





