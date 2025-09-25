<?php

function registra_commissione_vendita($product_id, $data_commissione_log) {

    do_action('commissione_vendita', $product_id, $data_commissione_log);

}



// TODO
function conversione_punti_in_denaro($costo_punti_pro) {
    $guadagno = (1 - PERCENTUALE_COMMISSIONE_VENDITA) * VALORE_PUNTI_PRO * $costo_punti_pro;
    return round($guadagno, 2); // Arrotonda al centesimo più vicino
}

function calcola_guadagno_venditore($costo_in_euro, $user_id) {
    $commissioni_disattivate = get_commissioni_disattivate($user_id);
    if ($commissioni_disattivate) {
        return 0;
    }
    return round((1-PERCENTUALE_COMMISSIONE_VENDITA) * $costo_in_euro, 2, PHP_ROUND_HALF_DOWN);
}

function calcola_commissione_vendita($costo_in_euro, $user_id) {
    $commissioni_disattivate = get_commissioni_disattivate($user_id);
    if ($commissioni_disattivate) {
        return $costo_in_euro;
    }
    return round((PERCENTUALE_COMMISSIONE_VENDITA) * $costo_in_euro, 2, PHP_ROUND_HALF_UP);
}
add_action('wp_ajax_conversione_punti_in_denaro', 'conversione_punti_in_denaro_callback');

function conversione_punti_in_denaro_callback() {
    $punti_pro = isset($_POST['punti_pro']) ? intval($_POST['punti_pro']) : 0;
    $guadagno_utente = conversione_punti_in_denaro($punti_pro);
    wp_send_json_success(array(
        'guadagno_utente' => $guadagno_utente,
    ));
}