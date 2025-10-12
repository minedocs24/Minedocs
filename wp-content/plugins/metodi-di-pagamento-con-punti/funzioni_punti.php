<?php

// Recupera i punti pro dell'utente corrente

function get_points_pro_utente($user_id) {
    global $sistemiPunti;
    return $sistemiPunti['pro']->ottieni_totale_punti($user_id);
}

function get_points_blu_utente($user_id) {
    global $sistemiPunti;
    return $sistemiPunti['blu']->ottieni_totale_punti($user_id);
}


function get_updated_points() {
    global $sistemiPunti;

    if(!is_user_logged_in(  )){
        //wp_send_json_error(array('message' => "Non hai effettuato l'accesso."));
        wp_die();
    }

    $response = array();

    foreach($sistemiPunti as $sistema){
        $response[$sistema->get_meta_key()]=$sistema->ottieni_totale_punti(get_current_user_id());
    }

    wp_send_json_success( $response );

}


add_action('wp_ajax_get_updated_points', 'get_updated_points');
add_action('wp_ajax_nopriv_get_updated_points', 'get_updated_points'); // Per utenti non loggati