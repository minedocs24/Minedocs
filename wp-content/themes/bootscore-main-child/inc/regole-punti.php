

<?php


// Hook into the updated_post_meta action to check for approval status change
add_action('updated_post_meta', 'cambiamento_stato_approvazione_prodotto', 10, 4);

function aggiungi_punti_blu_approvazione_prodotto($post_ID, $post, $update) {

    error_log('aggiungi_punti_blu_approvazione_prodotto');

    //TODO: Inserire filtro per far in modo che i punti vengono attribuiti solo se il documento è in condivisione e non in vendita

    $versione = get_numero_versione($post_ID);

    if ($versione > 1) {
        $punti_approvazione_prodotto = PUNTI_BLU_AGGIORNAMENTO_DOCUMENTO_CONDIVISO;
        $descrizione = 'Aggiornamento documento';
    } else {

        $punti_approvazione_prodotto = PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO;
        $descrizione = 'Caricamento documento';
    }

    


    // Check if the post type is 'product'
    if ($post->post_type != 'product') {
        return;
    }

    

    // Get the approval status meta data
    $approval_status = get_post_meta($post_ID, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true);

    // Check if the approval status is 'approved'
    if ($approval_status == 'approvato') {

        global $sistemiPunti;

        // Get the author ID
        $author_id = $post->post_author;

        $data = array(
            'user_id' => $author_id,
            'description' => $descrizione
        );


        // Update the author's points
        get_sistema_punti('blu')->aggiungi_punti($author_id, $punti_approvazione_prodotto, $data);
    }
}

function cambiamento_stato_approvazione_prodotto($meta_id, $post_id, $meta_key, $meta_value) {
    // Check if the meta key is 'approval_status'
    if ($meta_key == META_KEY_STATO_APPROVAZIONE_PRODOTTO) {
        // Get the post object
        $post = get_post($post_id);

        $versione = get_numero_versione($post_id);
        
        // Call the function to add points on product approval

        $stato_prodotto = get_stato_prodotto($post_id);
        if (strpos($stato_prodotto, 'eliminato') === 0 || strpos($stato_prodotto, 'nascosto') === 0 ) {
            return;
        }
   
        aggiungi_punti_blu_approvazione_prodotto($post_id, $post, false);

    }
}




// crea una funzione per aggiungere 20 punti blu all'utente quando pubblica una recensione su un documento non caricato da lui. Vale solo la prima recensione per ciascun documento.

add_action('wp_insert_comment', 'log_woocommerce_review', 10, 2);

function log_woocommerce_review($comment_ID, $comment) {

    if (PUNTI_BLU_PRIMA_RECENSIONE == 0) {
        return;
    }

    error_log("log_woocommerce_review");

    $comment_approved = $comment->comment_approved;


    // Assicurati che sia una recensione approvata
    if ($comment_approved != 1) {
        return;
    }
    error_log("comment_approved");

    // Ottieni i dati del commento
    $comment = get_comment($comment_ID);

    // Controlla se il commento è relativo a un prodotto WooCommerce
    if (get_post_type($comment->comment_post_ID) !== 'product') {
        return;
    }

    error_log("commento per prodotto");

    // Ottieni l'ID dell'utente che ha scritto il commento
    $user_id = $comment->user_id;

    // Ottieni le recensioni esistenti dell'utente per lo stesso prodotto
    $existing_reviews = get_comments([
        'post_id' => $comment->comment_post_ID,
        'user_id' => $user_id,
        'type'    => 'review',
    ]);

    // Se ci sono recensioni esistenti, esci senza fare nulla
    if (!empty($existing_reviews) && count($existing_reviews) > 1) {
        return;
    }

    // Stampa un messaggio nel debug.log
    $product = wc_get_product($comment->comment_post_ID);
    $user = get_userdata($user_id);
    $message = sprintf(
        'L\'utente %s (ID: %d) ha scritto una recensione per il prodotto "%s" (ID: %d).',
        $user ? $user->user_login : 'Utente anonimo',
        $user_id,
        $product ? $product->get_name() : 'Prodotto sconosciuto',
        $comment->comment_post_ID
    );

    $data = [
        'user_id' => $user_id, 
        'order_id' => null,
        'hidden_to_user' => false,
        'description' => 'Accredito punti blu per prima recensione su un documento',
    ];

    get_sistema_punti('blu')->aggiungi_punti($user_id, PUNTI_BLU_PRIMA_RECENSIONE, $data);


}






?>