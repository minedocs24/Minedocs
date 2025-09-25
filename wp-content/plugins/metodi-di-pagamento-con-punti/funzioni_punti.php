<?php





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