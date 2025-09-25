<?php


class Transaction {
    private $id;
    private $user_id;
    private $amount;
    private $points;
    private $points_type;
    private $currency;
    private $description;
    private $order_id;
    private $transaction_type;
    private $status;
    private $created_at;
    private $blu_points_balance;
    private $pro_points_balance;
    private $sales_balance;
    private $direction;
    private $internal_note;
    private $external_note;
    private $hidden_to_user;
    private $invoiceable;

    //private $approve_transaction_token;

    public function __construct($args = []) {

        

        $this->id = $args['id'] ?? null;
        $this->user_id = $args['user_id'] ?? null;
        $this->amount = $args['amount'] ?? null;
        $this->points = $args['points'] ?? null;
        $this->points_type = $args['points_type'] ?? null;
        $this->currency = $args['currency'] ?? 'euro';
        $this->description = $args['description'] ?? '';
        $this->order_id = $args['order_id'] ?? null;
        $this->transaction_type = $args['transaction_type'] ?? 'generic';
        $this->status = $args['status'] ?? 'pending_approval';
        $this->created_at = $args['created_at'] ?? current_time('mysql');
        $this->blu_points_balance = $args['blu_points_balance'] ?? get_sistema_punti('blu')->ottieni_totale_punti($this->user_id);
        $this->pro_points_balance = $args['pro_points_balance'] ?? get_sistema_punti('pro')->ottieni_totale_punti($this->user_id);
        //$this->sales_balance = $args['sales_balance'] ?? get_user_meta($args['user_id'], 'saldo_utente', true);
        $this->sales_balance = $args['sales_balance'] ?? ottieni_totale_prelevabile($args['user_id']);
        $this->internal_note = $args['internal_note'] ?? null;
        $this->external_note = $args['external_note'] ?? null;
        $this->hidden_to_user = $args['hidden_to_user'] ?? false;
        $this->direction = $args['direction'] ?? null;
        $this->invoiceable = $args['invoiceable'] ?? false;

        //$this->approve_transaction_token = $args['approve_transaction_token'] ?? null;
        
    }

    public static function insert($data) {
        global $wpdb;
        $table_name = TABELLA_TRANSAZIONI;

        /*if($data['status'] == 'approved') {
            $data['approve_transaction_token'] = null;
        } else {
            $data['approve_transaction_token'] = wp_generate_password(32, false);
        }*/


        $wpdb->insert($table_name, [
            'user_id' => $data['user_id'],
            'amount' => $data['amount'] ?? null,
            'points' => $data['points'] ?? null,
            'points_type' => $data['points_type'] ?? null,
            'currency' => $data['currency'] ?? 'euro',
            'description' => $data['description'],
            'order_id' => $data['order_id'] ?? null,
            'transaction_type' => $data['transaction_type'],
            'status' => $data['status'],
            'created_at' => $data['created_at'] ?? current_time('mysql'),
            'blu_points_balance' => $data['blu_points_balance'] ?? get_sistema_punti('blu')->ottieni_totale_punti($data['user_id']),
            'pro_points_balance' => $data['pro_points_balance'] ?? get_sistema_punti('pro')->ottieni_totale_punti($data['user_id']),
            'sales_balance' => $data['sales_balance'] ?? ottieni_totale_prelevabile($data['user_id']),
            'direction' => $data['direction'],
            'internal_note' => $data['internal_note'] ?? null,
            'external_note' => $data['external_note'] ?? null,
            'hidden_to_user' => $data['hidden_to_user'] ?? false,
            'invoiceable' => $data['invoiceable'] ?? false,
            //'approve_transaction_token' => $data['approve_transaction_token'] ?? null

        ]);

        if($data['status'] == 'pending_approval') {
            do_action('transazione_in_attesa_approvazione', $wpdb->insert_id);
        }

        return $wpdb->insert_id;
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getPoints() {
        return $this->points;
    }

    public function getPointsType() {
        return $this->points_type;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getOrderId() {
        return $this->order_id;
    }

    public function getTransactionType() {
        return $this->transaction_type;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getBluPointsBalance() {
        return $this->blu_points_balance;
    }

    public function getProPointsBalance() {
        return $this->pro_points_balance;
    }

    public function getSalesBalance() {
        return $this->sales_balance;
    }

    public function getDirection() {
        return $this->direction;
    }

    public function getInternalNote() {
        return $this->internal_note;
    }

    public function getExternalNote() {
        return $this->external_note;
    }

    public function isHiddenToUser() {
        return $this->hidden_to_user;
    }

    public function isInvoiceable() {
        return $this->invoiceable;
    }



    public static function get_by_id($id) {
        global $wpdb;
        $table_name = TABELLA_TRANSAZIONI;

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        return $row ? new self($row) : null;
    }

    public static function get_all($args = []) {
        global $wpdb;
        $table_name = TABELLA_TRANSAZIONI;

        $query = "SELECT * FROM $table_name";
        $where = [];

        error_log('Args: ' . print_r($args, true));

        if (!empty($args['user_id'])) {
            $where[] = $wpdb->prepare("user_id = %d", $args['user_id']);
        }

        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }

        if (!empty($args['currency'])) {
            $where[] = $wpdb->prepare("currency = %s", $args['currency']);
        }


        if(isset($args['hidden_to_user'])) {
            $where[] = $wpdb->prepare("hidden_to_user = %d", $args['hidden_to_user'] ? 1 : 0);
        }

        if(isset($args['invoiceable'])) {
            $where[] = $wpdb->prepare("invoiceable = %d", $args['invoiceable'] ? 1 : 0);
        }

        error_log('Where: ' . print_r($where, true));
        if ($where) {
            $query .= " WHERE " . implode(' AND ', $where);
        }

        $query .= " ORDER BY created_at DESC";

        $results = $wpdb->get_results($query, ARRAY_A);
        return array_map(fn($row) => new self($row), $results);
    }

    public static function update_status($id, $new_status) {
        global $wpdb;
        $table_name = TABELLA_TRANSAZIONI;

        return $wpdb->update($table_name, ['status' => $new_status], ['id' => $id]);
    }

    public static function delete($id) {
        global $wpdb;
        $table_name = TABELLA_TRANSAZIONI;

        return $wpdb->delete($table_name, ['id' => $id]);
    }

    public static function get_transactions_for_view($args = []) {
        $transactions = self::get_all($args);
        $records = array_map(fn($transaction) => $transaction->to_view(), $transactions);
        // return $records;
        $grouped_records = [];

        foreach ($records as $record) {
            $order_id = $record['ordine'];
            if ($order_id) {
            if (!isset($grouped_records[$order_id])) {
                $grouped_records[$order_id] = [];
            }
            $grouped_records[$order_id][] = $record;
            } else {
            $grouped_records[] = [$record];
            }
        }
        //error_log('Grouped Records: ' . print_r($grouped_records, true));

        $records = array_map(fn($transactions) => self::aggregate_transactions($transactions), $grouped_records);
        //error_log('Records: ' . print_r($records, true));
        //$records = self::order_transactions_by_date($records);
        return $records;
        //return $grouped_records;

    }

    public static function order_transactions_by_date($transactions) {
        usort($transactions, fn($a, $b) => strtotime($a['data']) - strtotime($b['data']));
        return $transactions;
    }

    public static function aggregate_transactions($transactions) {
        $aggregated = [
            'data' => '',
            'tipo' => '',
            'descrizione' => '',
            'saldo_blu' => '',
            'saldo_pro' => '',
            'saldo_vendite' => '',
            'ordine' => '',
            'link_ordine' => '',
            'entrata' => '',
            'uscita' => ''
        ];

        $aggregated['data'] = $transactions[0]['data'];

        $aggregated['tipo'] = implode(' - ', array_unique(array_map(fn($transaction) => $transaction['tipo'], $transactions)));

        $aggregated['descrizione'] = implode(' - ', array_unique(array_map(fn($transaction) => $transaction['descrizione'], $transactions)));

        $aggregated['saldo_blu'] = end($transactions)['saldo_blu'];

        $aggregated['saldo_pro'] = end($transactions)['saldo_pro'];

        $aggregated['saldo_vendite'] = end($transactions)['saldo_vendite'];

        $aggregated['ordine'] = end($transactions)['ordine'];

        $aggregated['link_ordine'] = end($transactions)['link_ordine'];

        $aggregated['entrata'] = implode(' - ', array_filter(array_map(fn($transaction) => $transaction['entrata'], $transactions)));
        $aggregated['uscita'] = implode(' - ', array_filter(array_map(fn($transaction) => $transaction['uscita'], $transactions)));

        return $aggregated;

    }

    public function to_view() {
        $record = [
            'data' => date('d/m/Y H:i:s', strtotime($this->getCreatedAt())),
            'tipo' => $this->getTransactionType(),
            'descrizione' => $this->getDescription(),
            'saldo_blu' => $this->getBluPointsBalance() . ' Punti Blu',
            'saldo_pro' => $this->getProPointsBalance() . ' Punti Pro',
            'saldo_vendite' => '€ ' . $this->getSalesBalance(),
            'ordine' => $this->getOrderId(),
            'link_ordine' => $this->getOrderId() ? wc_get_endpoint_url('view-order', $this->getOrderId(), wc_get_page_permalink('myaccount')) : ''
        ];

        $valore = '';

        if($this->getCurrency()=='points'){
            if($this->getPointsType()=='pro') {
                $valore =  $this->getPoints() . ' Punti Pro';
            } else {
                $valore =  $this->getPoints() . ' Punti Blu';
            }
        } else {
            if($this->getAmount() == 0) {
                $valore = '';
            } else {
                $valore = '€ ' . $this->getAmount();
            }

        }

        if($this->getDirection() == 'entrata') {
            $record['entrata'] = $valore;
            $record['uscita'] = '';
        } else {
            $record['entrata'] = '';
            $record['uscita'] = $valore;
        }

        return $record;
    }
}

