<?php

class SistemaPunti {

private $meta_key;
private $name;
private $path_icon = '';
private $print_icon = '';

public function __construct($nome) {
    $this->meta_key = 'punti_' . $nome;
    $this->name = $nome;

    // Registra le azioni AJAX
    add_action('wp_ajax_aggiungi_punti_'. $nome, [$this, 'aggiungi_punti_ajax']);
    add_action('wp_ajax_rimuovi_punti_' . $nome, [$this, 'rimuovi_punti_ajax']);
    add_action('wp_ajax_ottieni_totale_punti_' . $nome, [$this, 'ottieni_totale_punti_ajax']);
}

public function set_icon($path_icon) {
    $this->path_icon = $path_icon;
    if($path_icon != null){
        $this->print_icon = '<img src="'.$path_icon.'" class="icon-feature" style="max-width:25px; max-height:25px;">';
    } else {
        $this->print_icon  = ' ';
    }
}

public function get_icon(){
    return $this->path_icon;
}

public function print_icon(){
 return $this->print_icon;
}

public function set_name($name) {
    $this->name = $name;
}

public function get_name_unformatted() {
    return $this->name;
}

public function get_meta_key() {
    return $this->meta_key;
}

//restituisce il nome formattato (es. Punti Pro)
public function get_name() {
    return "Punti " . ucfirst($this->name);
}

// Metodo per aggiungere punti a un utente
public function aggiungi_punti($user_id, $punti, $data=array()) {
    

    if(isset($data['expiring_date'])){
        $expiring_date = $data['expiring_date'];
    } else {
        if ($this->name == 'pro') {
            $expiring_date = date('Y-m-d', strtotime('+1 year'));
        } else {//punti blu
            $expiring_date = BLU_POINTS_EXPIRING_DATE;
        }
    }

    if(isset($data['order_id'])){
        $order_id = $data['order_id'];
    } else {
        $order_id = null;
    }

    if(isset($data['related_subscription_id'])){
        $related_subscription_id = $data['related_subscription_id'];
    } else {
        $related_subscription_id = null;
    }

    if(isset($data['moltiplicatore'])){
        $moltiplicatore = floatval($data['moltiplicatore']);
    } else {
        $moltiplicatore = 1;
    }

    insert_points_register($user_id, $this->name, $punti, $punti, $expiring_date, $order_id, $related_subscription_id, $moltiplicatore);


    $data_log = [
        'user_id' => $user_id,
        'points' => $punti,
        'points_type' => $this->name,
        'currency' => 'points',
        'transaction_type' => 'Punti ' . $this->name,
        'status' => 'completed',
        'direction' => 'entrata',
    ];

    $data = array_merge($data_log, $data);

    do_action( 'punti_aggiunti', $user_id, $punti, $this->name, $data);

    $punti_nuovi = get_total_remaining_points_by_type($user_id, $this->name);

    return $punti_nuovi;

}

public function rimuovi_punti($user_id, $punti, $data=array()) {
    
    $valore_in_euro = remove_points_register($user_id, $this->name, $punti);

    $data_log = [
        'user_id' => $user_id,
        'points' => $punti,
        'points_type' => $this->name,
        'currency' => 'points',
        'transaction_type' => 'Punti ' . $this->name,
        'status' => 'completed',
        'direction' => 'uscita'
    ];

    $data = array_merge($data_log, $data);

    do_action( 'punti_rimossi', $user_id, $punti, $this->name, $data);

    $punti_nuovi = get_total_remaining_points_by_type($user_id, $this->name);

    return array('punti_nuovi' => $punti_nuovi, 'valore_in_euro' => $valore_in_euro);
}

// Metodo per ottenere il totale dei punti di un utente
public function ottieni_totale_punti($user_id) {
    //return (int) get_user_meta($user_id, $this->meta_key, true);
    $totale_punti = get_total_remaining_points_by_type($user_id, $this->name);
    return $totale_punti;
}

// Versione AJAX per aggiungere punti
public function aggiungi_punti_ajax() {

    if (!is_user_logged_in()) {
        wp_send_json_error('Utente non loggato');
        return;
    }

    if (!current_user_can('administrator')) {
        wp_send_json_error('Accesso negato. Solo gli amministratori possono eseguire questa azione.');
        return;
    }

    // Verifica nonce e parametri
    check_ajax_referer('sistema_punti_nonce', 'security');

    $user_id = intval($_POST['user_id']);
    $punti = intval($_POST['punti']);

    if ($user_id && $punti>0) {
        $totale_punti = $this->aggiungi_punti($user_id, $punti);
        $data = array(
            'user_id' => $user_id,
            'punti_rimossi' => $punti,
            'punti_totali' => $totale_punti,
            'meta_key' => $this->get_meta_key(),
            'tipo_punti' => $this->get_name(),
            'messaggio' => "Punti aggiunti con successo. Nuovo totale: {$totale_punti} {$this->get_name()}" 
        );
        wp_send_json_success($data);
    } else {
        wp_send_json_error('Dati non validi');
    }
}

// Versione AJAX per rimuovere punti
public function rimuovi_punti_ajax() {

    if (!is_user_logged_in()) {
        wp_send_json_error('Utente non loggato');
        return;
    }

    if (!current_user_can('administrator')) {
        wp_send_json_error('Accesso negato. Solo gli amministratori possono eseguire questa azione.');
        return;
    }

    // Verifica nonce e parametri
    check_ajax_referer('sistema_punti_nonce', 'security');

    $user_id = intval($_POST['user_id']);
    $punti = intval($_POST['punti']);

    if ($user_id && $punti>0) {
        try {
        $risultato_rimozione_punti = $this->rimuovi_punti($user_id, $punti);
        $totale_punti = $risultato_rimozione_punti['punti_nuovi'];
        //$valore_in_euro = $risultato_rimozione_punti['valore_in_euro'];
        $data = array(
            'user_id' => $user_id,
            'punti_rimossi' => $punti,
            'punti_totali' => $totale_punti,
            'meta_key' => $this->get_meta_key(),
            'tipo_punti' => $this->get_name(),
            'messaggio' => "Punti rimossi con successo. Nuovo totale: {$totale_punti} {$this->get_name()}" 
        );
        wp_send_json_success($data);
        } catch (Exception $e) {
            wp_send_json_error( 'Punti non sufficienti' );
        }
    } else {
        wp_send_json_error('Dati non validi');
    }
}

// Versione AJAX per ottenere il totale dei punti di un utente
public function ottieni_totale_punti_ajax() {

    if (!is_user_logged_in()) {
        wp_send_json_error('Utente non loggato');
        return;
    }

    if (!current_user_can('administrator')) {
        wp_send_json_error('Accesso negato. Solo gli amministratori possono eseguire questa azione.');
        return;
    }

    // Verifica nonce e parametri
    check_ajax_referer('sistema_punti_nonce', 'security');

    $user_id = intval($_POST['user_id']);

    if ($user_id) {
        $totale_punti = $this->ottieni_totale_punti($user_id);
        wp_send_json_success($totale_punti);
    } else {
        wp_send_json_error('Dati non validi');
    }
}
}
