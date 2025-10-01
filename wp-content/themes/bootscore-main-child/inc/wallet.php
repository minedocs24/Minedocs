<?php
function create_wallet_table() {
    global $wpdb;
    $table_name = TABELLA_WALLET;
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        credit float NOT NULL,
        remaining_credit float NOT NULL,
        valid_from date NOT NULL,
        expiration_date date NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_wallet_table');
//add_action('init', 'create_wallet_table');

function ricarica_wallet($user_id, $credit, $expiration_date = null, $valid_from = null) {
    global $wpdb;
    $table_name = TABELLA_WALLET;

    if ($expiration_date === null) {
        $expiration_date = date('Y-m-d H:i:s', strtotime('+1 year'));
    }

    if ($valid_from === null) {
        $valid_from = current_time('mysql');
    }

    $credit = number_format((float)$credit, 2, '.', '') ;
    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'credit' => $credit,
            'remaining_credit' => $credit,
            'valid_from' => $valid_from,
            'expiration_date' => $expiration_date
        )
    );
}

function ottieni_totale_prelevabile($user_id) {
    global $wpdb;
    $table_name = TABELLA_WALLET;

    $sql = $wpdb->prepare("SELECT SUM(remaining_credit) as totale_prelevabile FROM $table_name WHERE user_id = %d AND expiration_date > CURDATE() AND remaining_credit > 0", $user_id);

    $results = $wpdb->get_results($sql);

    $prelevabile = $results[0]->totale_prelevabile ? $results[0]->totale_prelevabile : 0;
    return $prelevabile;
}

function ottieni_totale_prossimamente_prelevabile($user_id) {
    global $wpdb;
    $table_name = TABELLA_WALLET;

    $sql = $wpdb->prepare("SELECT SUM(remaining_credit) as totale_prelevabile FROM $table_name WHERE user_id = %d AND expiration_date > CURDATE() AND remaining_credit > 0 AND valid_from >= CURDATE()", $user_id);

    $results = $wpdb->get_results($sql);

    $prelevabile = $results[0]->totale_prelevabile ? $results[0]->totale_prelevabile : 0;
    return $prelevabile;
}

function ottieni_credito_totale($user_id) {
    global $wpdb;
    $table_name = TABELLA_WALLET;

    $sql = $wpdb->prepare("SELECT SUM(remaining_credit) as totale_credito FROM $table_name WHERE user_id = %d    AND remaining_credit > 0", $user_id);

    $results = $wpdb->get_results($sql);

    $totale_credito = $results[0]->totale_credito ? $results[0]->totale_credito : 0;
    return $totale_credito;
}

function preleva_crediti($user_id, $amount) {
    global $wpdb;
    $table_name = TABELLA_WALLET;

    $prelevabile = ottieni_totale_prelevabile($user_id); 

    if ($prelevabile < $amount) {
        throw new Exception("Non hai abbastanza crediti prelevabili");
    }

    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d AND expiration_date > NOW() AND remaining_credit > 0 AND valid_from <= CURDATE() order by expiration_date ASC", $user_id);

    $results = $wpdb->get_results($sql);

    $rimanente_da_dedurre = $amount;

    foreach ($results as $result) {
        error_log("Processing wallet entry ID: " . $result->id);

        error_log("Wallet entry remaining credit: " . $result->remaining_credit);

        $disponibilita_record = $result->remaining_credit;

        if($rimanente_da_dedurre > 0) {
            if ($disponibilita_record >= $rimanente_da_dedurre) {
                $wpdb->update(
                    $table_name,
                    array(
                        'remaining_credit' => $disponibilita_record - $rimanente_da_dedurre
                    ),
                    array(
                        'id' => $result->id
                    )
                );
                error_log("Updated wallet entry ID " . $result->id . " to remaining credit " . ($disponibilita_record - $rimanente_da_dedurre));
                $rimanente_da_dedurre = 0;
            } else {
                $rimanente_da_dedurre -= $disponibilita_record;
                $wpdb->update(
                    $table_name,
                    array(
                        'remaining_credit' => 0
                    ),
                    array(
                        'id' => $result->id
                    )
                );
                error_log("Updated wallet entry ID " . $result->id . " to remaining credit 0");
            }
        }

    }
}


function processa_crediti_scaduti() {
    global $wpdb;
    $table_name = TABELLA_WALLET;

    $sql = "SELECT * FROM $table_name WHERE expiration_date < CURDATE() AND remaining_credit > 0";

    $results = $wpdb->get_results($sql);

    foreach ($results as $result) {

        $wpdb->query(('START TRANSACTION'));

        try {
            $wpdb->update(
                $table_name,
                array(
                    'remaining_credit' => 0
                ),
                array(
                    'id' => $result->id
                )
            );

            $data = array(
                'user_id' => $result->user_id,
                'credit' => $result->credit,
                'remaining_credit' => $result->remaining_credit,
                'valid_from' => $result->valid_from,
                'expiration_date' => $result->expiration_date
            );

            do_action('annullamento_crediti_scaduti', $result->user_id, $data);

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }

    }
}

function azzera_crediti_residui_utente($user_id) {
    global $wpdb;
    $table_name = TABELLA_WALLET;

    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d AND remaining_credit > 0", $user_id);

    $results = $wpdb->get_results($sql);

    foreach ($results as $result) {

        $wpdb->query('START TRANSACTION');

        try {
            $wpdb->update(
                $table_name,
                array(
                    'remaining_credit' => 0
                ),
                array(
                    'id' => $result->id
                )
            );

            $data = array(
                'user_id' => $result->user_id,
                'credit' => $result->credit,
                'remaining_credit' => $result->remaining_credit,
                'valid_from' => $result->valid_from,
                'expiration_date' => $result->expiration_date
            );

            do_action('annullamento_crediti_utente', $result->user_id, $data);

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }

    }
}




function register_expired_credits_cron() {

    /*if (wp_next_scheduled('processa_crediti_scaduti')) {
        wp_clear_scheduled_hook('processa_crediti_scaduti');
    }*/

    if (!wp_next_scheduled('processa_crediti_scaduti')) {
        wp_schedule_event(strtotime('17:59:00'), 'daily', 'processa_crediti_scaduti');
    }
}

add_action('init', 'register_expired_credits_cron');

add_action('wp', 'processa_crediti_scaduti');