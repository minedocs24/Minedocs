<?php

function handle_contact_form() {
    check_ajax_referer('contatti_nonce', 'nonce');
    
    // Check sul numero di invii
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    $limit = CONTACT_FORM_NUMERO_MASSIMO_CONTATTI; // Numero massimo di invii consentiti
    $time_window = CONTACT_FORM_TEMPO_DI_BLOCCO; 

    $attempts = get_transient("contact_form_attempts_$ip_address");
    error_log("transient" . print_r($attempts, true));
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
    set_transient("contact_form_attempts_$ip_address", $attempts, $time_window);

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);
   
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error(array('message' => 'Compila tutti i campi.'));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        wp_send_json_error(array('message' => 'Inserisci un indirizzo email valido.'));
    }

    // if (strlen($message) < 10) {
    //     wp_send_json_error(array('message' => 'Il messaggio deve contenere almeno 10 caratteri.'));
    // }

    if (strlen($message) > 500) {
        wp_send_json_error(array('message' => 'Il messaggio è troppo lungo.'));
    }

    $admin_email = get_users(array(
        'role' => 'administrator',
        'fields' => array('user_email')
    ));
    $subject = 'Nuovo messaggio dal modulo di contatto';
    $body = "
    <html>
    <head>
        <title>Nuovo messaggio dal modulo di contatto</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                padding: 20px;
            }
            .container {
                background-color: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            h2 {
                color: #0073aa;
            }
            p {
                line-height: 1.6;
            }
            .footer {
                margin-top: 20px;
                font-size: 0.9em;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Nuovo messaggio dal modulo di contatto</h2>
            <p><strong>Nome:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Messaggio:</strong></p>
            <p>$message</p>
            <div class='footer'>
                <p>Questo messaggio è stato inviato dal tuo sito web.</p>
            </div>
        </div>
    </body>
    </html>";
    $headers = ['Content-Type: text/html; charset=UTF-8', "From: noreply@minedocs.it", "Reply-To: $name <$email>"];

    try {
        foreach ($admin_email as $admin) {
            error_log("Invio email a: ".$admin->user_email);
            wp_mail($admin->user_email, $subject, $body, $headers);
        }
        wp_send_json(['success' => true, 'message' => 'Grazie per il tuo messaggio! Ti risponderemo a breve.']);
        wp_die();
    } catch (Exception $e) {
        error_log('Errore durante l\'invio dell\'email: ' . $e->getMessage());
        wp_send_json(['success' => false, 'message' => 'Si è verificato un errore nell\'invio della mail. Riprova più tardi.']);
        wp_die();
    }
    
    wp_die();
}
add_action('wp_ajax_handle_contact_form', 'handle_contact_form');
add_action('wp_ajax_nopriv_handle_contact_form', 'handle_contact_form');

