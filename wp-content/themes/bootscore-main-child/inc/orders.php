<?php

// Add custom fields to WooCommerce order
add_action('woocommerce_admin_order_data_after_billing_address', 'add_custom_order_fields');
function add_custom_order_fields($order){
    echo '<div class="form-field form-field-wide">';
    
    // ID Venditore
    woocommerce_wp_text_input(array(
        'id' => 'id_venditore',
        'label' => __('ID Venditore', 'woocommerce'),
        //'value' => get_post_meta($order->get_id(), '_id_venditore', true),
        'value' => $order->get_meta('_id_venditore'),
    ));
    // Link al profilo del venditore
    //$venditore_id = get_post_meta($order->get_id(), '_id_venditore', true);
    $venditore_id = $order->get_meta('_id_venditore');
    if ($venditore_id) {
        $venditore_profile_url = get_author_posts_url($venditore_id);
        echo '<p class="form-field">
                <label>' . __('Profilo Venditore', 'woocommerce') . '</label>
                <a href="' . esc_url($venditore_profile_url) . '" target="_blank">' . __('Visualizza Profilo', 'woocommerce') . '</a>
              </p>';
    }

    // Guadagno Lordo Venditore
    woocommerce_wp_text_input(array(
        'id' => 'guadagno_lordo_venditore',
        'label' => __('Guadagno Lordo Venditore', 'woocommerce'),
        //'value' => get_post_meta($order->get_id(), '_guadagno_lordo_venditore', true),
        'value' => $order->get_meta('_guadagno_lordo_venditore'),
    ));

    // Guadagno Netto Venditore
    woocommerce_wp_text_input(array(
        'id' => 'guadagno_netto_venditore',
        'label' => __('Guadagno Netto Venditore', 'woocommerce'),
        //'value' => get_post_meta($order->get_id(), '_guadagno_netto_venditore', true),
        'value' => $order->get_meta('_guadagno_netto_venditore'),
    ));

    // Commissione Venditore
    woocommerce_wp_text_input(array(
        'id' => 'commissione_minedocs',
        'label' => __('Commissione Minedocs', 'woocommerce'),
        //'value' => get_post_meta($order->get_id(), '_commissione_minedocs', true),
        'value' => $order->get_meta('_commissione_minedocs'),
    ));

    // Fattura Richiesta Venditore (Combobox)
    woocommerce_wp_select(array(
        'id' => 'richiesta_fattura_venditore',
        'label' => __('Richiesta Fattura Venditore', 'woocommerce'),
        'options' => array(
            '' => __('Seleziona', 'woocommerce'),
            'non_richiesta' => __('Non richiesta', 'woocommerce'),
            'richiesta' => __('Richiesta', 'woocommerce'),
            'caricata' => __('Caricata', 'woocommerce'),
            'non_richiedibile_venditore_cancellato' => __('Non richiedibile (Il venditore non esiste più)', 'woocommerce'),
        ),
        //'value' => get_post_meta($order->get_id(), '_richiesta_fattura_venditore', true),
        'value' => $order->get_meta('_richiesta_fattura_venditore'),
    ));


    // Campo di upload del file
    echo '<p class="form-field">
            <label for="file_fattura_venditore">' . __('File Fattura Venditore', 'woocommerce') . '</label>
            <input type="file" id="file_fattura_venditore" name="file_fattura_venditore" />
          </p>';
    

    echo '</div>';
}

// Save custom fields
add_action('woocommerce_process_shop_order_meta', 'save_custom_order_fields');
function save_custom_order_fields($order_id){
    $order = wc_get_order($order_id);
    if (isset($_POST['id_venditore'])) {
        $order->update_meta_data('_id_venditore', sanitize_text_field($_POST['id_venditore']));
        //update_post_meta($order_id, '_id_venditore', sanitize_text_field($_POST['id_venditore']));
    }
    if (isset($_POST['guadagno_lordo_venditore'])) {
        $order->update_meta_data('_guadagno_lordo_venditore', sanitize_text_field($_POST['guadagno_lordo_venditore']));
        //update_post_meta($order_id, '_guadagno_lordo_venditore', sanitize_text_field($_POST['guadagno_lordo_venditore']));
    }
    if (isset($_POST['guadagno_netto_venditore'])) {
        $order->update_meta_data('_guadagno_netto_venditore', sanitize_text_field($_POST['guadagno_netto_venditore']));
        //update_post_meta($order_id, '_guadagno_netto_venditore', sanitize_text_field($_POST['guadagno_netto_venditore']));
    }
    if (isset($_POST['richiesta_fattura_venditore'])) {
        $order->update_meta_data('_richiesta_fattura_venditore', sanitize_text_field($_POST['richiesta_fattura_venditore']));
        //update_post_meta($order_id, '_richiesta_fattura_venditore', sanitize_text_field($_POST['richiesta_fattura_venditore']));
    }
    if (isset($_POST['commissione_minedocs'])) {
        $order->update_meta_data('_commissione_minedocs', sanitize_text_field($_POST['commissione_minedocs']));
        //update_post_meta($order_id, '_commissione_minedocs', sanitize_text_field($_POST['commissione_minedocs']));
    }

    // Gestione dell'upload del file
    if (isset($_FILES['file_fattura_venditore']) && !empty($_FILES['file_fattura_venditore']['name'])) {
        $uploaded_file = wp_handle_upload($_FILES['file_fattura_venditore'], array('test_form' => false));
        if (isset($uploaded_file['url'])) {
            $order->update_meta_data('_file_fattura_venditore', esc_url($uploaded_file['url']));
            //update_post_meta($order_id, '_file_fattura_venditore', esc_url($uploaded_file['url']));
        }
    }
    $order->save();
}

// Mostra il link del file nella pagina di amministrazione dell'ordine
add_action('woocommerce_admin_order_data_after_billing_address', 'display_file_link_in_order_admin');
function display_file_link_in_order_admin($order){
    //$file_url = get_post_meta($order->get_id(), '_file_fattura_venditore', true);
    $file_url = $order->get_meta('_file_fattura_venditore');
    if ($file_url) {
        echo '<p><strong>' . __('File Fattura Venditore', 'woocommerce') . ':</strong> <a href="' . esc_url($file_url) . '" target="_blank">' . __('Visualizza Fattura', 'woocommerce') . '</a></p>';
    }
}


// Getter for ID Venditore
function get_id_venditore($order_id) {
    //return get_post_meta($order_id, '_id_venditore', true);
    return wc_get_order($order_id)->get_meta('_id_venditore');
}

// Setter for ID Venditore
function set_id_venditore($order_id, $value) {
    //update_post_meta($order_id, '_id_venditore', sanitize_text_field($value));
    $order = wc_get_order($order_id);
    $order->update_meta_data('_id_venditore', sanitize_text_field($value));
    $order->save();
    
}

function get_id_venditore_key() {
    return '_id_venditore';
}

// Getter for Guadagno Lordo Venditore
function get_guadagno_lordo_venditore($order_id) {
    //return get_post_meta($order_id, '_guadagno_lordo_venditore', true);

    return wc_get_order($order_id)->get_meta('_guadagno_lordo_venditore');
}

// Setter for Guadagno Lordo Venditore
function set_guadagno_lordo_venditore($order_id, $value) {
    //update_post_meta($order_id, '_guadagno_lordo_venditore', sanitize_text_field($value));
    $order = wc_get_order($order_id);
    $order->update_meta_data('_guadagno_lordo_venditore', sanitize_text_field($value));
    $order->save();
    
}

// Getter for Guadagno Netto Venditore
function get_guadagno_netto_venditore($order_id) {
    //return get_post_meta($order_id, '_guadagno_netto_venditore', true);
    return wc_get_order($order_id)->get_meta('_guadagno_netto_venditore');
}

// Setter for Guadagno Netto Venditore
function set_guadagno_netto_venditore($order_id, $value) {
    //update_post_meta($order_id, '_guadagno_netto_venditore', sanitize_text_field($value));
    $order = wc_get_order($order_id);
    $order->update_meta_data('_guadagno_netto_venditore', sanitize_text_field($value));
    $order->save();
}

// Getter for Richiesta Fattura Venditore
function get_richiesta_fattura_venditore($order_id) {
    //return get_post_meta($order_id, '_richiesta_fattura_venditore', true);
    return wc_get_order($order_id)->get_meta('_richiesta_fattura_venditore');
}

// Setter for Richiesta Fattura Venditore
function set_richiesta_fattura_venditore($order_id, $value) {
    //update_post_meta($order_id, '_richiesta_fattura_venditore', sanitize_text_field($value));
    $order = wc_get_order($order_id);
    $order->update_meta_data('_richiesta_fattura_venditore', sanitize_text_field($value));
    $order->save();
}

function get_richiesta_fattura_venditore_key() {
    return '_richiesta_fattura_venditore';
}

// Getter for Commissione Minedocs
function get_commissione_minedocs($order_id) {
    //return get_post_meta($order_id, '_commissione_minedocs', true);
    return wc_get_order($order_id)->get_meta('_commissione_minedocs');
}

// Setter for Commissione Minedocs
function set_commissione_minedocs($order_id, $value) {
    //update_post_meta($order_id, '_commissione_minedocs', sanitize_text_field($value));
    $order = wc_get_order($order_id);
    $order->update_meta_data('_commissione_minedocs', sanitize_text_field($value));
    $order->save();
}

// Getter for File Fattura Venditore
function get_file_fattura_venditore($order_id) {
    //return get_post_meta($order_id, '_file_fattura_venditore', true);
    return wc_get_order($order_id)->get_meta('_file_fattura_venditore');
}

// Setter for File Fattura Venditore
function set_file_fattura_venditore($order_id, $file_url) {
    //update_post_meta($order_id, '_file_fattura_venditore', esc_url($file_url));
    $order = wc_get_order($order_id);
    $order->update_meta_data('_file_fattura_venditore', esc_url($file_url));
    $order->save();
}

// Genera e salva l'hash dell'ID ordine con un salt

//add_action('woocommerce_checkout_create_order', 'generate_order_hash', 10, 2);
add_action('woocommerce_order_status_completed', 'generate_order_hash', 10, 1);
function generate_order_hash($order_id) {
    error_log('Generating order hash for order ID: ' . $order_id); // Debugging line

    // Ottieni l'oggetto ordine
    $order = wc_get_order($order_id);


    // Genera l'hash dell'ID ordine
    $order_hash = generate_order_hid($order_id);
    error_log('Generated order hash: ' . $order_hash); // Debugging line

    // Salva l'hash come meta dell'ordine
    $order->update_meta_data('_order_hid', $order_hash);

    // Salva i meta dati dell'ordine
    $order->save();
}

function generate_order_hid($order_id) {
    // Definisci un salt (puoi personalizzarlo o renderlo dinamico)
    $salt = 'ORDER_HASH_SALT';

    // Genera l'hash
    $order_hash = hash('sha256', $order_id . $salt);

    return $order_hash;
}

function get_order_hash($order_id) {
    //return get_post_meta($order_id, '_order_hid', true);
    return wc_get_order($order_id)->get_meta('_order_hid');
}

function set_order_hash($order_id) {
    //update_post_meta($order_id, '_order_hid', sanitize_text_field($value));
    $order = wc_get_order($order_id);
    $value = generate_order_hid($order_id);
    $order->update_meta_data('_order_hid', $value);
    $order->save();
}

function get_order_id_by_hash($hash) {
    $orders = wc_get_orders(array(
        'meta_key' => '_order_hid',
        'meta_value' => $hash,
        'limit' => 1,
        'return' => 'ids',
    ));

    return !empty($orders) ? $orders[0] : false;
}

// Aggiungi una pagina negli strumenti di WordPress per generare l'hash di tutti gli ordini
add_action('admin_menu', 'add_generate_hash_page');
function add_generate_hash_page() {
    add_management_page(
        __('Genera Hash Ordini', 'woocommerce'),
        __('Genera Hash Ordini', 'woocommerce'),
        'manage_woocommerce',
        'generate-hash-orders',
        'generate_hash_orders_page'
    );
}

function generate_hash_orders_page() {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    if (isset($_POST['generate_hashes'])) {
        

    $order_ids = wc_get_orders(array(
        'limit' => -1,
        'return' => 'ids',
    ));

        if (empty($order_ids)) {
            echo '<div class="error"><p>' . __('Nessun ordine trovato.', 'woocommerce') . '</p></div>';
            return;
        }
        foreach ($order_ids as $order_id) {

            set_order_hash($order_id);
        }

        echo '<div class="updated"><p>' . __('Hash generati con successo per tutti gli ordini.', 'woocommerce') . '</p></div>';
    }

    echo '<div class="wrap">';
    echo '<h1>' . __('Genera Hash per Ordini', 'woocommerce') . '</h1>';
    echo '<form method="post">';
    echo '<p>' . __('Clicca sul pulsante qui sotto per generare l\'hash per tutti gli ordini esistenti.', 'woocommerce') . '</p>';
    echo '<p><input type="submit" name="generate_hashes" class="button-primary" value="' . __('Genera Hash', 'woocommerce') . '"></p>';
    echo '</form>';
    echo '</div>';
}

// Mostra l'hash dell'ordine nella schermata di modifica dell'ordine
add_action('woocommerce_admin_order_data_after_order_details', 'display_order_hash_in_admin');
function display_order_hash_in_admin($order) {
    $order_hash = $order->get_meta('_order_hid');
    if ($order_hash) {
        echo '<p><strong>' . __('Order Hash', 'woocommerce') . ':</strong> ' . esc_html($order_hash) . '</p>';
    }
}


function get_orders_with_invoice_request() {

        // Ottieni l'ID dell'utente loggato
    $user_id = get_current_user_id();
    $key_id_venditore = get_id_venditore_key();

    // Ottieni tutti gli ordini di WooCommerce
    $venditore_orders = wc_get_orders(array(
        'orderby' => 'date',
        'order' => 'DESC',
        'limit' => -1,
        'meta_query' => array(
            array(
                'key' => $key_id_venditore,
                'value' => $user_id,
                'compare' => '='
            )
        )
    ));

    // Separare gli ordini con fattura richiesta dagli altri
    $orders_with_invoice_request = array();
    $other_orders = array();

    foreach ($venditore_orders as $order) {
        if (get_richiesta_fattura_venditore($order->get_id()) == 'richiesta') {
            $orders_with_invoice_request[] = $order;
        } else {
            $other_orders[] = $order;
        }
    }

    return $orders_with_invoice_request;
}

function get_number_of_orders_with_invoice_request() {
    $orders_with_invoice_request = get_orders_with_invoice_request();
    return count($orders_with_invoice_request);
}
