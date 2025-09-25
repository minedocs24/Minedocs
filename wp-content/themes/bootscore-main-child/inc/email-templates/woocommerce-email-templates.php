<?php
/**
 * Gestione dei template email di WooCommerce
 */

// Disabilita le email predefinite di WooCommerce
function disable_woocommerce_emails($email_classes) {
    // Rimuovi le email che non vogliamo inviare
    unset($email_classes['WC_Email_New_Order']); // Email per nuovo ordine (admin)
    unset($email_classes['WC_Email_Cancelled_Order']); // Email per ordine cancellato
    unset($email_classes['WC_Email_Failed_Order']); // Email per ordine fallito
    unset($email_classes['WC_Email_Customer_On_Hold_Order']); // Email per ordine in attesa
    unset($email_classes['WC_Email_Customer_Processing_Order']); // Email per ordine in elaborazione
    unset($email_classes['WC_Email_Customer_Completed_Order']); // Email per ordine completato
    unset($email_classes['WC_Email_Customer_Refunded_Order']); // Email per ordine rimborsato
    unset($email_classes['WC_Email_Customer_Invoice']); // Email per fattura
    unset($email_classes['WC_Email_Customer_Note']); // Email per note cliente
    
    return $email_classes;
}
// add_filter('woocommerce_email_classes', 'disable_woocommerce_emails');

// Funzione helper per inviare email
function send_woocommerce_email($order_id, $template_name) {
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $user = $order->get_user();
    $user_name = get_user_meta($user->ID, 'first_name', true) ?: $user->display_name;
    $user_email = $order->get_billing_email();

    // Cattura l'output del template
    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/woocommerce/' . $template_name . '.php');
    $message = ob_get_clean();

    // Invia l'email
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($user_email, $subject, $message, $headers);
}

// Template per email di nuovo ordine
function custom_woocommerce_new_order_email($order_id) {
    send_woocommerce_email($order_id, 'new-order');
}
add_action('woocommerce_order_status_pending', 'custom_woocommerce_new_order_email');

// Template per email di ordine completato
function custom_woocommerce_completed_order_email($order_id) {
    send_woocommerce_email($order_id, 'completed-order');
}
add_action('woocommerce_order_status_completed', 'custom_woocommerce_completed_order_email');

// Template per email di ordine in elaborazione
function custom_woocommerce_processing_order_email($order_id) {
    send_woocommerce_email($order_id, 'processing-order');
}
// add_action('woocommerce_order_status_processing', 'custom_woocommerce_processing_order_email');

// Template per email di ordine in attesa
function custom_woocommerce_on_hold_order_email($order_id) {
    send_woocommerce_email($order_id, 'on-hold-order');
}
add_action('woocommerce_order_status_on-hold', 'custom_woocommerce_on_hold_order_email'); 