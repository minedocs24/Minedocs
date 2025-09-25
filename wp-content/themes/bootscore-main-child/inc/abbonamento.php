<?php



// Save custom user profile fields
function save_custom_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    update_user_meta($user_id, 'scadenza_abbonamento', $_POST['scadenza_abbonamento']);
}
add_action('personal_options_update', 'save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'save_custom_user_profile_fields');




// Check if subscription is active
function is_abbonamento_attivo($user_id) {
    // $scadenza_abbonamento = get_the_author_meta('scadenza_abbonamento', $user_id);
    $scadenza_abbonamento = get_user_meta($user_id, 'scadenza_abbonamento', true);
    if (!$scadenza_abbonamento) {
        return false;
    }
    $current_date = date('Y-m-d');
    error_log('user_id: ' . $user_id);  
    error_log('Current Date: ' . $current_date);
    error_log('Scadenza Abbonamento: ' . $scadenza_abbonamento);
    return $current_date < $scadenza_abbonamento;
}

// Modify the custom user profile fields to show subscription status
function add_custom_user_profile_fields($user) {
    ?>
    <h3><?php _e("Abbonamento Pro", "blank"); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="scadenza_abbonamento"><?php _e("Scadenza Abbonamento"); ?></label></th>
            <td>
                <input type="date" name="scadenza_abbonamento" id="scadenza_abbonamento" value="<?php echo esc_attr(get_user_meta($user->ID, 'scadenza_abbonamento', true)); ?>" class="regular-text" /><br />
                
                <div style="margin-top: 10px;">
                <?php if (is_abbonamento_attivo($user->ID)) : ?>
                    <span style="color: white; background-color: green; padding: 2px 5px; border-radius: 3px;">Abbonamento Attivo</span>
                  <?php else : ?>

                    <span style="color: white; background-color: red; padding: 2px 5px; border-radius: 3px;">Abbonamento Non Attivo</span>

                <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'add_custom_user_profile_fields');
add_action('edit_user_profile', 'add_custom_user_profile_fields');

// Extend subscription by a certain number of days
function extend_abbonamento($user_id, $days, $data_log = array()) {
    $scadenza_abbonamento = get_user_meta($user_id, 'scadenza_abbonamento', true);
    $current_date = date('Y-m-d');

    if (!$scadenza_abbonamento || $current_date > $scadenza_abbonamento) {
        $new_scadenza = date('Y-m-d', strtotime("+$days days"));
    } else {
        $new_scadenza = date('Y-m-d', strtotime("$scadenza_abbonamento +$days days"));
    }

    $data_log = [
        'currency' => 'euro',
        'transaction_type' => 'Rinnovo Abbonamento',
        'status' => 'completed',
        'direction' => 'uscita',
    ];

    update_user_meta($user_id, 'scadenza_abbonamento', $new_scadenza);
    do_action('abbonamento_esteso', $user_id, $new_scadenza, $data_log);
}

// Mostra il metabox nella pagina di modifica dell'utente per il periodo di prova disponibile
function aggiungi_metabox_periodo_prova_disponibile($user) {
    // Ottieni il valore del periodo di prova disponibile dell'utente
    $periodo_prova_disponibile = get_user_meta($user->ID, 'periodo_prova_disponibile', true);
    ?>
    <h3><?php _e('Periodo di Prova Disponibile', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="periodo_prova_disponibile"><?php _e('Periodo di Prova Disponibile', 'textdomain'); ?></label></th>
            <td>
                <input type="checkbox" name="periodo_prova_disponibile" id="periodo_prova_disponibile" value="1" <?php checked($periodo_prova_disponibile, '1'); ?> />
                <br/>
                <span class="description"><?php _e('Seleziona se il periodo di prova è disponibile per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_periodo_prova_disponibile');
add_action('edit_user_profile', 'aggiungi_metabox_periodo_prova_disponibile');

// Salva il periodo di prova disponibile quando il profilo utente viene aggiornato
function salva_periodo_prova_disponibile_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva il periodo di prova disponibile
    $periodo_prova_disponibile = isset($_POST['periodo_prova_disponibile']) ? '1' : '0';
    update_user_meta($user_id, 'periodo_prova_disponibile', $periodo_prova_disponibile);
}
add_action('personal_options_update', 'salva_periodo_prova_disponibile_utente');
add_action('edit_user_profile_update', 'salva_periodo_prova_disponibile_utente');

/*

// Configura la chiave segreta di Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Dati necessari
$customerId = 'id_cliente';  // ID cliente già creato
$priceId = 'id_piano_prezzo'; // ID del prezzo/abbonamento creato

try {
    // Crea l'abbonamento
    $subscription = \Stripe\Subscription::create([
        'customer' => $customerId,
        'items' => [['price' => $priceId]],
        'payment_behavior' => 'default_incomplete', // Per confermare il pagamento
        'expand' => ['latest_invoice.payment_intent'], // Espandi dettagli pagamento
    ]);

    // ID dell'abbonamento
    echo "Abbonamento creato con successo: " . $subscription->id;
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Gestione errori
    echo "Errore: " . $e->getMessage();
}


*/

// Creazione di un abbonamento con Stripe
/*

add_action('woocommerce_payment_complete', function($order_id) {

    error_log('Entro nella funzione di assegnazione abbonamento');
    $order = wc_get_order($order_id);
    $products = $order->get_items();
    $product_stripe_id = 'prod_RM5fQKHse6aiIb'; // ID del prezzo/abbonamento creato
    // $customerId = 'cus_RM4kRIthKOBDSB';  // ID cliente già creato
    $customerId = get_post_meta($order_id, 'wp__stripe_customer_id', true);

    foreach ($products as $product) {
        $product_id = $product->get_product_id();
        //if (get_post_meta($product_id, '_is_recurring', true) === 'yes') {


            // Configura Stripe
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
            error_log('Ciao 1');

            // Crea il cliente su Stripe
            /*
            $customer = \Stripe\Customer::create([
                'email' => $order->get_billing_email(),
                'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'payment_method' => $order->get_payment_method(), // Usa il metodo di pagamento di WooCommerce
            ]);
            */
            // Ottieni i dettagli dell'abbonamento
//            $interval_unit = get_post_meta($product_id, '_recurring_interval_unit', true);
 //           $interval_count = get_post_meta($product_id, '_recurring_interval', true);
 //           $price = $product->get_total(); // Prezzo del prodotto
//            error_log(get_post_meta($order_id, '_stripe_customer_id', true));
//            error_log('Interval Unit: ' . $interval_unit);
//            error_log('Interval Count: ' . $interval_count);
//            error_log('Price: ' . $price);

            // Crea il piano di abbonamento
            /*
            $price_id = \Stripe\Price::create([
                'unit_amount' => $price * 100, // Converti in centesimi
                'currency' => 'eur',
                'recurring' => [
                    'interval' => $interval_unit,
                    'interval_count' => $interval_count,
                ],
                'product' => \Stripe\Product::create(['name' => $product->get_name()])->id,
            ])->id;
            */

            // Crea l’abbonamento
            /*\Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $price_id]],
                'expand' => ['latest_invoice.payment_intent'],
            ]);*/
/*
            try {
                // Crea l'abbonamento
                $subscription = \Stripe\Subscription::create([
                    'customer' => $customerId,
                    'items' => [['price_data' => [
                        'product' => $product_stripe_id,
                        'unit_amount' => $price * 100, // Converti in centesimi
                        'currency' => 'eur',
                        'recurring' => [
                            'interval' => $interval_unit,
                            'interval_count' => $interval_count,
                        ],
                    ]]],
                    'payment_behavior' => 'default_incomplete', // Per confermare il pagamento
                    'expand' => ['latest_invoice.payment_intent'], // Espandi dettagli pagamento
                ]);

                // ID dell'abbonamento
                error_log("Abbonamento creato con successo: " . $subscription->id);
                echo "Abbonamento creato con successo: " . $subscription->id;
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Gestione errori
                error_log("Errore: " . $e->getMessage());
                echo "Errore: " . $e->getMessage();
            }
            
            /*
            \Stripe\Subscription::create([
                'customer' => get_post_meta($order_id, '_stripe_customer_id', true),
                'items' => [['price' => $priceId]],
                'expand' => ['latest_invoice.payment_intent'],
            ]);*/

            // Salva l'ID cliente Stripe nell'ordine
            //update_post_meta($order_id, '_stripe_customer_id', $customer->id);
        //}
/*    }
}, 100);







/*



class WC_Gateway_Stripe_Custom extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'stripe_custom';
        $this->method_title = __('Stripe Payment', 'textdomain');
        $this->method_description = __('Paga con Stripe.', 'textdomain');
        $this->has_fields = true;

        // Imposta i campi del gateway
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Abilitato', 'textdomain'),
                'type' => 'checkbox',
                'label' => __('Abilita Stripe', 'textdomain'),
                'default' => 'yes',
            ],
            'title' => [
                'title' => __('Titolo', 'textdomain'),
                'type' => 'text',
                'default' => __('Carta di Credito (Stripe)', 'textdomain'),
            ],
            'description' => [
                'title' => __('Descrizione', 'textdomain'),
                'type' => 'textarea',
                'default' => __('Paga in modo sicuro con Stripe.', 'textdomain'),
            ],
        ];
    }

    public function payment_fields() {
        
        echo '<div id="stripe-card-element"></div>';
        echo '<small>' . __('Inserisci i dettagli della tua carta per completare il pagamento.', 'textdomain') . '</small>';
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);


        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        try {
            // Crea il PaymentIntent
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $order->get_total() * 100, // Importo in centesimi
                'currency' => 'eur',
                'payment_method' => $_POST['payment_method_id'], // ID del metodo di pagamento dal frontend
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            if ($intent->status === 'succeeded') {
                // Completa l'ordine in WooCommerce
                $order->payment_complete($intent->id);
                $order->add_order_note(__('Pagamento completato con Stripe.', 'textdomain'));

                return [
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                ];
            } else {
                wc_add_notice(__('Il pagamento non è stato completato. Riprova.', 'textdomain'), 'error');
                return;
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            wc_add_notice($e->getMessage(), 'error');
            return;
        }
    }
}

add_filter('woocommerce_payment_gateways', function($gateways) {
    $gateways[] = 'WC_Gateway_Stripe_Custom';
    return $gateways;
});

*/

// add_action('woocommerce_checkout_order_processed', 'create_stripe_subscription', 10, 1);
// function create_stripe_subscription($order_id) {
//     $order = wc_get_order($order_id);

//     foreach ($order->get_items() as $item) {
//         $product_id = $item->get_product_id();
//         $stripe_plan_id = "prod_RM5fQKHse6aiIb";//get_post_meta($product_id, '_stripe_plan_id', true);

//         if ($stripe_plan_id) {
//             // Include Stripe SDK
//             // require_once 'path/to/stripe-php/init.php';

//             \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

//             // Crea un cliente su Stripe
//             $customer = \Stripe\Customer::create([
//                 'email' => $order->get_billing_email(),
//                 'name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
//                 'payment_method' => $order->get_meta('_stripe_payment_method_id'),
//                 'invoice_settings' => [
//                     'default_payment_method' => $order->get_meta('_stripe_payment_method_id'),
//                 ],
//             ]);

//             // Crea l'abbonamento
//             $subscription = \Stripe\Subscription::create([
//                 'customer' => $customer->id,
//                 'items' => /* [['price' => $stripe_plan_id]], */[['price_data' => [
//                     'product' => $product_stripe_id,
//                     'unit_amount' => $price * 100, // Converti in centesimi
//                     'currency' => 'eur',
//                     'recurring' => [
//                         'interval' => $interval_unit,
//                         'interval_count' => $interval_count,
//                     ],
//                 ]]],
//                 'expand' => ['latest_invoice.payment_intent'],
//             ]);

//             // Salva l'ID abbonamento Stripe nell'ordine
//             $order->update_meta_data('_stripe_subscription_id', $subscription->id);
//             $order->save();
//         }
//     }
// }
