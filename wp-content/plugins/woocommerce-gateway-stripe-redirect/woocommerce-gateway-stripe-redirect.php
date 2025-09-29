<?php
/*
Plugin Name: WooCommerce Stripe Gateway Redirect
Description: A WooCommerce payment gateway that redirects users to Stripe Checkout.
Version: 1.0
Author: Il Tuo Nome
*/

if (!defined('ABSPATH')) {
    exit; // Blocca accesso diretto
}


include_once 'endpoints.php';



// Assicurati che WooCommerce sia attivo
add_action('plugins_loaded', 'wc_stripe_redirect_init', 11);

function wc_stripe_redirect_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Gateway_Stripe_Redirect extends WC_Payment_Gateway
    {
        //private $stripe_secret_key;
        //private $stripe_publishable_key;

        public function __construct()
        {
            $this->id = 'stripe_redirect';
            $this->has_fields = false;
            $this->method_title = __('Stripe Redirect', 'woocommerce');
            $this->method_description = __('Redirect to Stripe for payment.', 'woocommerce');

            // Carica le impostazioni
            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            //$this->stripe_secret_key = $this->get_option('stripe_secret_key');
            //$this->stripe_publishable_key = $this->get_option('stripe_publishable_key');

            // Salva le impostazioni
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public static function get_instance()
        {
            return new self();
        }   

        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable Stripe Redirect', 'woocommerce'),
                    'default' => 'yes',
                ],
                'title' => [
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title the user sees during checkout.', 'woocommerce'),
                    'default' => __('Stripe', 'woocommerce'),
                    'desc_tip' => true,
                ],
                'description' => [
                    'title' => __('Description', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('This controls the description the user sees during checkout.', 'woocommerce'),
                    'default' => __('Pay securely using Stripe.', 'woocommerce'),
                ],
                'stripe_secret_key' => [
                    'title' => __('Stripe Secret Key', 'woocommerce'),
                    'type' => 'password',
                ],
                'stripe_publishable_key' => [
                    'title' => __('Stripe Publishable Key', 'woocommerce'),
                    'type' => 'text',
                ],
                'stripe_webhook_secret' => [
                    'title' => __('Stripe Webhook Secret', 'woocommmerce'),
                    'type' => 'text'
                ],
                'stripe_tax_rate_id' => [
                    'title' => __('Stripe Tax Rate ID', 'woocommerce'),
                    'type' => 'text',
                ],
                'stripe_payment_methods' => [
                    'title' => __('Payment Methods', 'woocommerce'),
                    'type' => 'multiselect',
                    'options' => [
                        'card' => __('Credit Card', 'woocommerce'),
                        'ideal' => __('iDEAL', 'woocommerce'),
                        'bancontact' => __('Bancontact', 'woocommerce'),
                        'sofort' => __('SOFORT', 'woocommerce'),
                        'giropay' => __('Giropay', 'woocommerce'),
                        'eps' => __('EPS', 'woocommerce'),
                        'p24' => __('Przelewy24', 'woocommerce'),
                        'sepa_debit' => __('SEPA Direct Debit', 'woocommerce'),
                        'au_becs_debit' => __('BECS Direct Debit', 'woocommerce'),
                        'bacs' => __('Bank Transfer', 'woocommerce'),
                        'au_becs_debit' => __('BECS Direct Debit', 'woocommerce'),
                        'afterpay_clearpay' => __('Afterpay Clearpay', 'woocommerce'),
                        'klarna' => __('Klarna', 'woocommerce'),
                        'paypal' => __('PayPal', 'woocommerce'),
                        'revolut_pay' => __('Revolut Pay', 'woocommerce'),
                    ],
                    'default' => ['card'],
                    'desc_tip' => true,
                ],
            ];
        }

        public function is_available()
        {
            return true;
            return parent::is_available() && !empty($this->stripe_secret_key) && !empty($this->stripe_publishable_key);
        }
/*
        public function payment_fields()
        {
            // Mostra i campi del modulo personalizzati
            echo '<div>';
            echo '<p>' . esc_html($this->description) . '</p>';
            echo '<label for="stripe-email">' . __('Email Address', 'woocommerce') . '</label>';
            echo '<input type="email" id="stripe-email" name="stripe_email" class="input-text" placeholder="your@email.com" required />';
            echo '</div>';
        }

        public function validate_fields()
        {
            if (empty($_POST['stripe_email']) || !is_email($_POST['stripe_email'])) {
                wc_add_notice(__('Please enter a valid email address.', 'woocommerce'), 'error');
                return false;
            }
            return true;
        }
*/

        /*
        Funzione che ottiene il coupon da un codice promozionale
        */
        public function getCouponFromPromoCode($code) {
            try {
                $stripe = new \Stripe\StripeClient(WC_Gateway_Stripe_Redirect::get_instance()->get_option('stripe_secret_key'));
                // Expand the coupon object so we have full coupon details on the returned promotion code
                $promotionCodes = $stripe->promotionCodes->all(['limit' => 1000]);
                error_log('Promotion codes: ' . print_r($promotionCodes, true));
                foreach($promotionCodes as $promotionCode) {
                    error_log('Promotion code: ' . $promotionCode->coupon->id);
                    if($promotionCode->code == $code) {
                        return $promotionCode->coupon->id;
                    }
                }

                // if (!empty($promotionCodes->data) && isset($promotionCodes->data[0])) {
                //     return $promotionCodes->data[0]->coupon->id;
                // }
            } catch (\Exception $e) {
                error_log('Errore getCouponFromPromoCode: ' . $e->getMessage());
            }

            return null;
        }

        /*
        Funzione che personalizza il codice promozionale per il prodotto
        */
        public function customizeStripeCode($code, $product) {

            if ($code == 'PROMOLANCIO25'){
                if ($product && $product->get_sku() == SKU_ABBONAMENTO_30_GIORNI){
                    return 'PROMOLANCIO2530';
                } elseif ($product && $product->get_sku() == SKU_ABBONAMENTO_90_GIORNI){
                    return 'PROMOLANCIO2590';
                } elseif ($product && $product->get_sku() == SKU_ABBONAMENTO_365_GIORNI){
                    return 'PROMOLANCIO25365';
                } else {
                    return $code;
                }

            } else {
                return $code;
            }

        }

        public function process_payment($order_id)
        {
            error_log('Process payment');
            $order = wc_get_order($order_id);

            $coupon_code = $order->get_coupon_codes();
            $stripe_coupon_id = null;
            foreach($coupon_code as $code) {
                #ricerca il codice promozionale su stripe
                
                $items = $order->get_items();
                $first_item = reset($items);
                $product_id = $first_item->get_product_id();
                $product = wc_get_product($product_id);

                $customized_coupon_code = $this->customizeStripeCode($code, $product);
                //error_log('Customized coupon code: ' . $customized_coupon_code);
                $coupon = $this->getCouponFromPromoCode($customized_coupon_code);
                //error_log('Coupon: ' . $coupon);
                if($coupon) {
                    $stripe_coupon_id = $coupon;
                    break;
                }
                
            }

            // Stripe API configuration
            $payment_gateways = WC_Payment_Gateways::instance();
            $gateway = $payment_gateways->payment_gateways()['stripe_redirect']; 
            $stripe_secret_key = $gateway->get_option('stripe_secret_key');
            //$stripe_secret_key = $this->stripe_secret_key;

            \Stripe\Stripe::setApiKey($stripe_secret_key);

            $customer_id = get_user_meta(get_current_user_id(  ), '_stripe_customer_id', true);

            if(isset($customer_id) && $customer_id != '') {
                $customer = \Stripe\Customer::retrieve($customer_id);
            } else {
                $customer = \Stripe\Customer::create([
                    'email' => $order->get_billing_email(),
                    'address' => [
                        'line1' => $order->get_billing_address_1(),
                        'line2' => $order->get_billing_address_2(),
                        'city' => $order->get_billing_city(),
                        'state' => $order->get_billing_state(),
                        'postal_code' => $order->get_billing_postcode(),
                        'country' => $order->get_billing_country(),
                    ],
                    'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                ]);
                update_user_meta( get_current_user_id(  ), '_stripe_customer_id', $customer->id);
            }

            // Ottieni il primo prodotto dell'ordine
            $items = $order->get_items();            
            $first_item = reset($items);  
            // error_log('First item: ' . print_r($first_item, true));          
            $product_id = $first_item->get_product_id();
            
            get_post_meta($product_id, '_is_recurring', true) == 'yes' ? $mode = 'subscription' : $mode = 'payment';

            if($mode == 'subscription') {

                $id_prodotto_stripe = get_post_meta($product_id, '_id_prodotto_stripe', true);
                if(!$id_prodotto_stripe) {
                    return [
                        'result' => 'error',
                        'message' => 'ID prodotto Stripe non impostato',
                    ];
                }

                
                $product_stripe = \Stripe\Product::retrieve($id_prodotto_stripe); // ID del prodotto esistente
                $default_price_id = $product_stripe->default_price;

                $price_object = \Stripe\Price::retrieve($default_price_id);
                error_log('Price object: ' . print_r($price_object, true));

                // Check if the price on Stripe matches the WooCommerce product price
                //$product_price = $order->get_total() * 100; // Convert to cents
                $product_price = get_post_meta($product_id, '_price', true) * 100;
                if ($price_object->unit_amount != $product_price) {
                    // Create a new price on Stripe
                    $new_price = \Stripe\Price::create([
                        'product' => $id_prodotto_stripe,
                        'unit_amount' => $product_price,
                        'currency' => 'eur',
                        'recurring' => [
                            'interval' => 'month',
                            'interval_count' => 1,
                        ],
                    ]);
                    $default_price_id = $new_price->id;
                }

                $subscription_data =[];
                $periodo_prova_disponibile = get_user_meta(get_current_user_id(  ), 'periodo_prova_disponibile', true);
                error_log('Periodo prova disponibile: ' . $periodo_prova_disponibile);

                $tax_total = $order->get_total_tax(); // Tasse totali in formato decimale (es. 2.20)
                $tax_total_cents = intval(round($tax_total * 100)); // In centesimi per Stripe

                $tipo_prova = get_tipologia_prova_gratuita();
                switch($tipo_prova) {
                    case 'estensione_automatica':
                       // $subscription_data['trial_period_days'] = get_durata_periodo_prova();
                        break;
                    case 'sconto_primo_abbonamento':
                        $coupon_code = 'CODICE_PROMOZIONALE_PROVA_' . get_durata_periodo_prova() . '_GIORNI';
                        crea_codice_promozionale($coupon_code);
                        //$subscription_data['coupon'] = $coupon_code;
                        break;
                    case 'nessun_pagamento':
                        //$subscription_data['trial_period_days'] = get_durata_periodo_prova();
                        break;
                }

                if($periodo_prova_disponibile){
                    //$subscription_data['trial_period_days'] = get_durata_periodo_prova();
                }

                $session = \Stripe\Checkout\Session::create([
                    'customer' => $customer->id,
                    'payment_method_types' => $this->get_option('stripe_payment_methods'),
                    // 'line_items' => $line_items,
                    'line_items' => [
                        [
                            'price' => $default_price_id,
                            'quantity' => 1,
                            //'tax_rates' => [$this->get_option('stripe_tax_rate_id')],
                        ],
                    ],

                    'discounts' => [
                        [
                            'coupon' => $stripe_coupon_id
                        ],
                    ],

                    'subscription_data' => $subscription_data,
                    'mode' => 'subscription',
                    'success_url' => $this->get_return_url($order),
                    'cancel_url' => $order->get_cancel_order_url(),
                ]);

                $session_id = $session->id;

                // Aggiungi il riferimento della sessione all'ordine
                update_post_meta($order_id, '_stripe_session_id', $session_id);

                return [
                    'result' => 'success',
                    'redirect' => $session->url,
                ];
            } else {
                // Creazione della sessione di pagamento
                $line_items = [];
                foreach ($order->get_items() as $item) {
                    $product = $item->get_product();
                    $line_items[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $product->get_name(),
                            ],
                            'unit_amount' => $product->get_price() * 100, // Converti in centesimi
                        ],
                        'quantity' => $item->get_quantity(),
                    ];
                }

                $session = \Stripe\Checkout\Session::create([
                    'customer' => $customer->id,
                    'payment_method_types' => $this->get_option('stripe_payment_methods'),
                    //'customer_email' => $order->get_billing_email(),
                    /*'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => [
                                    'name' => 'Order ' . $order->get_order_number(),
                                ],
                                'unit_amount' => $order->get_total() * 100, // Converti in centesimi
                            ],
                            'quantity' => 1,
                        ],
                    ],*/
                    'line_items' => $line_items,
                    'mode' => 'payment',
                    'success_url' => $this->get_return_url($order),
                    'cancel_url' => $order->get_cancel_order_url(),
                ]);

                $session_id = $session->id;

                // Aggiungi il riferimento della sessione all'ordine
                update_post_meta($order_id, '_stripe_session_id', $session_id);
                

                // Reindirizza a Stripe Checkout
                return [
                    'result' => 'success',
                    'redirect' => $session->url,
                ];
            }
        }
    }

    // Aggiungi il gateway alla lista dei gateway di pagamento
    add_filter('woocommerce_payment_gateways', 'add_wc_gateway_stripe_redirect');

    function add_wc_gateway_stripe_redirect($methods)
    {
        $methods[] = 'WC_Gateway_Stripe_Redirect';
        return $methods;
    }


    //add_action('woocommerce_payment_complete', 'wc_stripe_redirect_payment_complete');
    //add_action('woocommerce_api_wc_gateway_stripe_redirect', 'wc_stripe_redirect_payment_complete_handler');
    add_action('woocommerce_thankyou', 'wc_stripe_redirect_after_returning_from_stripe');

    function wc_stripe_redirect_after_returning_from_stripe($order_id)
    {
        $order = wc_get_order($order_id);
        $session_id = get_post_meta($order_id, '_stripe_session_id', true);

        if ($session_id) {
            $order->add_order_note(
                sprintf(__('Payment processed with Stripe Checkout. Session ID: %s', 'woocommerce'), $session_id)
            );

            $stripe = new \Stripe\StripeClient(WC_Gateway_Stripe_Redirect::get_instance()->get_option('stripe_secret_key'));

            $session = $stripe->checkout->sessions->retrieve(
                $session_id,
                []
              );

            //   error_log('Session: ' . print_r($session, true));

            if ($session->payment_status === 'paid') {

                if (isset($session->subscription)) {
                    $subscription = $stripe->subscriptions->retrieve($session->subscription);
                    $current_user_id = get_current_user_id();
                    $existing_subscriptions = get_user_meta($current_user_id, 'sottoscrizioni', true);

                    if (!is_array($existing_subscriptions)) {
                        $existing_subscriptions = [];
                    }

                    $existing_subscriptions[] = array(
                        'id' => $subscription->id,
                        'stato' => $subscription->status,
                        'descrizione' => $subscription->items->data[0]->price->product->name,
                    );
                    update_user_meta($current_user_id, 'sottoscrizioni', $existing_subscriptions);
                    // error_log('Subscription: ' . print_r($subscription, true));
                    $trial_end = $subscription->trial_end;
                    // error_log('Trial end in checkout: ' . $trial_end);

                    $order->update_meta_data('_stripe_subscription_id', $subscription->id);

                    if ($trial_end) {
                        // $trial_days = ($trial_end - time()) / (60 * 60 * 24);
                        $trial_days = get_durata_periodo_prova();
                        $order->add_order_note(
                            sprintf(__('Durata periodo di prova: %d giorni', 'woocommerce'), $trial_days)
                        );
                        //update_post_meta($order_id, '_stripe_trial_end', $trial_end);
                        $order->update_meta_data('_stripe_trial_end', $trial_end);
                        // Applica un codice promozionale per scontare l'abbonamento
                        // $coupon_code = 'PROMO100'; // Codice promozionale
                        // $coupon = new WC_Coupon($coupon_code);
                        // if ($coupon->is_valid()) {
                        //     $order->apply_coupon($coupon_code);
                        //     $order->add_order_note(
                        //         sprintf(__('Sconto per periodo di prova applicato. Codice promozionale applicato: %s', 'woocommerce'), $coupon_code)
                        //     );
                        // } else {
                        //     $order->add_order_note(
                        //         sprintf(__('Codice promozionale non valido: %s', 'woocommerce'), $coupon_code)
                        //     );
                        // }
                      //  $order->apply_coupon('CODICE_PROMOZIONALE_PROVA_' . $trial_days . '_GIORNI');
                    }
                }

                $order->payment_complete();
                $order->add_order_note(
                    sprintf(__('Pagamento completato. Session ID: %s, Payment Intent ID: %s', 'woocommerce'), $session_id, $session->payment_intent)
                );
                
            } else {
                $order->update_status('failed', __('Pagamento fallito.', 'woocommerce'));
            }
/*
            $payment_intent = $stripe->paymentIntents->retrieve(
                $session_id,
                ['expand' => ['payment_method', 'customer','line_items']]
            );

            $order->add_order_note(
                sprintf(__('Payment status: %s', 'woocommerce'), $payment_intent->status)
            );

            error_log('Payment intent: ' . print_r($payment_intent, true)); */

        }
    }

    




/*
    add_action('rest_api_init', function () {
        register_rest_route('stripe/v1', '/webhook', [
            'methods' => 'POST',
            'callback' => 'handle_stripe_webhook',
            'permission_callback' => '__return_true',
        ]);
    });
    
    function handle_stripe_webhook(WP_REST_Request $request)
    {
        $payload = $request->get_body();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $secret = 'tuo_webhook_secret_key'; // Inserisci la chiave segreta del webhook (disponibile nel dashboard di Stripe)
    
        // Carica la libreria Stripe
        require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $secret
            );
    
            // Gestisci eventi specifici
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
    
                    // ID cliente Stripe
                    $customer_id = $session->customer;
    
                    // Registra nel log
                    error_log('Customer ID: ' . $customer_id);
    
                    // Puoi anche salvare l'ID cliente in WooCommerce o nel database
                    // $order_id = $session->client_reference_id; // Se hai passato un riferimento all'ordine
                    // update_post_meta($order_id, '_stripe_customer_id', $customer_id);
    
                    break;
    
                default:
                    error_log('Unhandled event type: ' . $event->type);
            }
    
            http_response_code(200); // Risposta OK per Stripe
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            error_log('Webhook error: ' . $e->getMessage());
            http_response_code(400); // Risposta di errore
            return;
        }
    }
    */

}

//add_action('woocommerce_before_cart', 'mostra_messaggio_sconto_carrello');

function mostra_messaggio_sconto_carrello() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $periodo_prova_disponibile = get_user_meta($user_id, 'periodo_prova_disponibile', true);
    if (!$periodo_prova_disponibile) {
        return;
    }

    $codice_promozionale = 'CODICE_PROMOZIONALE_PROVA_' . get_durata_periodo_prova() . '_GIORNI';
    crea_codice_promozionale($codice_promozionale);

    if (!WC()->cart->has_discount($codice_promozionale)) {
        WC()->cart->add_discount($codice_promozionale);
        wc_print_notice('Hai un periodo di prova attivo. Il codice promozionale "' . $codice_promozionale . '" è stato applicato al tuo ordine.', 'success');
    }
}

//add_action('woocommerce_before_calculate_totals', 'applica_codice_promozionale_periodo_prova', 10, 1);

function applica_codice_promozionale_periodo_prova($cart) {
    // if (is_admin() && !defined('DOING_AJAX')) {
    //     return;
    // }

    // Ottieni l'ID dell'utente loggato
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    // Verifica se l'utente ha un periodo di prova disponibile
    $periodo_prova_disponibile = get_user_meta($user_id, 'periodo_prova_disponibile', true);
    if (!$periodo_prova_disponibile) {
        return;
    }

    // Codice promozionale da applicare
    $codice_promozionale = 'CODICE_PROMOZIONALE_PROVA_' . get_durata_periodo_prova() . '_GIORNI';
    

    // Verifica se il codice promozionale è già applicato
    // if (!WC()->cart->has_discount($codice_promozionale)) {
    //     WC()->cart->add_discount($codice_promozionale);
    // }
}

function crea_codice_promozionale($codice_promozionale) {
    $coupon = new WC_Coupon();
    $coupon->set_code($codice_promozionale);
    $coupon->set_discount_type('percent');
    $coupon->set_amount(100);
    $coupon->set_individual_use(true);
    $coupon->set_usage_limit(1);
    $coupon->set_description('Codice promozionale per periodo di prova gratuito');
    // $coupon->set_free_shipping(false);
    // $coupon->set_expiry_date(date('Y-m-d', strtotime('+1 month')));
    $coupon->save();
}

// Carica la libreria Stripe
/*add_action('wp_enqueue_scripts', function () {
    if (!class_exists('Stripe\\Stripe')) {
        require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php'; // Assicurati che Stripe sia installato
    }
});*/


//add_action('woocommerce_before_cart', 'applica_codice_promozionale_automaticamente');
//add_action('woocommerce_before_checkout_form', 'applica_codice_promozionale_automaticamente');

function applica_codice_promozionale_automaticamente() {
    $codice_promozionale = 'PRO14GRATIS';

    $periodo_prova_disponibile = get_user_meta(get_current_user_id(  ), 'periodo_prova_disponibile', true);
    //wc_print_notice( 'Periodo prova disponibile: ' . $periodo_prova_disponibile, 'notice' );
    if (!$periodo_prova_disponibile) {
        return;
    }
    

    if (!WC()->cart->has_discount($codice_promozionale)) {
        WC()->cart->add_discount($codice_promozionale);
        wc_print_notice('Il codice promozionale "' . $codice_promozionale . '" è stato applicato automaticamente al tuo ordine.', 'success');
    }
}

// Forza i metodi di pagamento anche con carrello a 0€ per un codice specifico
function enable_payment_methods_for_zero_total_( $available_gateways ) {
    $coupon_code = 'PRO14GRATIS';

    // Controlla se il carrello ha il coupon applicato
    if ( WC()->cart->has_discount( $coupon_code ) && WC()->cart->get_total( 'edit' ) == 0 ) {
        // Rimuove il filtro che disabilita i metodi di pagamento
        add_filter( 'woocommerce_cart_needs_payment', '__return_true', 100 );
    }

    return $available_gateways;
}
//add_filter( 'woocommerce_available_payment_gateways', 'enable_payment_methods_for_zero_total' );

// Forza i metodi di pagamento anche con carrello a 0€ per un codice specifico
function enable_payment_methods_for_zero_total( $needs_payment, $cart ) {
    $coupon_code = 'PRO14GRATIS';

    // Controlla se il coupon è applicato e il totale del carrello è 0
    if ( WC()->cart->has_discount( $coupon_code ) && WC()->cart->get_total( 'edit' ) == 0 ) {
        return true; // Forza la necessità di pagamento
    }

    return $needs_payment;
}
add_filter( 'woocommerce_cart_needs_payment', 'enable_payment_methods_for_zero_total', 10, 2 );

add_action('show_user_profile', 'show_stripe_customer_id_field');
add_action('edit_user_profile', 'show_stripe_customer_id_field');

function show_stripe_customer_id_field($user)
{
    ?>
    <h3><?php _e('Stripe Customer ID', 'woocommerce'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="stripe_customer_id"><?php _e('Stripe Customer ID', 'woocommerce'); ?></label></th>
            <td>
                <input type="text" name="stripe_customer_id" id="stripe_customer_id" value="<?php echo esc_attr(get_user_meta($user->ID, '_stripe_customer_id', true)); ?>" class="regular-text" /><br />
                <span class="description"><?php _e('Enter the Stripe Customer ID for this user.', 'woocommerce'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'save_stripe_customer_id_field');
add_action('edit_user_profile_update', 'save_stripe_customer_id_field');

function save_stripe_customer_id_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, '_stripe_customer_id', sanitize_text_field($_POST['stripe_customer_id']));
}


