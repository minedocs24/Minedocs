<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//lasciarlo per primo
require_once __DIR__ . '/inc/admin/impostazioni.php';
require_once __DIR__ . '/inc/ruoli_utenti.php';

require_once __DIR__ .'/vendor/setasign/fpdi/src/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/vendor/setasign/fpdf/fpdf.php' ;
require_once __DIR__ . '/vendor/setasign/fpdi/src/autoload.php';
require_once __DIR__ . '/vendor/stripe/stripe-php/init.php';

require_once __DIR__ . '/vendor/swiper/swiper_load.php';
require_once __DIR__ . '/inc/load_template_part.php';

require_once __DIR__ . '/inc/ruoli_utenti.php';
// Google Tag Manager
if (get_option('google_tag_manager_enabled') == '1') {
    require_once __DIR__ . '/inc/google_tag_manager.php';
}

require_once __DIR__ . '/inc/log/transazioni.php';
require_once __DIR__ . '/inc/log/transaction.class.php';
require_once __DIR__ . '/inc/gestione_punti/gestione_punti.php';
require_once __DIR__ . '/inc/commissioni.php';
require_once __DIR__ . '/inc/login-protection/login_logs.php';
require_once __DIR__ . '/inc/servizi-esterni/verifica-mail-disify.php';

require_once __DIR__ . '/inc/funzioni-controllo-campi.php';
require_once __DIR__ . '/inc/taxonomies.php';
require_once __DIR__ . '/inc/mail-approval.php';
require_once __DIR__ . '/inc/userFunctions.php';
require_once __DIR__ . '/inc/postFunctions.php';
require_once __DIR__ . '/inc/utils.php';
require_once __DIR__ . '/inc/recensioni.php';
require_once __DIR__ . '/inc/products.php';
require_once __DIR__ . '/inc/abbonamento.php';
require_once __DIR__ . '/inc/user-profile-functions.php';
require_once __DIR__ . '/inc/controlli-download-file.php';
require_once __DIR__ . '/inc/safe-download.php';
require_once __DIR__ . '/inc/saldo.php';
require_once __DIR__ . '/inc/codice-fiscale.php';
require_once __DIR__ . '/inc/registrazione.php';
require_once __DIR__ . '/inc/impostazioni_utenti.php';
require_once __DIR__ . '/inc/nuovo-profilo-utente.php';
require_once __DIR__ . '/inc/login-signin.php';
require_once __DIR__ . '/inc/login.php';
require_once __DIR__ . '/inc/regole-punti.php';
require_once __DIR__ . '/inc/nazioni.php';
require_once __DIR__ . '/inc/orders.php';
require_once __DIR__ . '/inc/fatturazione-venditori.php';
require_once __DIR__ . '/inc/faq.php';
require_once __DIR__ . '/inc/ricerca.php';
require_once __DIR__ . '/inc/contatti.php';
require_once __DIR__ . '/inc/api.php';
require_once __DIR__ . '/inc/wallet.php';
require_once __DIR__ . '/inc/product-functions.php';
require_once __DIR__ . '/inc/profilo-utente/documenti-caricati.php';
require_once __DIR__ . '/inc/profilo-utente/documenti-acquistati.php';
require_once __DIR__ . '/inc/password-reset-handler.php';

require_once __DIR__ . '/modals/come_guadagnare_punti.php';

require_once __DIR__ . '/inc/studia-con-ai.php';
require_once __DIR__ . '/inc/studia-AI/coda-job.php';
require_once __DIR__ . '/inc/studia-AI/gestione-documenti.php';
require_once __DIR__ . '/inc/studia-AI/callbacks.php';

use setasign\Fpdi\Tcpdf\Fpdi;
// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:
if (!session_id()) {
    session_start();
}

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

function load_inter_font() {
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap', array(), null);
}
//add_action('wp_enqueue_scripts', 'load_inter_font');

function load_poppins_font() {
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap', array(), null);
}
add_action('wp_enqueue_scripts', 'load_poppins_font');

/*function load_sora_font() {
    wp_enqueue_style('sora-font', 'https://fonts.googleapis.com/css2?family=Sora:wght@400;600&display=swap', array(), null);
}
add_action('wp_enqueue_scripts', 'load_sora_font');*/


function my_global_scripts() {
    //wp_enqueue_script('pdfJsLib-script', 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js', array(), '1.0.0', true);
    wp_enqueue_script('pdfJsLib-script', get_stylesheet_directory_uri() .'/vendor/pdfjs-dist/pdf.min.js', array(), '1.0.0', true);
    wp_enqueue_script('pdfViewer_js', get_stylesheet_directory_uri(  ) . '/assets/js/pdfviewer.js', array('pdfJsLib-script'), '1.0.0', true);
    
    wp_enqueue_script('confetti-js', get_stylesheet_directory_uri() . '/vendor/confetti/confetti.browser.min.js', array(), '1.0.0', true);

    wp_enqueue_script('home-page-script', get_stylesheet_directory_uri() . '/assets/js/home.js', array(), '1.0.0', true);
    wp_enqueue_script('general-functions-script', get_stylesheet_directory_uri() . '/assets/js/functions.js', array('jquery'), null, true);
    wp_localize_script('general-functions-script', 'env_general_functions', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'modal_come_guadagnare_punti' => PATH_MODAL_COME_GUADAGNARE_PUNTI,
    ));
        
    wp_enqueue_script('premium-plans-script', get_stylesheet_directory_uri() . '/assets/js/premium-plans.js', array('jquery'), null, true);
    wp_enqueue_script('point-packs-script', get_stylesheet_directory_uri() . '/assets/js/point-packs.js', array('jquery'), null, true);
    // wp_enqueue_script('popper-js', get_stylesheet_directory_uri() . '/assets/js/popper.min.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'my_global_scripts', 100);

function my_enqueue_scripts_update_show_points() {
    if (!is_user_logged_in()) {
        return;
    }

    wp_enqueue_script('update_show_points', get_stylesheet_directory_uri() . '/assets/js/update_points_in_page.js', array('jquery'), null, true);

    wp_localize_script('update_show_points', 'env_update_points', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce_UpdatePoints'    =>  wp_create_nonce('update_points_nonce')
    ));


}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts_update_show_points');

function my_enqueue_scripts() {
    // Registra il tuo script principale
    wp_enqueue_script('call_download', get_stylesheet_directory_uri() . '/assets/js/call_download.js', array('jquery'), null, true);

    // Localizza lo script e passa l'url di admin-ajax.php
    wp_localize_script('call_download', 'env_call_download', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'    =>  wp_create_nonce('nonce_download_file'),
        'sottoscrizione_pro_url' => PIANI_PRO_PAGE,
        'ricarica_punti_pro_url' => PACCHETTI_PUNTI_PAGE,
    ));
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');


if ( !function_exists('return_template_part') ):
    function return_template_part($slug, $name = null, $args = array()) {
        ob_start();
        get_template_part($slug, $name, $args);
        return ob_get_clean();
    }
endif;



function load_custom_modal() {
    include get_stylesheet_directory() . '/template-parts/commons/custom-popup.php';
    
}
add_action('get_header', 'load_custom_modal', 100);



// END ENQUEUE PARENT ACTION

include_once( get_stylesheet_directory() . '/inc/functions_prices.php' );
include_once( get_stylesheet_directory() . '/inc/upload.php' );
//include_once( get_stylesheet_directory() . '/inc/upload-1.php' );


function enqueue_select2() {
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), null);
}
add_action('wp_enqueue_scripts', 'enqueue_select2');

function add_new_taxonomy_term() {
    if (isset($_POST['term_name'])) {
        $term_name = sanitize_text_field($_POST['term_name']);
        
        // Aggiungi il nuovo termine alla tassonomia "nome_corso"
        $new_term = wp_insert_term($term_name, 'nome_corso');
        
        if (is_wp_error($new_term)) {
            wp_send_json_error('Errore nell\'aggiunta del termine.');
        } else {
            wp_send_json_success($new_term);
        }
    }
}
add_action('wp_ajax_add_new_term', 'add_new_taxonomy_term');
add_action('wp_ajax_nopriv_add_new_term', 'add_new_taxonomy_term');


function assign_points_to_user() {
    // Verifica che l'utente sia loggato
    if (is_user_logged_in()) {
        $points = 0;
        if (isset($_POST['points'])) {
            $points = sanitize_text_field($_POST['points']);
        } 
        // Ottieni l'ID dell'utente loggato
        $user_id = get_current_user_id();

        $data = [
            'user_id' => $user_id, 
            'order_id' => null,
            'hidden_to_user' => false,
            'description' => 'Accredito punti blu per caricamento documento.',
        ];

        // Aggiungi i punti all'utente
        get_sistema_punti('blu')->aggiungi_punti($user_id, $points, $data);

        // Ottieni i punti aggiornati
        $new_points = get_sistema_punti('blu')->get_ottieni_totale_punti($user_id);

        // Risposta di successo
        wp_send_json_success(array('new_points', $new_points));
    } else {
        // Risposta di errore
        wp_send_json_error('User not logged in');
    }
}
// Registra l'azione AJAX per utenti loggati e non loggati
add_action('wp_ajax_assign_points_to_user', 'assign_points_to_user');
add_action('wp_ajax_nopriv_assign_points_to_user', 'assign_points_to_user');


function bootscore_main_child_template_redirect() {
    if (is_search() && 'product' === get_query_var('post_type')) {
        include(get_stylesheet_directory(  ) . '/search.php');
        exit();
    }
}
add_action('template_redirect', 'bootscore_main_child_template_redirect');

function filter_search_query($query) {
    if ($query->is_search() && $query->is_main_query()) {
        if (isset($_GET['post_type']) && $_GET['post_type'] == 'product') {
            $query->set('post_type', 'product');
        }
        if (isset($_GET['tipo_prodotto']) && $_GET['tipo_prodotto'] == 'documento') {
            $tax_query = array(
                array(
                    'taxonomy' => 'tipo_prodotto',
                    'field'    => 'slug',
                    'terms'    => 'documento',
                    'include_children' => true,
                ),
            );
            $query->set('tax_query', $tax_query);
        }
        $query->set('posts_per_page', 10);
    }
}
add_action('pre_get_posts', 'filter_search_query');



function enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');



function enqueue_billing_data_scripts() {
    if (is_page('profilo-utente-guadagni')) {
        wp_enqueue_script('billing-data-popup', get_stylesheet_directory_uri() . '/assets/js/profilo-utente-guadagni.js', array('jquery'), null, true);

        wp_localize_script('billing-data-popup', 'env_profilo_utente_guadagni', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce_saveBillingData'    => wp_create_nonce('nonce_save_billing_data'),
            'nonce_getBillingData'    => wp_create_nonce('nonce_get_billing_data'),
            'nonce_withdrawBalance'    => wp_create_nonce('nonce_withdraw_balance'),
            'nonce_updatePaypalInfo'   => wp_create_nonce('nonce_update_paypal_info'),
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_billing_data_scripts');

function enqueue_user_settings_scripts_() {
    if (is_page('profilo-utente-impostazioni') ) {
        wp_enqueue_script('user-settings-script', get_stylesheet_directory_uri() . '/assets/js/profilo-utente-impostazioni.js', array('jquery'), null, true);

        wp_localize_script('user-settings-script', 'env_profilo_utente_impostazioni', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    =>  wp_create_nonce('my_nonce'),
            'nonce_editUserFields' => wp_create_nonce('nonce_edit_user_fields'),
            'nonce_changePassword' => wp_create_nonce('nonce_change_password'),
            'nonce_disableAutomaticRenew' => wp_create_nonce('nonce_disable_automatic_renew'),
            'nonce_enableAutomaticRenew' => wp_create_nonce('nonce_enable_automatic_renew'),
            'nonce_cancelAutomaticRenew' => wp_create_nonce('nonce_cancel_automatic_renew'),
            'nonce_deleteAccount' => wp_create_nonce('nonce_delete_account'),
            'home_url' => home_url( ),
        ));
    }
}

function enqueue_user_profile_scripts() {
    if (is_page('profilo-utente')) {
        wp_enqueue_script('user-profile-script', get_stylesheet_directory_uri() . '/assets/js/profilo-utente.js', array('jquery'), null, true);

        wp_localize_script('user-profile-script', 'env_profilo_utente', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('my_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_user_profile_scripts');

function enqueue_user_settings_scripts($slug, $name, $args) {
    if ($slug === 'template-parts/profilo-utente/impostazioni') {
        wp_enqueue_script('user-settings-script', get_stylesheet_directory_uri() . '/assets/js/profilo-utente-impostazioni.js', array('jquery'), null, true);

        wp_localize_script('user-settings-script', 'env_profilo_utente_impostazioni', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    =>  wp_create_nonce('my_nonce')
        ));
    }
}

add_action('wp_enqueue_scripts', 'enqueue_user_settings_scripts_');
//add_action('get_template_part', 'enqueue_user_settings_scripts', 10, 3);



function enqueue_user_movements_scripts() {
    if (is_page('profilo-utente-movimenti') || is_page( 'nuovo-profilo-utente' )) {
        wp_enqueue_script('user-movements-script', get_stylesheet_directory_uri() . '/assets/js/profilo-utente-movimenti.js', array('jquery'), null, true);

        wp_localize_script('user-movements-script', 'env_profilo_utente_movimenti', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    =>  wp_create_nonce('my_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_user_movements_scripts');


function enqueue_login_signin_scripts() {
    if (is_page('login') || is_page('signin')) {
        wp_enqueue_script('login-signin-script', get_stylesheet_directory_uri() . '/assets/js/login-signin.js', array('jquery'), null, true);

        wp_localize_script('login-signin-script', 'env_login_signin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('my_nonce'),
            'redirect_url' => site_url( ) . '/registrazione-utente'
        ));
    }

    if (is_page('login')) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $login_attempts = get_login_attempts($ip);
        wp_enqueue_script('login-script', get_stylesheet_directory_uri() . '/assets/js/login.js', array('jquery'), null, true);
        wp_localize_script('login-signin-script', 'env_login', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'verifica_email_nonce'    => wp_create_nonce('verifica_email'),
            'recaptcha_tentativi' => NUMERO_TENTATIVI_LOGIN_PRIMA_DI_CAPTCHA,
            // 'login_attempts' => isset($_SESSION['login_attempts']) ? intval($_SESSION['login_attempts']) : 0,
            'login_attempts' => $login_attempts,
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_login_signin_scripts');

function enqueue_registration_scripts() {
    if (is_page('registrazione-utente')) {
        wp_enqueue_script('registration-script', get_stylesheet_directory_uri() . '/assets/js/registrazione.js', array('jquery'), null, true);

        wp_localize_script('registration-script', 'env_registrazione_utente', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'redirect_url' => site_url( ),
            // 'checkout_url' => wc_get_checkout_url(),
            'nonce'    =>  wp_create_nonce('my_nonce'),
            'nonce_registrationCompleted' => wp_create_nonce('nonce_registration_completed'),
            'nonce_saveProfilationData' => wp_create_nonce('nonce_save_profilation_data'),
            'nonce_verificaNickname' => wp_create_nonce('nonce_verifica_nickname'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_registration_scripts');

function enqueue_user_uploaded_documents_scripts() {
    if (is_page('profilo-utente-documenti-caricati')) {
        wp_enqueue_script('user-uploaded-documents-script', get_stylesheet_directory_uri() . '/assets/js/profilo-utente-documenti-caricati.js', array('jquery'), null, true);

        wp_localize_script('user-uploaded-documents-script', 'env_profilo_utente_documenti_caricati', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    =>  wp_create_nonce('my_nonce'),
            'delete_nonce' => wp_create_nonce('delete_file_nonce'),
            'edit_page_url' => CARICAMENTO_DOCUMENTO_PAGE, 
            'assets_url' => get_stylesheet_directory_uri() . '/assets/',
            'logo' => MINEDOCS_LOGO,
            'nonce_load_user_uploaded_documents' => wp_create_nonce('nonce_load_user_uploaded_documents'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_user_uploaded_documents_scripts');

function enqueue_user_purchased_documents_scripts() {
    if (is_page('profilo-utente-documenti-acquistati')) {
        wp_enqueue_script('user-purchased-documents-script', get_stylesheet_directory_uri() . '/assets/js/profilo-utente-documenti-acquistati.js', array('jquery'), null, true);

        wp_localize_script('user-purchased-documents-script', 'env_profilo_utente_documenti_acquistati', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    =>  wp_create_nonce('my_nonce'),
            'delete_nonce' => wp_create_nonce('delete_file_nonce'),
            'write_review_nonce' => wp_create_nonce('submit_review_nonce'),
            'assets_url' => get_stylesheet_directory_uri() . '/assets/',
            'logo' => MINEDOCS_LOGO,
            'user_purchased_nonce' => wp_create_nonce('nonce_load_user_purchased_documents'),
        ));
    }
}

add_action('wp_enqueue_scripts', 'enqueue_user_purchased_documents_scripts');

function enqueue_upload_scripts() {
    if (is_page('upload-2')) {
        wp_enqueue_script('upload-script', get_stylesheet_directory_uri() . '/assets/js/upload.js', array('jquery'), null, true);

        wp_localize_script('upload-script', 'env_upload', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce_upload'    => wp_create_nonce('upload_nonce'),
            'nonce_update'    => wp_create_nonce('update_nonce'),
            'nonce_file_upload' => wp_create_nonce('file_upload_nonce'),
            'home_url' => home_url( ),
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_upload_scripts');


function enqueue_login_email_verificata_scripts() {
        wp_enqueue_script('email-non-verificata', get_stylesheet_directory_uri() . '/assets/js/email_non_verificata.js', array('jquery'), null, true);
        wp_localize_script('email-non-verificata', 'env_email_non_verificata', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('email_non_verificata_nonce'),
            'logout_url' => wp_logout_url(home_url() ),
            'resend_email_nonce' => wp_create_nonce('resend_email_nonce'),
        ));
}
add_action('wp_enqueue_scripts', 'enqueue_login_email_verificata_scripts');

function enqueue_ricerca_scripts() {
    if (is_page('ricerca')) {
        wp_enqueue_script('search-script', get_stylesheet_directory_uri() . '/assets/js/ricerca.js', array('jquery'), null, true);
        wp_localize_script('search-script', 'env_search', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    =>  wp_create_nonce('my_nonce'),
        ));

        wp_enqueue_style('search-style', get_stylesheet_directory_uri() . '/assets/css/ricerca.css', array(), null);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_ricerca_scripts');

function enqueue_contact_scripts() {
    if (is_page('contatti')) {
        wp_enqueue_script('contact-script', get_stylesheet_directory_uri() . '/assets/js/contatti.js', array('jquery'), null, true);

        wp_localize_script('contact-script', 'env_contatti', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('contatti_nonce'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_contact_scripts');

function enqueue_studia_con_ai_scripts() {
    if (is_page('studia-con-ai')) {
        // wp_enqueue_script('studia-con-ai-script', get_stylesheet_directory_uri() . '/assets/js/studia-con-ai.js', array('jquery'), null, true);

        // wp_localize_script('studia-con-ai-script', 'env_studia_con_ai', array(
        //     'ajax_url' => admin_url('admin-ajax.php'),
        //     'nonce'    => wp_create_nonce('studia_con_ai_nonce'),
        //     'nonce_generate_summary' => wp_create_nonce('nonce_generate_summary'),
        // ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_studia_con_ai_scripts');

// if (defined('WP_DEBUG') && WP_DEBUG) {
//     add_filter('wp_error_log', function($message) {
//        //if (strpos($message, '_load_textdomain_just_in_time') != false) {
//          //   return false; // Non loggare queste righe
//         //}
//         return "CORNUTO:" . $message; // Logga tutto il resto
//     });
// }


// if(is_page('profilo-utente-guadagni')) {
//     add_action('wp_enqueue_scripts', 'enqueue_billing_data_scripts');
// }



// TODO: Vedere come gestire questo script che rallenta il caricamento della pagina
/** Disable Ajax Call from WooCommerce */
/*add_action('wp_footer', 'dequeue_woocommerce_cart_fragments', 100);
function dequeue_woocommerce_cart_fragments() {
    if (is_front_page()) {
        wp_dequeue_script('wc-cart-fragments');
    }
}
*/


// L'utente non può aggiungere più di un prodotto abbonamento nel carrello
add_filter('woocommerce_add_to_cart_validation', 'verifica_abbonamenti_nel_carrello', 10, 3);
function verifica_abbonamenti_nel_carrello($passed, $product_id, $quantity) {
    $product = wc_get_product($product_id);
    if ($product && strpos($product->get_sku(), 'ABBPRO') === 0) {
        $cart_items = WC()->cart->get_cart();
        foreach ($cart_items as $cart_item) {
            $cart_product = $cart_item['data'];
            if ($cart_product && strpos($cart_product->get_sku(), 'ABBPRO') === 0) {
                wc_add_notice('Non puoi aggiungere più di un prodotto abbonamento nel carrello.', 'error');
                return false;
            }
        }
    }
    return $passed;
}

function controllo_completamento_informazioni() {
    // URL della pagina di completamento informazioni
    $pagina_completamento_url = home_url('/registrazione-utente');

    // Se l'utente non è loggato, non fare nulla
    if (!is_user_logged_in()) {
        return;
    }

    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();

    // Se l'utente è un amministratore, non fare nulla
    if (current_user_can('administrator')) {
        return;
    }

    // Pagine escluse dal controllo
    $excluded_pages = array('registrazione-utente', 'privacy-policy', 'pagina-contatti');  //TODO
    if (is_page($excluded_pages)) {
        return; // Non applicare il controllo a queste pagine
    }

    // Recupera il valore del flag "completamento_informazioni_utente"
    $completamento_informazioni_utente = get_user_meta($user_id, 'completamento_informazioni_utente', true);

    // stampa un log di completamento_informazioni_utente
    error_log('completamento_informazioni_utente2: ' . $completamento_informazioni_utente . " UserID: " . $user_id);

    // Se l'utente non ha completato le informazioni, reindirizzalo alla pagina di completamento
    if ($completamento_informazioni_utente !== '1') {
        $redirect_url = add_query_arg($_GET, $pagina_completamento_url);
        wp_redirect($redirect_url);
        //wp_redirect($pagina_completamento_url);
        exit; // Termina l'esecuzione per evitare ulteriori caricamenti
    }
}
add_action('template_redirect', 'controllo_completamento_informazioni');



function log_user_request_info() {
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $request_uri = $_SERVER['REQUEST_URI'];
    $user_id = is_user_logged_in() ? get_current_user_id() : 'Guest';

    $log_message = sprintf(
        "User ID: %s, IP: %s, User Agent: %s, Request URI: %s, Session Data: %s",
        $user_id,
        $user_ip,
        $user_agent,
        $request_uri,
        json_encode($_SESSION)
    );

    error_log($log_message);
}
add_action('template_redirect', 'log_user_request_info');


/**
 * Register Custom Navigation Walker
 */
function register_navwalker(){
	require_once get_stylesheet_directory() . '/vendor/bootstrap-walker/class-wp-bootstrap-navwalker.php';
}
add_action( 'after_setup_theme', 'register_navwalker' );


add_action('pre_get_posts', function ($query) {
    // Assicuriamoci di non modificare la query nell'admin o nelle query AJAX
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Se stiamo visualizzando un singolo post
    if ($query->is_single) {
        $current_user_id = get_current_user_id();
        
        // Otteniamo il post richiesto
        global $wp_query;
        $post_id = $wp_query->get('p');
        $post = get_post($post_id);

        // Se il post è in bozza e l'utente è l'autore, modifichiamo la query
        if ($post && $post->post_status === 'draft' && $post->post_author == $current_user_id) {
            $query->set('post_status', array('publish', 'draft'));
        }
    }
});



register_nav_menus( array(
    'primary' => __( 'Primary Menu', 'THEMENAME' ),
) );


function redirect_search_to_custom_page() {
    if (is_search() && !is_admin()) {
        $search_query = get_query_var('s');
        wp_redirect(home_url('/ricerca') . '?search=' . urlencode($search_query));
        exit();
    }
}
add_action('template_redirect', 'redirect_search_to_custom_page');


// function custom_coming_soon_redirect() {
//     // URL della pagina "Coming Soon" personalizzata
//     $coming_soon_url = 'https://comingsoon.minedocs.it:8443/';
//     // Token segreto per bypassare la modalità "Coming Soon"
//     $secret_token = 'h687jGS6U';

//     // Log iniziale
//     error_log('custom_coming_soon_redirect triggered');

//     // IP ANDRE ALLOWED WITHAOT Cookie
//     if ($_SERVER['REMOTE_ADDR'] === '151.45.191.222' || $_SERVER['REMOTE_ADDR'] === '37.101.95.104') {
//         error_log('Access allowed for IP: 151.45.191.222');
//         return; // Permetti l'accesso
//     }

//     // IP ROBERTO ALLOWED WITHAOT Cookie
//     if ($_SERVER['REMOTE_ADDR'] === '151.50.1.204') {
//         error_log('Access allowed for Roberto');
//         return; // Permetti l'accesso
//     }

//     // IP Francesco ALLOWED WITHAOT Cookie
//     if ($_SERVER['REMOTE_ADDR'] === '81.56.22.152') {
//         error_log('Access allowed for Francesco');
//         return; // Permetti l'accesso
//     }

//     // Verifica se il cookie di bypass è impostato
//     if ( isset($_COOKIE['bypass_coming_soon']) && $_COOKIE['bypass_coming_soon'] === $secret_token ) {
//         error_log('Bypass cookie found, allowing access');
//         return; // L'utente ha già il cookie, permetti l'accesso
//     }

//     // Verifica se il parametro URL 'skip_maint' corrisponde al token segreto
//     if ( isset($_GET['skip_maint']) && $_GET['skip_maint'] === $secret_token ) {
//         error_log('URL parameter skip_maint matches secret token, setting bypass cookie');
//         // Imposta un cookie per bypassare la modalità "Coming Soon" nelle visite future
//         setcookie('bypass_coming_soon', $secret_token, time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN);
//         return; // Permetti l'accesso
//     }

//     // Se l'utente è un amministratore loggato, permetti l'accesso
//     if ( current_user_can('manage_options') ) {
//         error_log('User is an admin, allowing access');
//         return;
//     }

//     // Log di reindirizzamento
//     error_log('Redirecting to Coming Soon page: ' . $coming_soon_url);

//     // Reindirizza tutti gli altri utenti alla pagina "Coming Soon"
//     wp_redirect($coming_soon_url);
//     header('Location: ' . $coming_soon_url);
//     exit;
// }
// add_action('wp_head', 'custom_coming_soon_redirect', 1, 10);

function disabilita_rest_api_per_ospiti($result) {
    if (!current_user_can('administrator')) {
        return new WP_Error('rest_disabled', __('REST API disabilitata'), ['status' => 403]);
    }
    return $result;
}
//add_filter('rest_authentication_errors', 'disabilita_rest_api_per_ospiti');


// Disabilita il redirect automatico di ?author=ID -> /author/username
remove_action('template_redirect', 'redirect_canonical');

add_filter('xmlrpc_enabled', '__return_false');

remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('template_redirect', 'rest_output_link_header');

/*
add_filter('rest_endpoints', function ($endpoints) {
    $allowed_routes = [
        '/custom/v1/upload-document',
        '/jwt-auth/v1/token',
    ];

    foreach ($endpoints as $route => $callback) {
        $is_allowed = false;

        foreach ($allowed_routes as $allowed_route) {
            if (stripos(rtrim($route, '/'), $allowed_route) === 0) {
                $is_allowed = true;
                break;
            }
        }

        if (!$is_allowed) {
            unset($endpoints[$route]);
        }
    }

    return $endpoints;
}, 999);
*/


add_action('set_auth_cookie', function($auth_cookie, $expire, $expiration, $user_id, $scheme, $token) {
    error_log('Set auth cookie with SameSite=Lax for user ID: ' . $user_id);
    my_wp_set_auth_cookie_with_samesite($user_id);
}, 10, 6);

function my_wp_set_auth_cookie_with_samesite($user_id, $remember = false, $secure = '') {
    error_log('my_wp_set_auth_cookie_with_samesite triggered for user ID: ' . $user_id);
    if ('' === $secure) {
        $secure = is_ssl();
    }

    // Calcolo delle scadenze
    if ($remember) {
        $expiration = time() + apply_filters('auth_cookie_expiration', 1209600, $user_id, $remember); // 14 giorni
    } else {
        $expiration = time() + apply_filters('auth_cookie_expiration', 172800, $user_id, $remember); // 2 giorni
    }

    $auth_cookie = wp_generate_auth_cookie($user_id, $expiration, 'auth');
    $secure_auth_cookie = wp_generate_auth_cookie($user_id, $expiration, 'secure_auth');
    $logged_in_cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

    $auth_cookie_name = 'wordpress_' . COOKIEHASH;
    $secure_auth_cookie_name = 'wordpress_sec_' . COOKIEHASH;
    $logged_in_cookie_name = 'wordpress_logged_in_' . COOKIEHASH;

    $path = COOKIEPATH;
    $domain = COOKIE_DOMAIN;

    // Imposta i cookie con SameSite=Lax
    setcookie($auth_cookie_name, $auth_cookie, [
        'expires'  => $expiration,
        'path'     => $path,
        'domain'   => $domain,
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    setcookie($secure_auth_cookie_name, $secure_auth_cookie, [
        'expires'  => $expiration,
        'path'     => $path,
        'domain'   => $domain,
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    error_log('Secure auth cookie set: ' . $secure_auth_cookie_name . '=' . $secure_auth_cookie);
    error_log('Logged in cookie set: ' . $logged_in_cookie_name . '=' . $logged_in_cookie);
    setcookie($logged_in_cookie_name, $logged_in_cookie, [
        'expires'  => $expiration,
        'path'     => $path,
        'domain'   => $domain,
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    error_log('Auth cookie set: ' . $auth_cookie_name . '=' . $auth_cookie);
    error_log('Secure auth cookie set: ' . $secure_auth_cookie_name . '=' . $secure_auth_cookie);
    error_log('Logged in cookie set: ' . $logged_in_cookie_name . '=' . $logged_in_cookie);
}




add_action('init', function () {
    // Inizializza output buffering per modificare gli header Set-Cookie
    if (!headers_sent()) {
        ob_start(function ($buffer) {
            // Rimuovi tutti i Set-Cookie standard
            $headers = headers_list();
            foreach (headers_list() as $header) {
                if (stripos($header, 'Set-Cookie:') === 0) {
                    header_remove('Set-Cookie');
                }
            }

            // Reinvia i cookie con i flag corretti
            $new_headers = [];
            foreach ($headers as $header) {
                if (stripos($header, 'Set-Cookie:') === 0) {
                    $cookie = trim(substr($header, strlen('Set-Cookie:')));
                    if (stripos($cookie, 'Secure') === false) {
                        $cookie .= '; Secure';
                    }
                    if (stripos($cookie, 'HttpOnly') === false) {
                        $cookie .= '; HttpOnly';
                    }
                    if (stripos($cookie, 'SameSite=') === false) {
                        $cookie .= '; SameSite=Lax';
                    }
                    $new_headers[] = 'Set-Cookie: ' . $cookie;
                }
            }

            // Reinvia gli header aggiornati
            foreach ($new_headers as $h) {
                header($h, false);
            }

            return $buffer;
        });
    }
});


function filter_mail_from_email( $wp_email ) {
    return 'noreply@minedocs.it';
}

add_filter( 'wp_mail_from', 'filter_mail_from_email' );

// Forza il content type HTML per tutte le email
add_filter('wp_mail_content_type', function() {
    return 'text/html';
});

// Aggiungi gli headers corretti per tutte le email
// add_filter('wp_mail_headers', function($headers) {
//     if (!is_array($headers)) {
//         $headers = array();
//     }
//     $headers[] = 'Content-Type: text/html; charset=UTF-8';
//     return $headers;
// });

// Disabilita la pagina di reset password predefinita di WordPress
function disable_default_password_reset() {
    if (isset($_GET['action']) && $_GET['action'] === 'lostpassword') {
        wp_redirect(home_url('/reset-password'));
        exit();
    }
}
add_action('init', 'disable_default_password_reset');


// add_action('init', 'customize_logout');

// function customize_logout() {
//     if (isset($_GET['action']) && $_GET['action'] === 'logout') {
//         wp_redirect(home_url('/logout'));
//         exit();
//     }
// }

// Disabilita l'accesso alla pagina mio-account predefinita di WordPress
function disable_default_my_account() {
    if (is_page('mio-account')) {
        wp_redirect(home_url('/profilo-utente'));
        exit();
    }
}
add_action('template_redirect', 'disable_default_my_account');




// Carica i template email
// require_once get_stylesheet_directory() . '/inc/email-templates/load-email-templates.php';
require_once get_stylesheet_directory() . '/inc/email-templates/wordpress-email-templates.php';
require_once get_stylesheet_directory() . '/inc/email-templates/woocommerce-email-templates.php';

// Includi il gestore degli errori
require_once get_stylesheet_directory() . '/inc/error-handler.php';

// Gestione errori fatali tramite email agli amministratori
add_action('init', function () {
    register_shutdown_function('handle_critical_error');
});


// Disabilita la notifica inviata all'utente quando cambia l'email
add_filter( 'send_email_change_email', '__return_false' );

?>