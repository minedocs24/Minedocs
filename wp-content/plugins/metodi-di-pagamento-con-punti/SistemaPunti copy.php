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

public function get_meta_key() {
    return $this->meta_key;
}

//restituisce il nome formattato (es. Punti Pro)
public function get_name() {
    return "Punti " . ucfirst($this->name);
}

// Metodo per aggiungere punti a un utente
public function aggiungi_punti($user_id, $punti, $data=array()) {
    $punti_attuali = get_user_meta($user_id, $this->meta_key, true);
    $punti_nuovi = (int) $punti_attuali + $punti;
    if($punti_attuali == null){
        add_user_meta( $user_id, $this->meta_key, 0, true );
    }
    update_user_meta($user_id, $this->meta_key, $punti_nuovi);

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

    return $punti_nuovi;
}

// Metodo per rimuovere punti a un utente
public function rimuovi_punti($user_id, $punti, $data=array()) {
    $punti_attuali = (int) get_user_meta($user_id, $this->meta_key, true);
    if($punti > $punti_attuali){
        throw new Exception();
    }
    $punti_nuovi = max(0, $punti_attuali - $punti);  // Assicura che i punti non diventino negativi
    
    update_user_meta($user_id, $this->meta_key, $punti_nuovi);

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
    return $punti_nuovi;
}

// Metodo per ottenere il totale dei punti di un utente
public function ottieni_totale_punti($user_id) {
    return (int) get_user_meta($user_id, $this->meta_key, true);
}

// Versione AJAX per aggiungere punti
public function aggiungi_punti_ajax() {
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
    // Verifica nonce e parametri
    check_ajax_referer('sistema_punti_nonce', 'security');

    $user_id = intval($_POST['user_id']);
    $punti = intval($_POST['punti']);

    if ($user_id && $punti>0) {
        try {
        $totale_punti = $this->rimuovi_punti($user_id, $punti);
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
