<?php

add_action('rest_api_init', function () {
    register_rest_route('stripe/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => 'handle_stripe_webhook',
        'permission_callback' => '__return_true', // Permette l'accesso pubblico
    ]);
});



/**
 * Callback per gestire il webhook di Stripe
 */
function handle_stripe_webhook(WP_REST_Request $request) {
    // Inserisci qui il tuo Stripe Webhook Secret

    $payment_gateways = WC_Payment_Gateways::instance();
    $gateway = $payment_gateways->payment_gateways()['stripe_redirect']; 

    $webhook_secret = $gateway->get_option('stripe_webhook_secret');

    // Recupera il payload e i suoi header
    $payload = $request->get_body();
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    try {
        // Verifica la firma del webhook
        \Stripe\Webhook::constructEvent(
            $payload, 
            $sig_header, 
            $webhook_secret
        );
    } catch (\UnexpectedValueException $e) {
        // Payload non valido
        return new WP_REST_Response('Invalid payload', 400);
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Firma non verificata
        return new WP_REST_Response('Invalid signature', 400);
    }

    // Decode del payload in array PHP
    $event = json_decode($payload, true);

    if($event['type'] === 'checkout.session.completed') {
        handle_checkout_session_completed($event);
    } 

    // Controlla il tipo di evento e processalo
    if ($event['type'] === 'invoice.payment_succeeded') {
        $invoice = $event['data']['object'];
        
        // Gestisci pagamento riuscito (aggiorna ordine WooCommerce, invia email, ecc.)
        handle_payment_succeeded($invoice);
    } elseif ($event['type'] === 'invoice.payment_failed') {
        $invoice = $event['data']['object'];
        // Gestisci pagamento fallito (invia notifica, aggiorna stato ordine, ecc.)
        handle_payment_failed($invoice);
    } elseif ($event['type'] === 'customer.subscription.created') {
        $subscription = $event['data']['object'];
        // Gestisci creazione sottoscrizione
        handle_subscription_created($subscription);
    } elseif ($event['type'] === 'customer.subscription.updated') {
        $subscription = $event['data']['object'];
        // Gestisci aggiornamento sottoscrizione
         handle_subscription_updated($subscription);
    } elseif ($event['type'] === 'customer.subscription.deleted') {
        $subscription = $event['data']['object'];
        // Gestisci cancellazione sottoscrizione
        handle_subscription_deleted($subscription);
    } elseif ($event['type'] === 'invoice.marked_uncollectible') {
        $invoice = $event['data']['object'];
        // Gestisci fattura non riscuotibile
        handle_marked_uncollectible_subscription($event);
    } 

    // Risposta OK al webhook
    return new WP_REST_Response('Webhook received', 200);
}

/**
 * Gestisce un pagamento riuscito
 */
function handle_payment_succeeded($invoice) {
    // Esempio di gestione: recupera il cliente e aggiorna ordine WooCommerce
    error_log("Stampa Invoice: ");
    error_log(print_r($invoice, true));
    $customer_id = $invoice['customer'];
    $user_query = new WP_User_Query([
        'meta_key' => '_stripe_customer_id',
        'meta_value' => $customer_id,
        'number' => 1,
    ]);

    if (!empty($user_query->get_results())) {
        $user = $user_query->get_results()[0];
        $user_id = $user->ID;
        error_log("Utente WordPress trovato con ID: $user_id");
    } else {
        error_log("Nessun utente WordPress trovato per il cliente Stripe: $customer_id");
    }
    error_log("Pagamento riuscito per il cliente: $customer_id");
    // Recupera l'ID del prodotto Stripe dall'invoice
    $line_items = $invoice['lines']['data'];
    foreach ($line_items as $item) {
        $stripe_product_id = $item['price']['product'];
        
        // Trova il prodotto WooCommerce associato al prodotto Stripe
        $args = [
            'post_type' => 'product',
            'meta_query' => [
                [
                    'key' => '_id_prodotto_stripe',
                    'value' => $stripe_product_id,
                    'compare' => '='
                ]
            ]
        ];
        $query = new WP_Query($args);
        error_log("Stampa Query: ");
        error_log(print_r($query, true));
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();

                // Verifica se si tratta di un rinnovo automatico di una sottoscrizione
                if (isset($invoice['billing_reason']) && $invoice['billing_reason'] === 'subscription_cycle') {
                    // Crea un nuovo ordine WooCommerce
                    $order = wc_create_order();
                    $order->add_product(wc_get_product($product_id), 1); // Aggiungi il prodotto all'ordine
                    $order->set_customer_id($user_id); // Imposta l'ID del cliente
                    $order->calculate_totals();

                    $subscription_id = $invoice['subscription'];
                    error_log("Stampa Subscription ID: ");
                    error_log(print_r($subscription_id, true));
                    $order->update_meta_data('_stripe_subscription_id', $subscription_id);


                    $order->update_status('completed'); // Imposta lo stato dell'ordine su completato
                    $order->add_order_note('Stripe Invoice ID: ' . $invoice['id']);
                    $order->add_order_note('Amount Paid: ' . $invoice['amount_paid']);
                    // $order->add_order_note('Payment Method: ' . $invoice['payment_intent']['payment_method'] . ', Payment Status: ' . $invoice['payment_status'] . ', Payment Date: ' . date('Y-m-d H:i:s', $invoice['created']));
                    // Salva l'ordine
                    


                    ////TODOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO non viene salvato id
                    
                    $order->save();

                    
                    
                    
                    Transaction::insert([
                        'user_id' => $user_id,
                        'amount' => $order->get_total(),
                        'currency' => 'euro',
                        'description' => 'Rinnovo del piano pro',
                        'transaction_type' => 'Abbonamento',
                        'status' => 'completed',
                        'direction' => 'uscita',
                        'order_id' => $order->get_id(),
                        //'sales_balance' => get_user_meta($user_id, 'saldo_utente'),
                        'sales_balance' => ottieni_totale_prelevabile($user_id),
                        'blu_points_balance' => get_user_meta($user_id, 'punti_blu'),
                        'pro_points_balance' => get_user_meta($user_id, 'punti_pro'),
                    ]);

                    error_log("Ordine completato creato per il cliente: $customer_id con prodotto: $product_id");
                } else {
                    error_log("Non è un rinnovo automatico di una sottoscrizione, nessun ordine creato.");
                }
            }
        }
        wp_reset_postdata();
    }
}

/**
 * Gestisce un pagamento fallito
 */
function handle_payment_failed($invoice) {
    $customer_id = $invoice['customer'];
    error_log("Pagamento fallito per il cliente: $customer_id");
}

/**
 * Gestisce una sottoscrizione creata
 */
function handle_subscription_created($subscription) {

    do_action( 'stripe_handle_subscription_created', $subscription );

    /*$subscription_id = $subscription['id'];

    error_log("Voglio modificare la sottoscrizione con ID: $subscription_id");

    error_log("Carico la chiave segreta di Stripe");
    $payment_gateways = WC_Payment_Gateways::instance();
    $gateway = $payment_gateways->payment_gateways()['stripe_redirect']; 
    $stripe_secret_key = $gateway->get_option('stripe_secret_key');
    //$stripe_secret_key = $this->stripe_secret_key;

    error_log("Applico la chiave segreta di Stripe");
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    error_log("Aggiorno la sottoscrizione");
    // Posticipare la scadenza di 14 giorni
    $updated_subscription = \Stripe\Subscription::update(
        $subscription_id, // ID della sottoscrizione
        [
            'trial_end' => strtotime('+14 days'), // Posticipa di 14 giorni
            'proration_behavior' => 'none', // Evita addebiti immediati
        ]
    );
    error_log(print_r($updated_subscription, true));
    error_log('Next Billing Date: ' . date('Y-m-d', $updated_subscription->current_period_end));


    error_log("Sottoscrizione creata con ID: $subscription_id");*/
}

/**
 * Gestisce un evento checkout.session.completed
 */
function handle_checkout_session_completed($event) {

    error_log(print_r($event, true));
    return;

    /*$session = $event['data']['object'];
    $customer_id = $session['customer'];
    $payment_intent = $session['payment_intent'];
    $payment_intent_id = $payment_intent ?? null;

    error_log("Sessione di checkout completata per il cliente: $customer_id", 1, CUSTOM_ERROR_LOG);

    // Recupera l'ordine WooCommerce associato alla sessione di checkout
    $order_id = get_order_id_by_session_id($session['id']);
    $order = wc_get_order($order_id);

    // Aggiorna l'ordine con l'ID del pagamento Stripe
    if ($payment_intent_id) {
        update_post_meta($order_id, '_stripe_payment_intent_id', $payment_intent_id);
    }

    // Completa l'ordine
    $order->payment_complete($payment_intent_id);
    $order->reduce_order_stock();
    $order->save();*/
}

function aggiungi_prova_gratuita($subscription) {

    $subscription_id = $subscription['id'];

    error_log("AGGIUNGI PROVA GRATUITA");
    error_log("SUBSCRIPTION");

    error_log(print_r($subscription, true));

    $payment_gateways = WC_Payment_Gateways::instance();
    $gateway = $payment_gateways->payment_gateways()['stripe_redirect']; 
    $stripe_secret_key = $gateway->get_option('stripe_secret_key');

    \Stripe\Stripe::setApiKey($stripe_secret_key);

    $updated_subscription = \Stripe\Subscription::update(
        $subscription_id,
        [
            'pause_collection' => [
                'behavior' => 'void',
                'resumes_at' => strtotime('+1 month +14 days'),
            ],

        ]
    );

    error_log("Sottoscrizione messa in pausa");
    error_log(print_r($updated_subscription, true));

}

// add_action( 'stripe_handle_subscription_created', 'aggiungi_prova_gratuita' );

//add_action( 'stripe_handle_subscription_created', 'aggiungi_prova_gratuita_2' );


function aggiungi_prova_gratuita_1 ($subscription) {

    $subscription_id = $subscription['id'];


    error_log("AGGIUNGI PROVA GRATUITA");
    error_log("SUBSCRIPTION");

    error_log(print_r($subscription, true));

    $payment_gateways = WC_Payment_Gateways::instance();
    $gateway = $payment_gateways->payment_gateways()['stripe_redirect']; 
    $stripe_secret_key = $gateway->get_option('stripe_secret_key');

    \Stripe\Stripe::setApiKey($stripe_secret_key);

    $items = $subscription['items']['data'];
    error_log("ITEMS");
    error_log(print_r($items, true));

    $items = array_map(function ($item) {
        return [
            'price' => $item['price']['id'],
            'quantity' => $item['quantity'],
        ];
    }, $items);



    $schedule = \Stripe\SubscriptionSchedule::create([
        'from_subscription' => $subscription_id,
        'phases' => [
            // Fase 1: Pagamento immediato

            // Fase 2: Periodo gratuito di 14 giorni
            [
              'items' => $items,
              'trial' => true,
              
              'end_date' => strtotime('+1 month +14 days'),
            ],
            // Fase 3: Sottoscrizione regolare
            [
              'items' => $items,
              'iterations' => null,
              'collection_method' => 'charge_automatically',
            ],
          ],
          //'customer' => $subscription['customer'],
          //'start_date' => strtotime('+1 month'),

        ],
        
    );

    error_log("SCHEDULE");
    error_log(print_r($schedule, true));

    $updated_subscription = \Stripe\Subscription::update(
        $subscription_id, // ID della sottoscrizione
        [
            'cancel_at' => strtotime('+1 month'),
            'proration_behavior' => 'none', // Evita addebiti immediati
            
        ]
    );

    error_log("SOTTOSCRIZIONE AGGIORNATA");

    /*$schedule = \Stripe\SubscriptionSchedule::create([
        'from_subscription' => $subscription_id,
    ]);



    error_log("SCHEDULE");
    error_log(print_r($schedule, true));

    $schedule = \Stripe\SubscriptionSchedule::update(
        $schedule->id, // ID dello schedule appena creato
        [
          'phases' => [

              // Fase 2: Periodo gratuito di 14 giorni
              [
                'start_date' => $current_period_end, // Inizia alla fine del periodo corrente
                'end_date' => strtotime('+1 month +14 days'), // Fine dopo 14 giorni
                'trial' => true, // Periodo di prova
                'items' => $items,
              ],
              // Fase 3: Sottoscrizione regolare
              [
                
                'iterations' => null, // Continua fino alla cancellazione
                'items' => $items,
              ],
          ],
          
        ],
        
      );
      
      error_log("SCHEDULE AGGIORNATO");
        error_log(print_r($schedule, true));
      

*/
   

}

//add_action( 'stripe_handle_subscription_created', 'aggiungi_prova_gratuita_1' );


function handle_subscription_deleted($subscription) {
    $subscription_id = $subscription['id'];
    error_log("Sottoscrizione cancellata con ID: $subscription_id");

    $customer_id = $subscription['customer'];
    $user_query = new WP_User_Query([
        'meta_key' => '_stripe_customer_id',
        'meta_value' => $customer_id,
        'number' => 1,
    ]);

    if (!empty($user_query->get_results())) {
        $user = $user_query->get_results()[0];
        $user_id = $user->ID;
        $subscriptions = get_user_meta($user_id, 'sottoscrizioni', true);
        $subscriptions = (array) $subscriptions;
        foreach ($subscriptions as $key => $subscription_data) {
        
            $subscription_data = (array) $subscription_data;

            if ($subscription_data['id'] == $subscription_id) {

                unset($subscriptions[$key]);
                update_user_meta($user_id, 'sottoscrizioni', $subscriptions);
                break;
            }
        }
        error_log("ID della sottoscrizione cancellato per l'utente con ID: $user_id");
    } else {
        error_log("Nessun utente WordPress trovato per il cliente Stripe: $customer_id");
    }
}

function handle_subscription_updated($subscription) {
    $subscription_id = $subscription['id'];
    if ($subscription['pause_collection']) {
            error_log("La sottoscrizione è stata sospesa.");
            error_log("Sottoscrizione sospesa con ID: $subscription_id");

        $customer_id = $subscription['customer'];
        $user_query = new WP_User_Query([
            'meta_key' => '_stripe_customer_id',
            'meta_value' => $customer_id,
            'number' => 1,
        ]);

        if (!empty($user_query->get_results())) {
            $user = $user_query->get_results()[0];
            $user_id = $user->ID;
            $subscriptions = get_user_meta($user_id, 'sottoscrizioni', true);
            $subscriptions = (array) $subscriptions;
            foreach ($subscriptions as $key => $subscription_data) {
            
                $subscription_data = (array) $subscription_data;

                if ($subscription_data['id'] == $subscription_id) {

                    $subscriptions[$key]['stato'] = 'suspend';
                    update_user_meta($user_id, 'sottoscrizioni', $subscriptions);
                    break;
                }
            }
            error_log("ID della sottoscrizione cancellato per l'utente con ID: $user_id");
        } else {
            error_log("Nessun utente WordPress trovato per il cliente Stripe: $customer_id");
        }
    } else {
        error_log("La sottoscrizione è stata riattivata.");
        error_log("Sottoscrizione riattivata con ID: $subscription_id");

        $customer_id = $subscription['customer'];
        $user_query = new WP_User_Query([
            'meta_key' => '_stripe_customer_id',
            'meta_value' => $customer_id,
            'number' => 1,
        ]);

        if (!empty($user_query->get_results())) {
            $user = $user_query->get_results()[0];
            $user_id = $user->ID;
            $subscriptions = get_user_meta($user_id, 'sottoscrizioni', true);
            $subscriptions = (array) $subscriptions;
            foreach ($subscriptions as $key => $subscription_data) {
            
                $subscription_data = (array) $subscription_data;

                if ($subscription_data['id'] == $subscription_id) {

                    $subscriptions[$key]['stato'] = 'active';
                    update_user_meta($user_id, 'sottoscrizioni', $subscriptions);
                    break;
                }
            }
            error_log("ID della sottoscrizione cancellato per l'utente con ID: $user_id");
        } else {
            error_log("Nessun utente WordPress trovato per il cliente Stripe: $customer_id");
        }
    }


   
    
}

//gestisci la chiamata ajax handle_cancel_subscription
add_action('wp_ajax_handle_cancel_subscription', 'handle_cancel_subscription');
add_action('wp_ajax_nopriv_handle_cancel_subscription', 'handle_cancel_subscription');

function handle_cancel_subscription() {
    // Assicurati che l'utente sia autenticato
    if (!is_user_logged_in()) {
        wp_die('Non autorizzato');
    }
    // Verifica il nonce per la sicurezza
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_cancel_automatic_renew')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    // Ottieni l'ID dell'utente e l'ID della sottoscrizione
    $user_id = get_current_user_id();
    if (!ha_sottoscrizioni_sospese($user_id) && ha_sottoscrizioni_attive($user_id)) {
        wp_send_json_error(['message' => 'Non puoi cancellare la sottoscrizione perché non è mai stata sospesa o non ne è posseduta una attiva.']);
    }
    $subscriptions = get_user_meta($user_id, 'sottoscrizioni', true);
    $subscriptions = (array) $subscriptions;
    error_log("Sottoscrizioni");
    error_log(print_r($subscriptions, true));
    $subscription_id = $subscriptions[0] ?? null;

    if (!$subscription_id) {
        wp_die('Sottoscrizione non trovata.');
    }

    // Passa alla funzione che cancella la sottoscrizione
    cancel_subscription_json($user_id, $subscription_id['id']);
}

function cancel_subscription($user_id, $subscription_id) {
    $stripe = new \Stripe\StripeClient(WC_Gateway_Stripe_Redirect::get_instance()->get_option('stripe_secret_key'));

    try {
        // Recupera e annulla la sottoscrizione
        error_log("Cancella sottoscrizione con ID: $subscription_id");
        $stripe->subscriptions->retrieve($subscription_id)->cancel();

        do_action('cancella_rinnovo_abbonamento', $user_id, $subscription_id);

        return true;
    } catch (Exception $e) {
        return false;
    }
}

function cancel_subscription_json($user_id, $subscription_id) {
    $stripe = new \Stripe\StripeClient(WC_Gateway_Stripe_Redirect::get_instance()->get_option('stripe_secret_key'));

    try {
        // Recupera e annulla la sottoscrizione
        error_log("Cancella sottoscrizione con ID: $subscription_id");
        $stripe->subscriptions->retrieve($subscription_id)->cancel();

        do_action('cancella_rinnovo_abbonamento', $user_id, $subscription_id);

        wp_send_json_success(['message' => 'Sottoscrizione annullata con successo.']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
    
}

//gestisci la chiamata ajax handle_suspend_subscription
add_action('wp_ajax_handle_suspend_subscription', 'handle_suspend_subscription');
add_action('wp_ajax_nopriv_handle_suspend_subscription', 'handle_suspend_subscription');

function handle_suspend_subscription() {
    // Assicurati che l'utente sia autenticato
    if (!is_user_logged_in()) {
        wp_die('Non autorizzato');
    }
    // Verifica il nonce per la sicurezza
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_disable_automatic_renew')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    // Ottieni l'ID dell'utente e l'ID della sottoscrizione
    $user_id = get_current_user_id();
    if (!ha_sottoscrizioni_attive($user_id)) {
        wp_send_json_error(['message' => 'Non puoi sospendere la sottoscrizione perché non ne è posseduta una attiva.']);
    }
    if (ha_sottoscrizioni_sospese($user_id)) {
        wp_send_json_error(['message' => 'Non puoi sospendere la sottoscrizione perché ne è già posseduta una sospesa.']);
    }
    $subscriptions = get_user_meta($user_id, 'sottoscrizioni', true);
    $subscriptions = (array) $subscriptions;
    error_log("Sottoscrizioni");
    error_log(print_r($subscriptions, true));
    $subscription_id = $subscriptions[0]['id']?? null;

    if (!$subscription_id) {
        wp_send_json_error(['message' => 'Sottoscrizione non trovata.']);
    }

    $stripe = new \Stripe\StripeClient(WC_Gateway_Stripe_Redirect::get_instance()->get_option('stripe_secret_key'));

    try{
        // Recupera e annulla la sottoscrizione
        $stripe->subscriptions->update($subscription_id, [
            'pause_collection' => [
            'behavior' => 'mark_uncollectible',
            ],
        ]);
        do_action( 'sospendi_abbonamento', $user_id, $subscription_id );
        wp_send_json_success(['message' => 'Sottoscrizione sospesa con successo.']);
    }
    catch(Exception $e){
        wp_send_json_error(['message' => $e->getMessage()]);
    }
    
}


// Gestisci la chiamata ajax handle_resume_subscription
add_action('wp_ajax_handle_resume_subscription', 'handle_resume_subscription');
add_action('wp_ajax_nopriv_handle_resume_subscription', 'handle_resume_subscription');

function handle_resume_subscription() {
    // Assicurati che l'utente sia autenticato
    if (!is_user_logged_in()) {
        wp_die('Non autorizzato');
    }
    // Verifica il nonce per la sicurezza
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_enable_automatic_renew')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    // Ottieni l'ID dell'utente e l'ID della sottoscrizione
    $user_id = get_current_user_id();
    if (!ha_sottoscrizioni_sospese($user_id) && ha_sottoscrizioni_attive($user_id)) {
        wp_send_json_error(['message' => 'Non puoi riattivare la sottoscrizione perché non ne è posseduta una sospesa o ne si possiede già una attiva']);
    }
    $sottoscrizione = get_sottoscrizione_sospesa($user_id);
    $subscription_id = $sottoscrizione['id'] ?? null;

    error_log("Sottoscrizione sospesa da riattivare: " . print_r($sottoscrizione, true));

    if (!$subscription_id) {
        wp_die('Sottoscrizione non trovata.');
    }

    $stripe = new \Stripe\StripeClient(WC_Gateway_Stripe_Redirect::get_instance()->get_option('stripe_secret_key'));

    try {
        // Recupera e riattiva la sottoscrizione
        $stripe->subscriptions->update($subscription_id, [
            'pause_collection' => '',
        ]);
        do_action( 'resume_abbonamento', $user_id, $subscription_id );
        wp_send_json_success(['message' => 'Sottoscrizione riattivata con successo.']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

add_action('stripe_handle_invoice_marked_uncollectible', 'handle_marked_uncollectible_subscription');

function handle_marked_uncollectible_subscription($event) {
    $invoice = $event['data']['object'];
    $subscription_id = $invoice['subscription'];
    $customer_id = $invoice['customer'];

    $user_query = new WP_User_Query([
        'meta_key' => '_stripe_customer_id',
        'meta_value' => $customer_id,
        'number' => 1,
    ]);

    if (!empty($user_query->get_results())) {
        $user = $user_query->get_results()[0];
        $user_id = $user->ID;

        $stripe = new \Stripe\StripeClient(WC_Gateway_Stripe_Redirect::get_instance()->get_option('stripe_secret_key'));

        try {
            // Cancella la sottoscrizione
            $stripe->subscriptions->cancel($subscription_id);

            // Aggiorna lo stato della sottoscrizione nell'utente
            $subscriptions = get_user_meta($user_id, 'sottoscrizioni', true);
            $subscriptions = (array) $subscriptions;
            foreach ($subscriptions as $key => $subscription_data) {
                $subscription_data = (array) $subscription_data;
                if ($subscription_data['id'] == $subscription_id) {
                    unset($subscriptions[$key]);
                    update_user_meta($user_id, 'sottoscrizioni', $subscriptions);
                    break;
                }
            }

            error_log("Sottoscrizione cancellata per il cliente: $customer_id con ID sottoscrizione: $subscription_id");
        } catch (Exception $e) {
            error_log("Errore durante la cancellazione della sottoscrizione: " . $e->getMessage());
        }
    } else {
        error_log("Nessun utente WordPress trovato per il cliente Stripe: $customer_id");
    }
}