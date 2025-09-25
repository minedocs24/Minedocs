<?php

function custom_login_failed_redirect($username) {
    $login_page = LOGIN_PAGE; // Modifica con l'URL della tua pagina di login.
    wp_redirect($login_page . '?login=failed');
    exit;
}
add_action('wp_login_failed', 'custom_login_failed_redirect');


function custom_login_not_allowed_redirect() {
    $login_page = LOGIN_PAGE; // Modifica con l'URL della tua pagina di login.

    if(isset($_GET['error']) && $_GET['error'] == 'email_not_verified') {
        return;
    }

    wp_redirect($login_page . '?login=false');
    exit;
}
add_action('wp_logout', 'custom_login_not_allowed_redirect');




function auto_redirect_after_login()
{
        wp_redirect(home_url() . "?msg=login-ok");
        exit();
    
}
add_action('wp_login', 'auto_redirect_after_login');



/*
add_action('wp_enqueue_scripts', function() {
    if(is_user_logged_in()){
        if(current_user_can('administrator')){
            return;
        }
        $user = wp_get_current_user();
        $email_verificata = get_email_verificata($user->ID);
        if(!$email_verificata){
            wp_enqueue_script('email-non-verificata', get_stylesheet_directory_uri() . '/assets/js/email_non_verificata.js', array('jquery'), null, true);
            wp_localize_script('email-non-verificata', 'env_email_non_verificata', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('email_non_verificata_nonce'),
                'logout_url' => wp_logout_url(home_url() )
            ));
        }
    }
    //wp_enqueue_script('email-non-verificata', get_stylesheet_directory_uri() . '/assets/js/email-non-verificata.js', array('jquery'), null, true);
});


*/

function resetta_tentativi_login() {
    // $_SESSION['login_attempts'] = 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    delete_transient("login_attempts_$ip");
}
