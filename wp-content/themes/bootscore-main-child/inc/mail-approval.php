<?php

function send_email_approval_product($product_id){

    // genera un token di 32 caratteri
    $token = bin2hex(random_bytes(32));

    // salva il token nel database come meta del prodotto
    update_post_meta($product_id, 'approval_token', $token);

    // ottieni l'indirizzo email di tutti gli utenti con il ruolo di amministratore
    $admin_email = get_users(array(
        'role' => 'administrator',
        'fields' => array('user_email')
    ));

    // trasforma l'array di oggetti in una stringa contenente solo gli indirizzi email separati da punto e virgola
    // $admin_email = implode(';', array_column($admin_email, 'user_email'));

    // ottieni il prodotto
    $product = get_post($product_id);

    // costruisci il link di approvazione
    $approval_link = add_query_arg(array(
        'action' => 'approve_product',
        'product_id' => $product_id,
        'token' => $token
    ), home_url());

    // costruisci il link di rifiuto
    $reject_link = add_query_arg(array(
        'action' => 'reject_product',
        'product_id' => $product_id,
        'token' => $token
    ), home_url());

    // costruisci il contenuto dell'email
    $subject = 'Approva il prodotto: ' . $product->post_title;
    $message = get_approve_product_email_html($product, $approval_link, $reject_link);

    // aggiungi header per email in formato HTML
    $headers = array('Content-Type: text/html; charset=UTF-8');

    //TODO: eliminare
    //wp_mail('frankheat03@gmail.com', $subject, $message, $headers);

    // invia l'email ad ogni amministratore
    foreach ($admin_email as $admin) {
        error_log("Invio email a: ".$admin->user_email);
        wp_mail($admin->user_email, $subject, $message, $headers);
    }


}

function approve_product($product_id, $token){
    // ottieni il token salvato come meta del prodotto
    $saved_token = get_post_meta($product_id, 'approval_token', true);

    // verifica che il token passato corrisponda a quello salvato
    if ($saved_token === $token) {

        // ottieni il prodotto Woocommerce
        $product = wc_get_product($product_id);
        
        $result = check_product_data($product);
        $terms_with_draft_status = $result['terms_with_draft_status'];
        $missing_taxonomies = $result['missing_taxonomies'];
        error_log("Termini associati al prodotto: ".print_r($terms_with_draft_status, true));
        if (!empty($terms_with_draft_status || !empty($missing_taxonomies))) {
            $error = '<h2>Errore di approvazione</h2>';
            if (!empty($missing_taxonomies)) {
                $error .= '<p>Attenzione! Il prodotto non ha termini assegnati per le seguenti tassonomie:<br></p>';
                $error .= '<ul>';
                foreach ($missing_taxonomies as $taxonomy) {
                    $error .= '<li><strong>Tassonomia:</strong> ' . esc_html($taxonomy) . '</li>';
                }
                $error .= '</ul>';
                $error .= '<p>I termini associati alle tassonomie sopra potrebbero esser già stati rifiutati da un amministratore. <br>Per favore, assicurati che i termini siano correttamente associati al prodotto.</p>';
            }
            // mostra i termini associati al prodotto che sono in stato "draft"
            $error .= '<p>Il prodotto non può essere approvato perché i seguenti termini associati sono in stato "draft":</p>';
            $error .= '<ul>';
            foreach ($terms_with_draft_status as $term_data) {
                $error .= '<li><strong>Termine:</strong> ' . $term_data['name'] . ' (ID: ' . $term_data['term_id'] . ') - <strong>Tassonomia:</strong> ' . $term_data['taxonomy'] . '</li>';
            }
            $error .= '</ul>';
            $error .= '<p>Per favore, approva i termini prima di procedere con l\'approvazione del prodotto.</p>';

            // Mostra il messaggio di errore direttamente all'utente
            wp_die($error, 'Errore di approvazione', array('response' => 403));
            
        } else {//Nessun termine associato al prodotto è in stato "draft"

            // aggiorna lo stato del prodotto a "pubblicato"


            $eliminato = false;
            $stato_documento = get_stato_prodotto($product_id);
            if (strpos($stato_documento, 'eliminato') !== 0) {
                // Codice da eseguire se $stato_documento non inizia con "eliminato"
                $product->set_status('publish');

                // salva il prodotto
                $product->save();
            }else {
                // Se il prodotto è già stato eliminato, non fare nulla
                $eliminato = true;
            }

            // imposta lo stato di approvazione ad approvato
            update_post_meta($product_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, 'approvato');

            // elimina il token
            delete_post_meta($product_id, 'approval_token');

            // mostra un messaggio HTML di successo
            echo get_approve_product_response_html(true, $eliminato);
            exit;
        }
    } else {
        // invia una risposta di errore
        echo get_invalid_token_response_html();
        exit;
    }
}

function reject_product($product_id, $token){
    // ottieni il token salvato come meta del prodotto
    $saved_token = get_post_meta($product_id, 'approval_token', true);

    $product = get_post($product_id);

    // verifica che il token passato corrisponda a quello salvato
    if ($saved_token === $token) {
        // elimina il prodotto
        //wp_delete_post($product_id);

        update_post_meta($product_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, 'non_approvato');

        // elimina il token
        delete_post_meta($product_id, 'approval_token');

        // mostra un messaggio HTML di successo
        echo get_approve_product_response_html(false);
        exit;
    } else {
        // invia una risposta di errore
        echo get_invalid_token_response_html();
        exit;
    }
}

// fai in modo che quando viene eseguita una GET request con action=approve_product, la funzione approve_product venga eseguita
add_action('init', function(){
    if (isset($_GET['action']) && $_GET['action'] === 'approve_product') {
        if (isset($_GET['product_id'], $_GET['token'])) {
            approve_product($_GET['product_id'], $_GET['token']);
        }
    }
});

// fai in modo che quando viene eseguita una GET request con action=reject_product, la funzione reject_product venga eseguita   
add_action('init', function(){
    if (isset($_GET['action']) && $_GET['action'] === 'reject_product') {
        if (isset($_GET['product_id'], $_GET['token'])) {
            reject_product($_GET['product_id'], $_GET['token']);
        }
    }
});


function send_mail_approval_term($term_id, $name_product=''){    
    
    // genera un token di 32 caratteri
    $token = bin2hex(random_bytes(32));
    // ottieni il termine
    $term = get_term($term_id);
    //Recupera l'id dell'utente, successivamente i suoi dati e poi nickname e email
    $creator_id = (int) get_term_meta($term_id, 'created by:', true) ?: get_current_user_id();
    $creator = get_userdata($creator_id);
    $creator_nickname = $creator ? $creator->nickname : '';
    $creator_email = $creator ? $creator->user_email : '';
    //Recupero tassonomia
    $taxonomy_obj = get_taxonomy($term->taxonomy);
    $taxonomy_label = $taxonomy_obj ? $taxonomy_obj->labels->singular_name : $term->taxonomy;

    // salva il token nel database come meta del termine
    update_term_meta($term_id, 'approval_token', $token);

    // ottieni l'indirizzo email di tutti gli utenti con il ruolo di amministratore
    $admin_email = get_users(array(
        'role' => 'administrator',
        'fields' => array('user_email')
    ));


    // trasforma l'array di oggetti in una stringa contenente solo gli indirizzi email separati da punto e virgola
    //$admin_email = implode(';', array_column($admin_email, 'user_email'));


    // costruisci il link di approvazione
    $approval_link = add_query_arg(array(
        'action' => 'approve_term',
        'term_id' => $term_id,
        'token' => $token
    ), home_url());

    // costruisci il link di rifiuto
    $reject_link = add_query_arg(array(
        'action' => 'reject_term',
        'term_id' => $term_id,
        'token' => $token
    ), home_url());

    // costruisci il contenuto dell'email
    $subject = 'Approva il termine ' . $term->name . ' (' . $taxonomy_label . ')';
    $message = get_approve_term_email_html($term, $approval_link, $reject_link, $creator_nickname, $creator_email, $taxonomy_label, $name_product);

    // aggiungi header per email in formato HTML
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // invia l'email ad ogni amministratore
    foreach ($admin_email as $admin) {
        wp_mail($admin->user_email, $subject, $message, $headers);
    }

}

function approve_term($term_id, $token){
    // ottieni il token salvato come meta del termine
    $saved_token = get_term_meta($term_id, 'approval_token', true);

    // verifica che il token passato corrisponda a quello salvato
    if ($saved_token === $token) {

        // aggiorna il meta del termine
        update_term_meta($term_id, 'status', 'approved');

        // elimina il token
        delete_term_meta($term_id, 'approval_token');

        // invia una risposta di successo
        echo get_approve_term_response_html(true);
        exit;
    } else {
        // invia una risposta di errore
        echo get_invalid_token_response_html();
        exit;
    }
}

function reject_term($term_id, $token){
    // ottieni il token salvato come meta del termine
    $saved_token = get_term_meta($term_id, 'approval_token', true);

    $term = get_term($term_id);

    // verifica che il token passato corrisponda a quello salvato
    if ($saved_token === $token) {
        // elimina il termine
        wp_delete_term($term_id, $term->taxonomy);

        // elimina il token
        delete_term_meta($term_id, 'approval_token');
        delete_term_meta($term_id, 'status');

        // invia una risposta di successo
        echo get_approve_term_response_html(false);
        exit;
    } else {
        // invia una risposta di errore
        echo get_invalid_token_response_html();
        exit;
    }
}


// fai in modo che quando viene eseguita una GET request con action=approve_term, la funzione approve_term venga eseguita

add_action('init', function(){
    if (isset($_GET['action']) && $_GET['action'] === 'approve_term') {
        if (isset($_GET['term_id'], $_GET['token'])) {
            approve_term($_GET['term_id'], $_GET['token']);
        }
    }
});

// fai in modo che quando viene eseguita una GET request con action=reject_term, la funzione reject_term venga eseguita

add_action('init', function(){
    if (isset($_GET['action']) && $_GET['action'] === 'reject_term') {
        if (isset($_GET['term_id'], $_GET['token'])) {
            reject_term($_GET['term_id'], $_GET['token']);
        }
    }
});

//funzione che viene richiamata quando devono essere approvate solo delle modifiche su un file
function send_email_approve_product_edit($product_id, $edited_fields){
    
    // genera un token di 32 caratteri
    $token = bin2hex(random_bytes(32));

    // salva il token nel database come meta del prodotto
    update_post_meta($product_id, 'approval_token', $token);

    // ottieni l'indirizzo email di tutti gli utenti con il ruolo di amministratore
    $admin_email = get_users(array(
        'role' => 'administrator',
        'fields' => array('user_email')
    ));

    // ottieni il prodotto
    $product = get_post($product_id);
    error_log("ID prodotto: ".$product_id);
    // costruisci il link di approvazione
    $approval_link = add_query_arg(array(
        'action' => 'approve_edit_product',
        'product_id' => $product_id,
        'token' => $token,
        'edited_fields' => $edited_fields
    ), home_url());

    // costruisci il link di rifiuto
    $reject_link = add_query_arg(array(
        'action' => 'reject_edit_product',
        'product_id' => $product_id,
        'token' => $token
    ), home_url());

    // costruisci il contenuto dell'email
    $subject = 'Approva modifica per il prodotto: ' . $product->post_title;
    $message = get_approve_edit_product_email_html($product, $edited_fields, $approval_link, $reject_link);

    // aggiungi header per email in formato HTML
    $headers = array('Content-Type: text/html; charset=UTF-8');

    //TODO: eliminare
    // wp_mail('frankheat03@gmail.com', $subject, $message, $headers);

    // invia l'email ad ogni amministratore
    foreach ($admin_email as $admin) {
        error_log("Invio email a: ".$admin->user_email);
        wp_mail($admin->user_email, $subject, $message, $headers);
    }

}

//funzione che viene richiamata quando devono essere approvate solo delle modifiche su un file
add_action('init', function(){
    if (isset($_GET['action']) && $_GET['action'] === 'approve_edit_product') {
        if (isset($_GET['product_id'], $_GET['token'], $_GET['edited_fields'])) {
            approve_edit_product($_GET['product_id'], $_GET['token'], $_GET['edited_fields']);
        }
    }
});

//funzione che viene richiamata quando devono essere rifiutate solo delle modifiche su un file
add_action('init', function(){
    if (isset($_GET['action']) && $_GET['action'] === 'reject_edit_product') {
        if (isset($_GET['product_id'], $_GET['token'])) {
            reject_edit_product($_GET['product_id'], $_GET['token']);
        }
    }
});

function approve_edit_product($product_id, $token, $edited_fields){
    // ottieni il token salvato come meta del prodotto
    $saved_token = get_post_meta($product_id, 'approval_token', true);

    // verifica che il token passato corrisponda a quello salvato
    if ($saved_token === $token) {
        if (!aggiorna_prodotto_esistente($edited_fields)){
            wp_json_error('Errore durante l\'aggiornamento del file.');
        };
        // aggiorna il meta del prodotto
        update_post_meta($product_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, 'approvato');

        // elimina il token
        delete_post_meta($product_id, 'approval_token');

        // invia una risposta di successo
        echo get_approve_product_response_html(true);
        exit;
    } else {
        // invia una risposta di errore
        echo get_invalid_token_response_html();
        exit;
    }
}

function reject_edit_product($product_id, $token){
    // ottieni il token salvato come meta del prodotto
    $saved_token = get_post_meta($product_id, 'approval_token', true);

    // verifica che il token passato corrisponda a quello salvato
    if ($saved_token === $token) {
        // Riporto il file ad approvato in quanto deve tornare ad essere acquistabile
        update_post_meta($product_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, 'approvato');

        // elimina il token
        delete_post_meta($product_id, 'approval_token');

        // mostra un messaggio HTML di successo
        echo get_approve_product_response_html(false);
        exit;
    } else {
        // invia una risposta di errore
        echo get_invalid_token_response_html();
        exit;
    }
}


function get_approve_edit_product_email_html($product, $edited_fields, $approval_link, $reject_link, $creator_nickname = '', $creator_email = ''){
    $product_info = get_product_taxonomy_info($product->ID);
    $product_info = $product_info['taxonomies'];
    $author = get_userdata($product->post_author);
    $uploader_nickname = $author ? $author->nickname : '';
    $uploader_email = $author ? $author->user_email : '';
    $email_body = '
    <html>
    <head>
        <style>
            .email-container {
                font-family: Arial, sans-serif;
                color: #333;
                line-height: 1.6;
            }
            .email-header {
                background-color: #f8f8f8;
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }
            .email-body {
                padding: 20px;
            }
            .email-footer {
                background-color: #f8f8f8;
                padding: 10px;
                border-top: 1px solid #ddd;
                text-align: center;
                font-size: 12px;
                color: #777;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                margin: 10px 0;
                font-size: 16px;
                color: #fff !important;
                background-color: #0073aa;
                text-decoration: none;
                border-radius: 5px;
            }
            .button.reject {
                background-color: #d9534f;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f8f8f8;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h2>Modifica prodotto in attesa di approvazione</h2>
            </div>
            <div class="email-body">
                <p>Ciao amministratore,</p>
                <p>Una modifica al prodotto esistente è stata proposta e necessita della tua approvazione.</p>
                <p><strong>Nome prodotto:</strong> ' . $product->post_title . '</p>
                <p><strong>Caricato dall`utente:</strong> ' . esc_html($uploader_nickname) . ' (' . esc_html($uploader_email) . ')</p>
                <h3>Confronto tra prodotto corrente e modifiche proposte:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Attributo</th>
                            <th>Valore Corrente</th>
                            <th>Nuovo Valore</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Titolo</strong></td>
                            <td>' . esc_html($product->post_title) . '</td>
                            <td>' . esc_html($edited_fields["post_title"]) . '</td>
                        </tr>
                        <tr>
                            <td><strong>Descrizione</strong></td>
                            <td>' . esc_html($product->post_content) . '</td>
                            <td>' . esc_html($edited_fields["post_content"]) . '</td>
                        </tr>';  
                        error_log("Product info: ".print_r($product_info, true));
                        foreach ($product_info as $meta_key => $meta_value) {
                            error_log("Meta key: " . $meta_key);
                            error_log("Meta value: " . $meta_value);
                            $current_value = $meta_value; // Valore corrente preso direttamente da $product_info
                            $new_value = isset($edited_fields[$meta_key]) ? $edited_fields[$meta_key] : 'N/A';
                            $email_body .= '<tr>';
                            $email_body .= '<td><strong>' . ucfirst(str_replace('_', ' ', $meta_key)) . '</strong></td>';
                            $email_body .= '<td>' . esc_html($current_value) . '</td>';
                            $email_body .= '<td>' . esc_html($new_value) . '</td>';
                            $email_body .= '</tr>';
                        }
                        $email_body .= '<tr>
                            <td><strong>Prezzo</strong></td>
                            <td>' . esc_html(get_post_meta($product->ID, $product_info['modalita_pubblicazione'] === 'vendi' ? "_costo_in_punti_pro" : "_costo_in_punti_blu", true)) . '</td>
                            <td>' . esc_html($edited_fields['modalita_pubblicazione'] === 'vendi' ? $edited_fields["prezzo"] : PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO) . '</td>
                        </tr>
                    </tbody>
                </table>
                <p>Per approvare le modifiche, clicca su questo link:</p>
                <p><a href="' . $approval_link . '" class="button">Approva modifiche</a></p>
                <p>Per rifiutare le modifiche, clicca su questo link:</p>
                <p><a href="' . $reject_link . '" class="button reject">Rifiuta modifiche</a></p>
            </div>
            <div class="email-footer">
                <p>Grazie per la tua collaborazione.</p>
            </div>
        </div>
    </body>
    </html>';
    return $email_body;
}


function get_invalid_token_response_html(){
    return '<html>
    <head>
        <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            color: #333;
            text-align: center;
            padding: 50px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        h1 {
            color: #d9534f;
        }
        p {
            font-size: 16px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #0073aa;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #005f8d;
        }
        </style>
    </head>
    <body>
        <div class="container">
        <h1>Token non valido</h1>
        <p>Il token fornito non è valido o è scaduto.</p>
        <a href="' . home_url() . '">Torna alla Home</a>
        </div>
    </body>
    </html>';
}

function  get_approve_product_response_html($approved, $deleted = false){
    return '<html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f8f8;
                    color: #333;
                    text-align: center;
                    padding: 50px;
                }
                .container {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                    display: inline-block;
                }
                .reject {
                    color: #d9534f;
                }
                .approve {
                    color: #5cb85c;
                }
                p {
                    font-size: 16px;
                }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    font-size: 16px;
                    color: #fff;
                    background-color: #0073aa;
                    text-decoration: none;
                    border-radius: 5px;
                }
                a:hover {
                    background-color: #005f8d;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1 class="' . ($approved ? 'approve' : 'reject') . '">' . ($approved ? 'Richiesta Approvata' : 'Richiesta Rifiutata') . '</h1>
                <p>La richiesta di pubblicazione del prodotto è stata ' . ($approved ? 'approvata' : 'rifiutata') . ' con successo.</p>
                ' . ($deleted ? '<p>Ad ogni modo, il prodotto è stato eliminato prima dell\'approvazione e non sarà pubblicato.</p>' : '') . '
                <a href="' . home_url() . '">Torna alla Home</a>
            </div>
        </body>
        </html>';
}



function get_approve_product_email_html($product, $approval_link, $reject_link){
    $product_info = get_product_taxonomy_info($product->ID);
    $is_update = (get_post_meta($product->ID, '_numero_versione', true) > 1);
    $old_product_info = array();
    if ($is_update) {
        // ottieni le informazioni sul prodotto precedente
        $old_product_id = get_post_meta($product->ID, '_id_base_prodotto', true);
        error_log("ID prodotto base: ".$old_product_id);
        $old_product_info = get_product_taxonomy_info($old_product_id, true);
    }
    $author = get_userdata($product->post_author);
    $uploader_nickname = $author ? $author->nickname : '';
    $uploader_email = $author ? $author->user_email : '';
    $email_body = '
    <html>
    <head>
        <style>
            .email-container {
                font-family: Arial, sans-serif;
                color: #333;
                line-height: 1.6;
            }
            .email-header {
                background-color: #f8f8f8;
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }
            .email-body {
                padding: 20px;
            }
            .email-footer {
                background-color: #f8f8f8;
                padding: 10px;
                border-top: 1px solid #ddd;
                text-align: center;
                font-size: 12px;
                color: #777;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                margin: 10px 0;
                font-size: 16px;
                color: #fff !important;
                background-color: #0073aa;
                text-decoration: none;
                border-radius: 5px;
            }
            .button.reject {
                background-color: #d9534f;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h2>' . ($is_update ? 'Aggiornamento prodotto in attesa di approvazione' : 'Nuovo prodotto in attesa di approvazione') . '</h2>
            </div>
            <div class="email-body">
                <p>Ciao amministratore,</p>
                ' . ($is_update ? '<p>Un prodotto esistente è stato aggiornato con un nuovo file e ha bisogno della tua approvazione.</p>' : '<p>Un nuovo prodotto è stato aggiunto al sito e ha bisogno della tua approvazione.</p>') . '
                <p><strong>Nome prodotto:</strong> ' . $product->post_title . '</p>
                <p><strong>Caricato dall`utente:</strong> ' . esc_html($uploader_nickname) . ' (' . esc_html($uploader_email) . ')</p>
                <p><strong>Descrizione prodotto:</strong> ' . $product->post_content . '</p>';
                
                if ($is_update) {
                    $email_body .= '<h3>Confronto tra prodotto aggiornato e prodotto precedente:</h3>';
                    $email_body .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
                    $email_body .= '<thead><tr><th>Attributo</th><th>Nuovo Prodotto</th><th>Vecchio Prodotto</th></tr></thead>';
                    $email_body .= '<tbody>';

                    foreach ($product_info['taxonomies'] as $taxonomy => $new_terms) {
                        $old_terms = isset($old_product_info['taxonomies'][$taxonomy]) ? $old_product_info['taxonomies'][$taxonomy] : 'N/A';
                        $email_body .= '<tr>';
                        $email_body .= '<td><strong>' . ucfirst(str_replace('_', ' ', $taxonomy)) . '</strong></td>';
                        $email_body .= '<td>' . esc_html($new_terms) . '</td>';
                        $email_body .= '<td>' . esc_html($old_terms) . '</td>';
                        $email_body .= '</tr>';
                    }

                    if (!empty($product_info['missing_taxonomies']) || !empty($old_product_info['missing_taxonomies'])) {
                        $email_body .= '<tr>';
                        $email_body .= '<td><strong>Tassonomie Mancanti</strong></td>';
                        $email_body .= '<td>' . (!empty($product_info['missing_taxonomies']) ? esc_html(implode(', ', array_keys($product_info['missing_taxonomies']))) : 'Nessuna') . '</td>';
                        $email_body .= '<td>' . (!empty($old_product_info['missing_taxonomies']) ? esc_html(implode(', ', array_keys($old_product_info['missing_taxonomies']))) : 'Nessuna') . '</td>';
                        $email_body .= '</tr>';
                    }

                    $email_body .= '</tbody>';
                    $email_body .= '</table>';
                } else {
                    $email_body .= '<h3>Informazioni sul prodotto:</h3>';
                    foreach ($product_info['taxonomies'] as $taxonomy => $terms) {
                        $email_body .= '<p><strong>' . ucfirst(str_replace('_', ' ', $taxonomy)) . ':</strong> ' . esc_html($terms) . '</p>';
                    }
    
                    if (!empty($product_info['missing_taxonomies'])) {
                        $email_body .= '<h3 style="color: red;">⚠️ Attenzione: Tassonomie mancanti</h3>';
                        foreach ($product_info['missing_taxonomies'] as $taxonomy => $warning) {
                            $email_body .= '<p><strong>' . ucfirst(str_replace('_', ' ', $taxonomy)) . ':</strong> <span style="color: red;">' . esc_html($warning) . '</span></p>';
                        }
                    }
                }
    $email_body .= '
                <p>Per approvare il prodotto, clicca su questo link:</p>
                <p><a href="' . $approval_link . '" class="button">Approva prodotto</a></p>
                <p>Per rifiutare il prodotto, clicca su questo link:</p>
                <p><a href="' . $reject_link . '" class="button reject">Rifiuta prodotto</a></p>
            </div>
            <div class="email-footer">
                <p>Grazie per la tua collaborazione.</p>
            </div>
        </div>
    </body>
    </html>';
    return $email_body;
}

function get_approve_term_email_html($term, $approval_link, $reject_link, $creator_nickname = '', $creator_email = '', $taxonomy_label = '', $name_product = ''){
    return '
    <html>
    <head>
        <style>
            .email-container {
                font-family: Arial, sans-serif;
                color: #333;
                line-height: 1.6;
            }
            .email-header {
                background-color: #f8f8f8;
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }
            .email-body {
                padding: 20px;
            }
            .email-footer {
                background-color: #f8f8f8;
                padding: 10px;
                border-top: 1px solid #ddd;
                text-align: center;
                font-size: 12px;
                color: #777;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                margin: 10px 0;
                font-size: 16px;
                color: #fff !important;
                background-color: #0073aa;
                text-decoration: none;
                border-radius: 5px;
            }
            .button.reject {
                background-color: #d9534f;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h2>Nuovo termine in attesa di approvazione</h2>
            </div>
            <div class="email-body">
                <p>Ciao amministratore,</p>
                <p>' . ($name_product ? 'Un nuovo file è stato caricato con un nuovo termine e ha bisogno della tua approvazione.' : 'Un utente si è registrato con un nuovo termine e ha bisogno della tua approvazione.') . '</p>
                <p><strong>' . esc_html($taxonomy_label) . ':</strong> ' . $term->name . '</p>
                <p><strong>Utente:</strong> ' . esc_html($creator_nickname) . ' (' . esc_html($creator_email) . ')</p>
                ' . ($name_product ? '<p><strong>Prodotto:</strong> ' . $name_product . '</p>' : '') . '
                <p>Per approvare il termine, clicca su questo link:</p>
                <p><a href="' . $approval_link . '" class="button">Approva termine</a></p>
                <p>Per rifiutare il termine, clicca su questo link:</p>
                <p><a href="' . $reject_link . '" class="button reject">Rifiuta termine</a></p>
            </div>
            <div class="email-footer">
                <p>Grazie per la tua collaborazione.</p>
            </div>
        </div>
    </body>
    </html>';
}

function get_approve_term_response_html($approved){
    return '<html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f8f8;
                    color: #333;
                    text-align: center;
                    padding: 50px;
                }
                .container {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                    display: inline-block;
                }
                .reject {
                    color: #d9534f;
                }
                .approve {
                    color: #5cb85c;
                }
                p {
                    font-size: 16px;
                }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    font-size: 16px;
                    color: #fff;
                    background-color: #0073aa;
                    text-decoration: none;
                    border-radius: 5px;
                }
                a:hover {
                    background-color: #005f8d;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1 class="' . ($approved ? 'approve' : 'reject') . '">' . ($approved ? 'Richiesta Approvata' : 'Richiesta Rifiutata') . '</h1>
                <p>La richiesta di pubblicazione del termine è stata ' . ($approved ? 'approvata' : 'rifiutata') . ' con successo.</p>
                <p>' . (!$approved ? 'Il termine è stato eliminato dal database.' : '') . '</p>
                <a href="' . home_url() . '">Torna alla Home</a>
            </div>
        </body>
        </html>';
}