<?php

function minedocs_create_points_register_table() {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        points_type ENUM('blu', 'pro') DEFAULT NULL,
        purchased_points INT DEFAULT NULL,
        remaining_points INT DEFAULT NULL,
        expiring_date DATE DEFAULT NULL,
        order_id BIGINT(20) UNSIGNED DEFAULT NULL,    
        unit_price DECIMAL(10, 4) DEFAULT NULL,
        related_subscription_id VARCHAR(64) DEFAULT NULL,  
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE SET NULL
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'minedocs_create_points_register_table');
//add_action('init', 'minedocs_create_points_register_table');

function insert_points_register($user_id, $points_type, $purchased_points, $remaining_points, $expiring_date, $order_id, $related_subscription_id = null, $moltiplicatore = 1) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'points_type' => $points_type,
            'purchased_points' => $purchased_points,
            'remaining_points' => $remaining_points,
            'expiring_date' => $expiring_date,
            'order_id' => $order_id,
            'unit_price' => $points_type == 'pro' ? (VALORE_PUNTI_PRO / $moltiplicatore) : 0,
            'related_subscription_id' => $related_subscription_id
        )
    );
}

function get_points_register($user_id) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $sql = "SELECT * FROM $table_name WHERE user_id = $user_id";

    $results = $wpdb->get_results($sql);

    return $results;
}

function get_points_register_by_type($user_id, $points_type, $order_by = 'expiring_date', $order = 'ASC') {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $sql = "SELECT * FROM $table_name WHERE user_id = $user_id AND points_type = '$points_type' ORDER BY $order_by $order";

    $results = $wpdb->get_results($sql);

    return $results;
}

function get_total_remaining_points_by_type($user_id, $points_type) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    /*$subscriptions = get_lista_sottoscrizioni_attive($user_id);
    $subscription_ids = implode(',', array_map('intval', $subscriptions));
    $sql = "SELECT SUM(remaining_points) as total_remaining_points FROM $table_name WHERE user_id = $user_id AND points_type = '$points_type' AND expiring_date >= CURDATE() AND (related_subscription_id IS NULL OR related_subscription_id IN ($subscription_ids))";
    */
    $sql = "SELECT SUM(remaining_points) as total_remaining_points FROM $table_name WHERE user_id = $user_id AND points_type = '$points_type' AND expiring_date >= CURDATE()";

    $results = $wpdb->get_results($sql);
    $total_remaining_points = $results[0]->total_remaining_points;

    if (!$total_remaining_points) {
        return 0;
    }

    return $total_remaining_points;
}


/**
 * Aggiorna la data di scadenza dei punti relativi ad una sottoscrizione
 * @param mixed $subscription_id
 * @param mixed $expiring_date
 * @return void
 */
function update_expire_date_subscription($subscription_id, $expiring_date) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $wpdb->update(
        $table_name,
        array('expiring_date' => $expiring_date),
        array('related_subscription_id' => $subscription_id)
    );
}

function get_total_purchased_points_by_type($user_id, $points_type) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $sql = "SELECT SUM(purchased_points) as total_purchased_points FROM $table_name WHERE user_id = $user_id AND points_type = '$points_type'";

    $results = $wpdb->get_results($sql);
    $total_purchased_points = $results[0]->total_purchased_points;

    if (!$total_purchased_points) {
        return 0;
    }

    return $total_purchased_points;
}

function get_total_expired_points_by_type($user_id, $points_type) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $sql = "SELECT SUM(remaining_points) as total_expired_points FROM $table_name WHERE user_id = $user_id AND points_type = '$points_type' AND expiring_date < CURDATE()";

    $results = $wpdb->get_results($sql);
    $total_expired_points = $results[0]->total_expired_points;

    if (!$total_expired_points) {
        return 0;
    }

    return $total_expired_points;
}


/**
 * Summary of remove_points_register
 * @param mixed $user_id
 * @param mixed $points_type
 * @param mixed $points_to_remove
 * @throws \Exception
 * @return float|int valore in euro dei punti rimossi
 */
function remove_points_register($user_id, $points_type, $points_to_remove) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $total_remaining_points = get_total_remaining_points_by_type($user_id, $points_type);

    if ($total_remaining_points < $points_to_remove) {
        throw new Exception('Not enough points to remove');
    }

    $sql = "SELECT * FROM $table_name WHERE user_id = $user_id AND points_type = '$points_type' AND expiring_date >= CURDATE() ORDER BY expiring_date ASC";

    $results = $wpdb->get_results($sql);

    $to_remove = $points_to_remove;
    $value = 0;

    foreach ($results as $result) {
        if ($to_remove > 0) {
            if ($result->remaining_points >= $to_remove) {
                $wpdb->update(
                    $table_name,
                    array('remaining_points' => $result->remaining_points - $to_remove),
                    array('id' => $result->id)
                );
                $value+= $result->unit_price * $to_remove;
                $to_remove = 0;                
            } else {
                $to_remove -= $result->remaining_points;
                $value+= $result->unit_price * $result->remaining_points;
                $wpdb->update(
                    $table_name,
                    array('remaining_points' => 0),
                    array('id' => $result->id)
                );
               
            }
        }
    }

    return $value;
}

function update_points_expiry_to_today($user_id) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;
    $current_date = date('Y-m-d');

    $wpdb->update(
        $table_name,
        array('expiring_date' => $current_date),
        array('user_id' => $user_id)
    );
}


function get_all_expired_points() {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $sql = "SELECT * FROM $table_name WHERE expiring_date < CURDATE() AND remaining_points > 0";

    $results = $wpdb->get_results($sql);

    return $results;
}


function process_expired_points() {

    error_log('Processing expired points...');
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $expired_points = get_all_expired_points();

    foreach ($expired_points as $expired_point) {
        $wpdb->query('START TRANSACTION');

        try {
            $wpdb->update(
                $table_name,
                array('remaining_points' => 0),
                array('id' => $expired_point->id)
            );

            $data = array(
                'user_id' => $expired_point->user_id,
                'points_type' => $expired_point->points_type,
                'purchased_points' => $expired_point->purchased_points,
                'remaining_points' => $expired_point->remaining_points,
                'expiring_date' => $expired_point->expiring_date,
                'order_id' => $expired_point->order_id,
                'unit_price' => $expired_point->unit_price,
                'related_subscription_id' => $expired_point->related_subscription_id
            );
            do_action('annullamento_punti_scaduti', $expired_point->user_id, $data);

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }
}


function register_expired_points_cron() {
    /*if (wp_next_scheduled('process_expired_points_event')) {
        wp_clear_scheduled_hook('process_expired_points_event');
    }*/
    if (!wp_next_scheduled('process_expired_points_event')) {
        wp_schedule_event(strtotime('midnight'), 'daily', 'process_expired_points_event');
    }
}

add_action('process_expired_points_event', 'process_expired_points');

add_action('wp', 'register_expired_points_cron');


function log_scheduled_events() {
    $crons = _get_cron_array();
    if (!empty($crons)) {
        foreach ($crons as $timestamp => $cron) {
            foreach ($cron as $hook => $events) {
                foreach ($events as $event) {
                    error_log("Scheduled event: Hook - $hook, Timestamp - " . date('Y-m-d H:i:s', $timestamp) . ", Schedule - " . (isset($event['schedule']) ? $event['schedule'] : 'One-time'));
                }
            }
        }
    } else {
        error_log('No scheduled events found.');
    }
}

//add_action('wp', 'log_scheduled_events');

function get_points_expiring_on_date($user_id, $date) {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;

    $sql = $wpdb->prepare(
        "SELECT SUM(remaining_points) as points_expiring FROM $table_name WHERE user_id = %d AND expiring_date = %s",
        $user_id,
        $date
    );

    $results = $wpdb->get_results($sql);
    $points_expiring = $results[0]->points_expiring;

    if (!$points_expiring) {
        return 0;
    }

    return $points_expiring;
}

function send_email_to_users_with_expiring_points() {
    global $wpdb;

    $table_name = TABELLA_REGISTRO_PUNTI;
    $date_in_10_days = date('Y-m-d', strtotime('+10 days'));

    error_log('Sending email to users with expiring points');
    error_log('Date in 10 days: ' . $date_in_10_days);

    foreach (get_users() as $user) {
        error_log('User: ' . $user->user_email);
        $points_expiring = get_points_expiring_on_date($user->ID, $date_in_10_days);
        $expiring_date = $date_in_10_days;
        error_log('Points expiring: ' . $points_expiring);
        
        if ($points_expiring > 0) {
            $subject = 'I tuoi punti stanno per scadere';
            
            // Includi il template dell'email
            ob_start();
            include(get_stylesheet_directory() . '/inc/email-templates/email-expiring-points.php');
            $message = ob_get_clean();
            
            // Imposta gli header per email in formato HTML
            $headers = array('Content-Type: text/html; charset=UTF-8');

            $sent = wp_mail($user->user_email, $subject, $message, $headers);
            if ($sent) {
                error_log( 'Ti abbiamo inviato una email con le istruzioni per reimpostare la password.');
            } else {
                error_log( 'Errore nell\'invio dell\'email. Riprova più tardi.');
            }
        }
    }
}

add_action('send_email_to_users_with_expiring_points_event', 'send_email_to_users_with_expiring_points');

function register_send_email_cron() {

    // if (wp_next_scheduled('send_email_to_users_with_expiring_points_event')) {
    //     wp_clear_scheduled_hook('send_email_to_users_with_expiring_points_event');
    // }


    if (!wp_next_scheduled('send_email_to_users_with_expiring_points_event')) {
        wp_schedule_event(strtotime('00:02:00'), 'daily', 'send_email_to_users_with_expiring_points_event');
    }
}

add_action('wp', 'register_send_email_cron');



