<?php
/**
 * Carica tutti i template email
 */

// Carica il template header comune
require_once get_stylesheet_directory() . '/inc/email-templates/minedocs-email-header.php';

// Carica i template specifici
require_once get_stylesheet_directory() . '/inc/email-templates/email-reset-password.php';
require_once get_stylesheet_directory() . '/inc/email-templates/email-password-changed.php';
require_once get_stylesheet_directory() . '/inc/email-templates/email-report-review.php';
require_once get_stylesheet_directory() . '/inc/email-templates/email-expiring-points.php';
require_once get_stylesheet_directory() . '/inc/email-templates/email-conferma-indirizzo-paypal.php';

// Carica gli hook di WordPress
require_once get_stylesheet_directory() . '/inc/email-templates/wordpress-email-templates.php'; 