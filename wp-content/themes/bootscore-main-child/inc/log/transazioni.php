<?php
/**
 * Plugin Name: Minedocs Transactions
 * Description: Gestione delle transazioni utenti con supporto a euro, punti, e WooCommerce.
 * Version: 1.1
 * Author: [Il tuo nome]
 */

// Creazione della tabella
function minedocs_create_transaction_table() {
    global $wpdb;

    $table_name = TABELLA_TRANSAZIONI;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        amount DECIMAL(10, 2) DEFAULT NULL,
        points INT DEFAULT NULL,
        points_type ENUM('blu', 'pro') DEFAULT NULL,
        currency ENUM('euro', 'points') NOT NULL,
        description TEXT NOT NULL,
        order_id BIGINT(20) UNSIGNED DEFAULT NULL,
        transaction_type VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE SET NULL,
        blu_points_balance INT DEFAULT 0,
        pro_points_balance INT DEFAULT 0,
        sales_balance DECIMAL(10, 2) DEFAULT 0,
        direction ENUM('entrata', 'uscita') NOT NULL,
        internal_note TEXT DEFAULT NULL,
        external_note TEXT DEFAULT NULL,
        hidden_to_user BOOLEAN DEFAULT FALSE,
        invoiceable BOOLEAN DEFAULT FALSE,
        fattura_id INT DEFAULT NULL
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'minedocs_create_transaction_table');

//add_action( 'init', 'minedocs_create_transaction_table' );








add_action( 'punti_aggiunti', 'minedocs_registra_transazione_aggiunta_punti', 10, 4 );

function minedocs_registra_transazione_aggiunta_punti($user_id, $punti, $points_type, $data) {

    error_log('minedocs_registra_transazione_aggiunta_punti');
    
    Transaction::insert([
        'user_id' => $user_id,
        'points' => $punti,
        'points_type' => $points_type,
        'transaction_type' => $data['transaction_type'] ?? 'Punti ' + $points_type,
        'status' => $data['status'] ?? null,
        'direction' => 'entrata',
        'description' => $data['description'] ?? '',
        'order_id' => $data['order_id'] ?? null,
        'currency' => 'points',
        'hidden_to_user' => $data['hidden_to_user'] ?? false,
        //'sales_balance' => get_user_meta($user_id, 'saldo_utente', true),
        //'blu_points_balance' => get_user_meta($user_id, 'punti_blu', true),
        //'pro_points_balance' => get_user_meta($user_id, 'punti_pro', true),
    ]);

}

add_action( 'punti_rimossi', 'minedocs_registra_transazione_rimozione_punti', 10, 4 );

function minedocs_registra_transazione_rimozione_punti($user_id, $punti, $points_type, $data) {
    
    Transaction::insert([
        'user_id' => $user_id,
        'points' => $punti,
        'points_type' => $points_type,
        'transaction_type' => 'Punti ' . $points_type,
        'currency' => 'points',
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => $data['description'] ?? '',
        'order_id' => $data['order_id'] ?? null,
        'hidden_to_user' => $data['hidden_to_user'] ?? false,
    ]);

}

add_action( 'abbonamento_esteso', 'minedocs_registra_transazione_abbonamento_esteso', 10, 4 );

function minedocs_registra_transazione_abbonamento_esteso($user_id, $nuova_scadenza, $data) {
    
    error_log('Data (registrazione transazione abbonamento esteso): ' . print_r($data, true));

    Transaction::insert([
        'user_id' => $user_id,
        'points' => null,
        'points_type' => null,
        'transaction_type' => $data['transaction_type'],
        'currency' => $data['currency'] ?? 'euro',
        'status' => $data['status'] ?? 'completed',
        'direction' => $data['direction'] ?? 'uscita',
        'description' => /* $data['description'] ??  */'Abbonamento esteso fino al ' . $nuova_scadenza,
        'order_id' => $data['order_id'],
        'hidden_to_user' => $data['hidden_to_user'] ?? false,
    ]);

    error_log('Data (registrazione transazione abbonamento esteso): ' . print_r($data, true));
}

add_action( 'saldo_prelevato', 'minedocs_registra_transazione_prelevato_saldo', 10, 4 );
function minedocs_registra_transazione_prelevato_saldo($user_id, $amount, $data=array()) {

    Transaction::insert([
        'user_id' => $user_id,
        'amount' => $amount,
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Prelievo',
        'currency' => 'euro',
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Trasferimento sul conto PayPal ('. $data['paypal_email'] . ') di € ' . $amount,
        'order_id' => null,
        'hidden_to_user' => false,
    ]);

}

add_action( 'cancella_rinnovo_abbonamento', 'minedocs_registra_transazione_cancella_rinnovo_abbonamento', 10, 3 );

function minedocs_registra_transazione_cancella_rinnovo_abbonamento($user_id, $subscription_id, $data=array()) {
    
    Transaction::insert([
        'user_id' => $user_id,
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Cancella Rinnovo Abbonamento',
        'currency' => null,
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Annullato rinnovo automatico piano Pro',
        'hidden_to_user' => false,
        'internal_note' => 'ID abbonamento: ' . $subscription_id,
    ]);

}

add_action( 'sospendi_abbonamento', 'minedocs_registra_transazione_sospendi_abbonamento', 10, 3 );

function minedocs_registra_transazione_sospendi_abbonamento($user_id, $subscription_id, $data=array()) {
    
    Transaction::insert([
        'user_id' => $user_id,
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Sospensione Abbonamento',
        'currency' => null,
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Sospeso abbonamento piano Pro',
        'hidden_to_user' => false,
        'internal_note' => 'ID abbonamento: ' . $subscription_id,
    ]);

}

add_action( 'resume_abbonamento', 'minedocs_registra_transazione_resume_abbonamento', 10, 3 );

function minedocs_registra_transazione_resume_abbonamento($user_id, $subscription_id, $data=array()) {
    
    Transaction::insert([
        'user_id' => $user_id,
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Ripresa Abbonamento',
        'currency' => null,
        'status' => 'completed',
        'direction' => 'entrata',
        'description' => 'Ripreso abbonamento piano Pro',
        'hidden_to_user' => false,
        'internal_note' => 'ID abbonamento: ' . $subscription_id,
    ]);

}

add_action('prodotto_venduto', 'minedocs_registra_transazione_prodotto_venduto', 10, 3);

function minedocs_registra_transazione_prodotto_venduto($user_id, $data = array()) {

    Transaction::insert([
        'user_id' => $user_id,
        'amount' => $data['amount'],
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Vendita',
        'currency' => 'euro',
        'status' => 'completed',
        'direction' => 'entrata',
        'description' => $data['description'],
        'order_id' => $data['order_id'] ?? null,
        'hidden_to_user' => $data['hidden_to_user'] ?? false,
        'internal_note' => $data['internal_note'] ?? null,
    ]);
}

add_action('commissione_vendita', 'minedocs_registra_transazione_commissione_vendita', 10, 2);

function minedocs_registra_transazione_commissione_vendita($product_id, $data) {

    $user_id = get_post($product_id)->post_author;
    $amount = (float) $data['amount'];
    $invoiceable = true;
    if($amount == 0) {
        $invoiceable = false;
    }

    Transaction::insert([
        'user_id' => $user_id,
        'amount' => $data['amount'],
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Commissione vendita',
        'currency' => 'euro',
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => $data['description'],
        'order_id' => $data['order_id'] ?? null,
        'hidden_to_user' => $data['hidden_to_user'] ?? false,
        'internal_note' => $data['internal_note'] ?? null,
        'invoiceable' => $invoiceable
    ]);
}

add_action( 'woocommerce_order_status_completed', 'minedocs_registra_transazione_ordine_completato', 10, 1 );

function minedocs_registra_transazione_ordine_completato($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    
    Transaction::insert([
        'user_id' => $user_id,
        'amount' => $order->get_total(),
        'transaction_type' => 'Ordine effettuato',
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Ordine ' . $order_id,
        'order_id' => $order_id,
        'hidden_to_user' => false,
    ]);

}


//INSERIMENTO TRANSAZIONE PER RICARICO
add_action('trattieni_ricarico_su_punti_pro', 'inserisci_transazione_ricarico_su_punti_pro', 10, 3);

function inserisci_transazione_ricarico_su_punti_pro($user_id, $ricarico, $data) {
    Transaction::insert([
        'user_id' => $user_id,
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Commissione acquisto punti Pro',
        'currency' => 'euro',
        'amount' => $ricarico,
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Ricarico punti Pro',
        'order_id' => $data['order_id'] ?? null,
        'hidden_to_user' => true,
        'invoiceable' => true
    ]);


}

add_action('annullamento_punti_scaduti', 'transazione_annullamento_punti_scaduti', 10, 2);

function transazione_annullamento_punti_scaduti($user_id, $data) {
    $punti = $data['remaining_points'];
    $points_type = isset($data['points_type'])? $data['points_type'] : 'pro';



    Transaction::insert([
        'user_id' => $user_id,
        'points' => $punti,
        'points_type' => $points_type,
        'transaction_type' => 'Annullamento punti scaduti',
        'currency' => 'points',
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Annullamento punti scaduti',
        'hidden_to_user' => false,
        'invoiceable' => false
    ]);
}

add_action('annullamento_punti_scaduti', 'transazione_fatturazione_punti_scaduti', 10, 2);

function transazione_fatturazione_punti_scaduti($user_id, $data) {
    $punti = $data['remaining_points'];
    $unit_price = $data['unit_price'];

    $amount = $punti * $unit_price;

    $invoiceable = $data['invoiceable'] ?? $amount > 0;

    Transaction::insert([
        'user_id' => $user_id,
        'points' => $punti,
        'points_type' => 'euro',
        'transaction_type' => 'Fatturazione punti scaduti',
        'currency' => 'euro',
        'amount' => $amount,
        'status' => 'completed',    
        'direction' => 'uscita',
        'description' => 'Fatturazione punti scaduti',
        'hidden_to_user' => true,
        'invoiceable' => $invoiceable,
    ]);
}

add_action('annullamento_crediti_scaduti', 'transazione_annullamento_crediti_scaduti', 10, 2);

function transazione_annullamento_crediti_scaduti($user_id, $data) {
    $crediti = $data['remaining_credit'];

    Transaction::insert([
        'user_id' => $user_id,
        'amount' => $crediti,
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Annullamento crediti scaduti',
        'currency' => 'euro',
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Annullamento crediti scaduti',
        'hidden_to_user' => false,
        'invoiceable' => true
    ]);
}


add_action('annullamento_crediti_utente', 'transazione_annullamento_crediti_utente', 10, 2);

function transazione_annullamento_crediti_utente($user_id, $data) {
    $crediti = $data['remaining_credit'];

    Transaction::insert([
        'user_id' => $user_id,
        'amount' => $crediti,
        'points' => null,
        'points_type' => null,
        'transaction_type' => 'Annullamento crediti utente',
        'currency' => 'euro',
        'status' => 'completed',
        'direction' => 'uscita',
        'description' => 'Annullamento crediti utente',
        'hidden_to_user' => false,
        'invoiceable' => true
    ]);
}


add_action('admin_menu', 'minedocs_register_transaction_log_page');

function minedocs_register_transaction_log_page() {
    add_menu_page(
        'Transaction Logs',
        'Transaction Logs',
        'manage_options',
        'transaction-logs',
        'minedocs_transaction_log_page',
        'dashicons-list-view',
        6
    );
}

function minedocs_transaction_log_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = TABELLA_TRANSAZIONI;

    $per_page = 10;
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $offset = ($paged - 1) * $per_page;

    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $order_by = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
    $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

    $where = '';
    if ($search) {
        $where = $wpdb->prepare("WHERE description LIKE %s", '%' . $wpdb->esc_like($search) . '%');
    }

    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
    $transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name $where ORDER BY $order_by $order LIMIT %d OFFSET %d",
        $per_page, $offset
    ));

    $total_pages = ceil($total_items / $per_page);

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Transaction Logs</h1>
        <form method="get">
            <input type="hidden" name="page" value="transaction-logs" />
            <p class="search-box">
                <label class="screen-reader-text" for="transaction-search-input">Search Transactions:</label>
                <input type="search" id="transaction-search-input" name="s" value="<?php echo esc_attr($search); ?>" />
                <input type="submit" id="search-submit" class="button" value="Search Transactions" />
            </p>
        </form>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id">ID</th>
                    <th scope="col" class="manage-column column-user_id">User ID</th>
                    <th scope="col" class="manage-column column-amount">Amount</th>
                    <th scope="col" class="manage-column column-points">Points</th>
                    <th scope="col" class="manage-column column-points_type">Points Type</th>
                    <th scope="col" class="manage-column column-currency">Currency</th>
                    <th scope="col" class="manage-column column-description">Description</th>
                    <th scope="col" class="manage-column column-order_id">Order ID</th>
                    <th scope="col" class="manage-column column-transaction_type">Transaction Type</th>
                    <th scope="col" class="manage-column column-status">Status</th>
                    <th scope="col" class="manage-column column-created_at">Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($transactions) : ?>
                    <?php foreach ($transactions as $transaction) : ?>
                        <tr>
                            <td><?php echo esc_html($transaction->id); ?></td>
                            <td><?php echo esc_html($transaction->user_id); ?></td>
                            <td><?php echo esc_html($transaction->amount); ?></td>
                            <td><?php echo esc_html($transaction->points); ?></td>
                            <td><?php echo esc_html($transaction->points_type); ?></td>
                            <td><?php echo esc_html($transaction->currency); ?></td>
                            <td><?php echo esc_html($transaction->description); ?></td>
                            <td><?php echo esc_html($transaction->order_id); ?></td>
                            <td><?php echo esc_html($transaction->transaction_type); ?></td>
                            <td><?php echo esc_html($transaction->status); ?></td>
                            <td><?php echo esc_html($transaction->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="11">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        $pagination_args = [
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'per_page' => $per_page,
            'current' => $paged,
            'base' => add_query_arg('paged', '%#%'),
            'format' => '?paged=%#%',
        ];
        echo paginate_links($pagination_args);
        ?>
    </div>
    <?php
}


add_action('transazione_in_attesa_approvazione', 'invia_email_admin_richiesta_approvazione_transazione', 10, 1);
/*
function invia_email_admin_richiesta_approvazione_transazione($transaction_id) {
    $transaction = Transaction::find($transaction_id);
    $user = get_user_by('id', $transaction->user_id);
    $user_email = $user->user_email;
    $subject = 'Richiesta di approvazione transazione';
    $message = 'Ciao, ' . "\n\n";
    $message .= 'E\' stata richiesta l\'approvazione della seguente transazione:' . "\n\n";
    $message .= 'ID: ' . $transaction->id . "\n";
    $message .= 'User ID: ' . $transaction->user_id . "\n";
    $message .= 'Amount: ' . $transaction->amount . "\n";
    $message .= 'Points: ' . $transaction->points . "\n";
    $message .= 'Points Type: ' . $transaction->points_type . "\n";
    $message .= 'Currency: ' . $transaction->currency . "\n";
    $message .= 'Description: ' . $transaction->description . "\n";
    $message .= 'Order ID: ' . $transaction->order_id . "\n";
    $message .= 'Transaction Type: ' . $transaction->transaction_type . "\n";
    $message .= 'Status: ' . $transaction->status . "\n";
    $message .= 'Created At: ' . $transaction->created_at . "\n\n";
    $message .= '*/