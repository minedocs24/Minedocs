<?php
/**
 * Plugin Name: Login Logs
 * Description: Log all login attempts
 * Version: 1.0
 * Author: [Il tuo nome]
 */

// Creazione della tabella
function minedocs_create_log_login_table() {
    error_log('minedocs_create_log_login_table');
    global $wpdb;

    $table_name = TABELLA_LOGIN_LOGS;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED DEFAULT NULL, -- NULL per login falliti
        user_login VARCHAR(100) NOT NULL, -- Salva il nome utente/email anche se il login fallisce
        ip_address VARCHAR(50) NOT NULL,
        user_agent TEXT DEFAULT NULL,
        attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        success TINYINT(1) NOT NULL, -- 1 = login riuscito, 0 = fallito
        reason VARCHAR(255) DEFAULT NULL, -- Motivo del fallimento (es. password errata)
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'minedocs_create_log_login_table');

//add_action( 'init', 'minedocs_create_log_login_table' );

function log_login_attempt($user_login, $ip_address, $user_agent, $success, $reason = null) {
    global $wpdb;

    $table_name = TABELLA_LOGIN_LOGS;

    $wpdb->insert(
        $table_name,
        array(
            'user_login' => $user_login,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'success' => $success,
            'reason' => $reason,
        )
    );
}

// Verifica che l'utente non sia bloccato ancor prima di autenticarsi
function verifica_utente_bloccato($user, $username, $password) {
    error_log('verifica_utente_bloccato');
    if (is_wp_error($user) || !$user) {
        error_log('verifica_utente_bloccato is_wp_error($user) || !$user');
        return $user; // Se c'è già un errore, non fare nulla
    }

    $blocked_until = get_user_meta($user->ID, 'login_block_until', true);
    error_log('verifica_utente_bloccato blocked_until: ' . $blocked_until);
    if ($blocked_until && strtotime($blocked_until) > time()) {
        error_log('verifica_utente_bloccato blocked');
        return new WP_Error('blocked', 'Troppi tentativi falliti. Sei bloccato fino al ' . $blocked_until);
    }
    error_log('verifica_utente_bloccato not blocked');
    return $user;
}

add_filter('effettua_verifiche_utente', 'verifica_utente_bloccato' , 23, 3);

// Registra i tentativi di login falliti e blocca l'utente se necessario
add_action('wp_login_failed', 'registra_tentativo_login_fallito', 10, 1);

function registra_tentativo_login_fallito($username) {
    error_log('wp_login_failed');

    // if (!isset($_SESSION['login_attempts'])) {
    //     $_SESSION['login_attempts'] = 0;
    // }
    // $_SESSION['login_attempts']++;

    global $wpdb;

    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);

    // Recupera il numero di tentativi falliti dal transient
    $login_attempts = get_transient("login_attempts_$ip") ?: 0;
    $login_attempts++;
    error_log('Registra tenativo login fallito: login_attempts: ' . $login_attempts);
    // Aggiorna il transient con il nuovo numero di tentativi
    set_transient("login_attempts_$ip", $login_attempts, 60 * MINUTE_IN_SECONDS); // Scadenza di 60 minuti


    // Registriamo il tentativo fallito nella tabella
    log_login_attempt($username, $ip, $user_agent, 0);

    // Recuperiamo l'utente
    $user = get_user_by('login', $username);
    if (!$user) return;


    // Contiamo i tentativi falliti
    $failed_attempts = get_failed_login_attempts($username);

    error_log('failed_attempts: ' . $failed_attempts);

    // Recuperiamo lo stato attuale del blocco
    $stage = get_user_meta($user->ID, 'login_failure_stage', true) ?: 0;

    // Controlliamo se il blocco deve essere attivato
    if ($failed_attempts >= ($stage + 1) * USER_MAX_FAILED_LOGIN_ATTEMPTS) {
        $block_duration = match ($stage) {
            0 => USER_BLOCK_STAGE_1,  // 15 minuti
            1 => USER_BLOCK_STAGE_2,  // 1 ora
            default => USER_BLOCK_STAGE_3  // Permanente
        };

        update_user_meta($user->ID, 'login_block_until', date('Y-m-d H:i:s', time() + $block_duration));
        update_user_meta($user->ID, 'login_failure_stage', $stage + 1);
    }
}

function get_login_attempts($ip) {
    return get_transient("login_attempts_$ip") ?: 0;
}

function get_failed_login_attempts($username) {
    global $wpdb;

    $table_name = TABELLA_LOGIN_LOGS;

        // Recuperiamo l'ultimo login riuscito
        $last_successful_login = $wpdb->get_var($wpdb->prepare("
        SELECT attempt_time FROM " . TABELLA_LOGIN_LOGS .
        " WHERE user_login = %s AND success = 1 
        ORDER BY attempt_time DESC LIMIT 1
    ", $username));
    error_log('last_successful_login: ' . $last_successful_login);
    // Contiamo i tentativi falliti dopo l'ultimo login riuscito
    if ($last_successful_login) {
        $failed_attempts = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM " . TABELLA_LOGIN_LOGS .  
            " WHERE user_login = %s AND success = 0 
            AND attempt_time > %s
        ", $username, $last_successful_login));
    } else {
        // Se non c'è mai stato un login riuscito, consideriamo tutti i fallimenti
        $failed_attempts = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM " . TABELLA_LOGIN_LOGS . 
            " WHERE user_login = %s AND success = 0
        ", $username));
    }

    return $failed_attempts;
}

add_action('wp_login', function($user_login, $user) {
    // error_log('wp_login');
    global $wpdb;

    // Rimuoviamo il blocco
    delete_user_meta($user->ID, 'login_block_until');
    delete_user_meta($user->ID, 'login_failure_stage');

    // Registriamo il login riuscito
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    log_login_attempt($user_login, $ip, $user_agent, 1);

}, 10, 2);

function generate_and_send_otp($user) {
    $otp = rand(100000, 999999); // Genera un OTP a 6 cifre
    update_user_meta($user->ID, 'login_otp', $otp);
    update_user_meta($user->ID, 'login_otp_expires', time() + 300); // OTP valido per 5 minuti

    $to = $user->user_email;
    $subject = 'Your OTP Code';
    $message = 'Your OTP code is: ' . $otp;
    $to = 'wordpress@localhost.com';
    wp_mail($to, $subject, $message);
}

function verify_otp($user_id, $otp) {
    $stored_otp = get_user_meta($user_id, 'login_otp', true);
    $otp_expires = get_user_meta($user_id, 'login_otp_expires', true);

    if ($stored_otp && $otp_expires && time() < $otp_expires && $stored_otp == $otp) {
        delete_user_meta($user_id, 'login_otp');
        delete_user_meta($user_id, 'login_otp_expires');
        return true;
    }

    return false;
}

// add_action('wp_login', function($user_login, $user) {
//     error_log('wp_login_otp');
//     if (in_array('administrator', $user->roles)) {
//         error_log('wp_login_otp');
//         generate_and_send_otp($user);
//         wp_logout();
//         wp_redirect(add_query_arg('otp_required', '1', wp_login_url()));
//         exit;
//     }
// }, 20, 2);

// add_action('login_form', function() {
//     error_log('login_form_otp');
//     if (isset($_GET['otp_required'])) {
//         echo '<p>Enter the OTP sent to your email:</p>';
//         echo '<input type="text" name="otp" />';
//     }
// });

// add_filter('authenticate', function($user, $username, $password) {
//     error_log('authenticate_otp');
//     if (isset($_POST['otp'])) {
//         $otp = sanitize_text_field($_POST['otp']);
//         if ($user && in_array('administrator', $user->roles) && !verify_otp($user->ID, $otp)) {
//             return new WP_Error('invalid_otp', 'Invalid OTP');
//         }
//     }
//     return $user;
// }, 30, 3);
