<?php

function getUserInformationJSON($user_id, $check_login = true) {

    if (!is_user_logged_in() && $check_login) {
        return json_encode(array());
    }

    // Calcolo del numero di vendite dell'utente corrente
    $user_documents_sold_count = get_user_documents_sold_count($user_id);

    // Calcolo delle recensioni ricevute dall'utente sui suoi documenti
    $user_documents = get_posts(array(
        'post_type' => 'product',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
    ));

    $reviews_on_user_documents_count = 0;
    foreach ($user_documents as $document_id) {
        $reviews_on_user_documents_count += count(get_comments(array(
            'post_id' => $document_id,
            'status' => 'approve',
        )));
    }

    // Calcolo della media delle recensioni ricevute dall'utente sui suoi documenti
    $reviews_on_user_documents_rating_avg = getPostAverageRating($user_documents);
    // $reviews_on_user_documents_rating_sum = 0;
    // foreach ($user_documents as $document_id) {
    //     $reviews = get_comments(array(
    //         'post_id' => $document_id,
    //         'status' => 'approve',
    //     ));
    //     foreach ($reviews as $review) {
    //         $reviews_on_user_documents_rating_sum += intval(get_comment_meta($review->comment_ID, 'rating', true));
    //     }
    // }
    // // Calcolo della media delle recensioni ricevute dall'utente sui suoi documenti
    // $reviews_on_user_documents_rating_avg = 0;
    // if ($reviews_on_user_documents_count > 0) {
    //     $reviews_on_user_documents_rating_avg = $reviews_on_user_documents_rating_sum / $reviews_on_user_documents_count;
    // }
    // // Arrotondamento alla prima cifra decimale
    // $reviews_on_user_documents_rating_avg = round($reviews_on_user_documents_rating_avg, 1);
    
    // Calcolo dei documenti caricati dall'utente
    $user_documents_count = count(get_posts(array(
        'post_type' => 'product',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
    )));

    $user_info = array(
        'reviews_on_user_documents' => $reviews_on_user_documents_count,
        'reviews_on_user_documents_avg' => $reviews_on_user_documents_rating_avg,
        'documents_count' => $user_documents_count,
        'documents_downloaded_count' => $user_documents_sold_count,
    );

    return json_encode($user_info);

}

// crea una funzione che per un utente non è impostato il meta "custom_avatar" restituisce l'avatar di default
function get_user_avatar_url($user_id) {
    $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
    if ($custom_avatar) {
        return $custom_avatar;
    } else {
        return get_stylesheet_directory_uri(  ) . '/assets/img/user/student-hat.svg';
    }
}

// crea una funzione che restituisce il numero di documenti venduti da un utente
function get_user_documents_sold_count($user_id) {
    // Recupera tutti gli ordini completati
    $orders = wc_get_orders(array(
        'status' => 'completed',
        'limit' => -1,
    ));
    $sold_count = 0;

    foreach ($orders as $order) {
        // Itera su ogni prodotto dell'ordine
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);

            // Controlla se l'utente è il venditore del prodotto
            if ($product && $product->get_post_data()->post_author == $user_id) {
                $sold_count += $item->get_quantity();
            }
        }
    }

    return $sold_count;
}


// Aggiungi l'azione per gestire la richiesta AJAX
add_action('wp_ajax_change_user_password', 'change_user_password');
add_action('wp_ajax_nopriv_change_user_password', 'change_user_password');

function change_user_password() {
    // Verifica le autorizzazioni
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato.']);
        wp_die();
    }
    // Verifica il nonce per la sicurezza
    check_ajax_referer('nonce_change_password', 'security');

    // if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'nonce_change_password')) {
    //     wp_send_json_error(['message' => 'Nonce non valido.']);
    // }

    error_log('Nonce verificato');
    // Recupera l'ID dell'utente corrente
    $user_id = get_current_user_id();
    
    // Recupera la nuova password dal POST
    $new_password = sanitize_text_field($_POST['new_password']);
    $current_password = sanitize_text_field($_POST['current_password']);

    // Verifica che la password corrente sia corretta
    if (!wp_check_password($current_password, wp_get_current_user()->data->user_pass, $user_id)) {
        wp_send_json_error(['message' => 'La password corrente non è corretta']);
        wp_die();
    }

    if(!verifica_complessita_password($new_password)){
        wp_send_json_error(['message' => 'La password non rispetta i requisiti minimi di complessità. <br>'. PASSWORD_COMPLEXITY_MESSAGE]);
        wp_die();
    }

    // Verifica che la nuova password non sia vuota
    if (empty($new_password)) {
        wp_send_json_error(['message' => 'La nuova password non può essere vuota']);
        wp_die();
    }

    // Cambia la password dell'utente
    wp_set_password($new_password, $user_id);

    // Mantieni l'utente loggato dopo il cambio della password
    wp_set_auth_cookie($user_id);

    // Invia una risposta di successo
    wp_send_json_success(['message' => 'Password cambiata con successo']);
    wp_die();
}

function get_user_istituto($user_id) {
    $nome_istituto_terms = get_user_meta($user_id, 'nome_istituto', true);
    $term = get_term($nome_istituto_terms, 'nome_istituto');
    return $term->name;
}

function send_verification_email($user_id){
    error_log("Invio mail verifica");
    // genera un token di 32 caratteri
    $token = get_user_meta($user_id, 'email_confirmation_token', true);

    // if (empty($token)) {
    //     $token = bin2hex(random_bytes(16));
    //     update_user_meta($user_id, 'verification_token', $token);
    // }

    $user = get_userdata($user_id);
    $user_info = get_user_meta($user_id, 'first_name', true);

    $email = $user->user_email;
    error_log($user_info);
    error_log($email);
    $verification_link = add_query_arg(array(
        'action' => 'verify_email',
        'user_id' => $user_id,
        'token' => $token,
    ), site_url('/verify-email'));

    $headers = array('Content-Type: text/html; charset=UTF-8');

    $subject = 'Verifica la tua email - ' . get_bloginfo('name');
    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/email-verifica-mail.php');
    $message = ob_get_clean();

    wp_mail($email, $subject, $message, $headers);
}
// add_action('init', 'send_verification_email');

function verify_email(){
    $user_id = $_GET['user_id'];
    $token = $_GET['token'];

    $saved_token = get_user_meta($user_id, 'email_confirmation_token', true);
    

    if ($saved_token === $token) {
        set_email_verificata($user_id, true);
        delete_user_meta($user_id, 'email_confirmation_token');
        wp_set_auth_cookie($user_id);
        $redirect_url = add_query_arg(array(
            'msg' => 'email-verified',
        ), PROFILAZIONE_UTENTE_PAGE);
        wp_redirect($redirect_url);
        exit;
    } else {
        $redirect_url = add_query_arg(array(
            'msg' => 'invalid-token',

        ), home_url());
        wp_redirect($redirect_url);
        exit;
    }
}

add_action('init', function() {
    if (isset($_GET['action']) && $_GET['action'] == 'verify_email' && isset($_GET['user_id']) && isset($_GET['token'])) {
        verify_email();

    }
});

add_action('wp_footer', function() {
    if (isset($_GET['msg']) && $_GET['msg'] == 'email-verified') {
        $user = get_userdata(get_current_user_id());
        $email = $user->user_email;
        echo '<script>modal_email_verificata();</script>';
        echo '<script>window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
            \'event\': \'user_registration_success\',
            \'user_email\': \'' . esc_js($email) . '\' // opzionale
            });
            console.log("Evento user_email_verified inviato");
            console.log("Email: " + \'' . esc_js($email) . '\');
            console.log("User ID: " + \'' . esc_js($user->ID) . '\');
            console.log("DataLayer: " + window.dataLayer);
            </script>';
    }
}, 100);

add_action('wp_footer', function() {
    if (isset($_GET['msg']) && $_GET['msg'] == 'invalid-token') {
        echo '<script>modal_invalid_token();</script>';
    }
}, 100);

// Aggiungi i campi personalizzati al profilo utente
add_action('show_user_profile', 'aggiungi_campi_personalizzati_profilo_utente');
add_action('edit_user_profile', 'aggiungi_campi_personalizzati_profilo_utente');

function aggiungi_campi_personalizzati_profilo_utente($user) {
    ?>
    <h3>Stato di cancellazione utente</h3>
    <table class="form-table">
        <tr>
            <th><label for="utente_eliminato">Utente Eliminato</label></th>
            <td>
                <input type="checkbox" name="utente_eliminato" id="utente_eliminato" value="1" <?php checked(get_user_meta($user->ID, 'utente_eliminato', true), 1); ?> />
                <span class="description">Seleziona se l'utente è stato eliminato.</span>
            </td>
        </tr>
        <tr>
            <th><label for="data_eliminazione_utente">Data Eliminazione Utente</label></th>
            <td>
                <input type="date" name="data_eliminazione_utente" id="data_eliminazione_utente" value="<?php echo esc_attr(get_user_meta($user->ID, 'data_eliminazione_utente', true)); ?>" />
                <span class="description">Inserisci la data di eliminazione dell'utente.</span>
            </td>
        </tr>
    </table>
    <?php
}

// Salva i campi personalizzati
add_action('personal_options_update', 'salva_campi_personalizzati_profilo_utente');
add_action('edit_user_profile_update', 'salva_campi_personalizzati_profilo_utente');

function salva_campi_personalizzati_profilo_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, 'utente_eliminato', isset($_POST['utente_eliminato']) ? 1 : 0);
    update_user_meta($user_id, 'data_eliminazione_utente', sanitize_text_field($_POST['data_eliminazione_utente']));
}


function get_utente_eliminato($user_id) {
    return (bool) intval(get_user_meta($user_id, 'utente_eliminato', true));
}

function set_utente_eliminato($user_id, $value) {
    update_user_meta($user_id, 'utente_eliminato', $value ? 1 : 0);
}

function get_data_eliminazione_utente($user_id) {
    return get_user_meta($user_id, 'data_eliminazione_utente', true);
}

function set_data_eliminazione_utente($user_id, $value) {
    update_user_meta($user_id, 'data_eliminazione_utente', sanitize_text_field($value));
}

function cancellazione_documenti_per_cancellazione_utente($user_id) {
    error_log("Cancellazione documenti per cancellazione utente");
    // Recupera tutti i documenti dell'utente
    $user_documents = get_posts(array(
        'post_type' => 'product',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
    ));

    // Elimina ogni documento
    foreach ($user_documents as $document_id) {
        wp_update_post(array(
            'ID' => $document_id,
            'post_status' => 'draft',
        ));

        imposta_stato_prodotto($document_id, 'eliminato_cancellazione_utente');
    }
}
add_action('eliminazione_utente', 'cancellazione_documenti_per_cancellazione_utente', 10, 1);


function fatturazione_credito_residuo_per_cancellazione_utente($user_id) {
    error_log("Fatturazione credito residuo per cancellazione utente");
    azzera_crediti_residui_utente($user_id);
}
add_action('eliminazione_utente', 'fatturazione_credito_residuo_per_cancellazione_utente', 10, 1);

function annullamento_sottoscrizioni_per_cancellazione_utente($user_id) {
    error_log("Annullamento sottoscrizioni per cancellazione utente");
    error_log($user_id);
    if (ha_sottoscrizioni_sospese($user_id) || !ha_sottoscrizioni_attive($user_id)) {
        error_log("L'utente non ha sottoscrizioni attive o sospese.");
        return;
    }
    $subscriptions = get_user_meta($user_id, 'sottoscrizioni', true);
    $subscriptions = (array) $subscriptions;
    error_log("Sottoscrizioni");
    error_log(print_r($subscriptions, true));
    $subscription = $subscriptions[0] ?? null;
    $subscription_id = $subscription['id'] ?? null;

    if (!$subscription_id) {
        error_log("Nessuna sottoscrizione trovata per l'utente con ID: " . $user_id);
        return;
    }

    cancel_subscription($user_id, $subscription_id);
    error_log("Sottoscrizione con ID " . $subscription_id . " annullata per l'utente con ID: " . $user_id);
}
add_action('eliminazione_utente', 'annullamento_sottoscrizioni_per_cancellazione_utente', 5, 1);


function modifica_scadenza_punti_per_cancellazione_utente($user_id) {

    update_points_expiry_to_today($user_id);
    error_log("Scadenza punti modificata per l'utente con ID: " . $user_id);
}
add_action('eliminazione_utente', 'modifica_scadenza_punti_per_cancellazione_utente', 10, 1);

function modifica_dati_utente_per_cancellazione_utente($user_id) {
    error_log("Modifica dati utente per cancellazione utente");
    $user = get_userdata($user_id);
    $email = $user->user_email;
    $username = $user->user_login;
    $nickname = $user->nickname;

    error_log(print_r($user, true));
    // Modifica l'email e il nome utente dell'utente
    wp_update_user(array(
        'ID' => $user_id,
        'user_email' => 'deleted_' . time() . '_' . $email,
        'user_login' => 'deleted_' . time() . '_' . $username,
        'nickname' => 'deleted_' . time() . '_' . $nickname
    ));

    forza_modifica_user_login($user_id, 'deleted_' . time() . '_' . $username);

    // Genera una nuova password randomica
    $random_password = wp_generate_password(16, true, true). '_' . time();



    // Aggiorna la password dell'utente
    wp_set_password($random_password, $user_id);



    error_log("Password randomica generata e inviata via email per l'utente con ID: " . $user_id);

    $user = get_userdata($user_id);

    error_log(print_r($user, true));
    // Elimina i metadati dell'utente

    error_log("Email e nome utente modificati per l'utente con ID: " . $user_id);
}
add_action('eliminazione_utente', 'modifica_dati_utente_per_cancellazione_utente', 20, 1);

function forza_modifica_user_login($user_id, $nuovo_login) {
    global $wpdb;

    $user_id = intval($user_id);
    $nuovo_login = sanitize_user($nuovo_login);

    // Controlla se il nuovo login esiste già
    if (username_exists($nuovo_login)) {
        return new WP_Error('login_exists', 'Questo nome utente è già in uso.');
    }

    // Esegui la query per aggiornare user_login
    $result = $wpdb->update(
        $wpdb->users,
        array('user_login' => $nuovo_login),
        array('ID' => $user_id),
        array('%s'),
        array('%d')
    );

    if ($result === false) {
        return new WP_Error('update_failed', 'Errore durante la modifica del nome utente.');
    }

    // Pulisce la cache dell’utente
    clean_user_cache($user_id);



    return true;
}



function traccia_eliminazione_utente($user_id) {

    set_utente_eliminato($user_id, true);
    set_data_eliminazione_utente($user_id, date('Y-m-d H:i:s'));
    error_log("Utente con ID " . $user_id . " marcato come eliminato.");
}
add_action('eliminazione_utente', 'traccia_eliminazione_utente', 10, 1);



function sospendi_richieste_fatturazione_utente($user_id) {
    error_log("Sospendi richieste fatturazione utente");
    $orders = wc_get_orders(array(
        'status' => array('pending', 'processing', 'completed', 'on-hold', 'cancelled', 'refunded', 'failed'),
        'limit' => -1,
    ));

    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            $product = get_post($product_id);

            

            if ($product && $product->post_author == $user_id) {
                $stato_fatturazione = $order->get_meta('_richiesta_fattura_venditore');

                if ($stato_fatturazione != 'caricata') {
                    set_richiesta_fattura_venditore($order->get_id(), 'non_richiedibile_venditore_cancellato');
                    error_log("Ordine ID " . $order->get_id() . " contiene un prodotto dell'utente con ID: " . $user_id);
                }
            }
        }
    }
}
add_action('eliminazione_utente', 'sospendi_richieste_fatturazione_utente', 10, 1);

/*add_action('init', function() {
    sospendi_richieste_fatturazione_utente(18);
});*/

// Getter and setter for user meta fields

function get_user_nome_istituto($user_id, $slug = false) {
    $term_id = get_user_meta($user_id, 'nome_istituto', true);
    if ($slug && $term_id) {
        $term = get_term($term_id, 'nome_istituto');
        return $term ? $term->slug : null;
    }
    return $term_id;
}

function set_user_nome_istituto($user_id, $value) {
    return update_user_meta($user_id, 'nome_istituto', sanitize_text_field($value));
}

function has_user_nome_istituto($user_id) {
    return !empty(get_user_meta($user_id, 'nome_istituto', true));
}

function get_user_nome_corso_di_laurea($user_id, $slug = false) {
    $term_id = get_user_meta($user_id, 'nome_corso_di_laurea', true);
    if ($slug && $term_id) {
        $term = get_term($term_id, 'nome_corso_di_laurea');
        return $term ? $term->slug : null;
    }
    return $term_id;
}

function set_user_nome_corso_di_laurea($user_id, $value) {
    return update_user_meta($user_id, 'nome_corso_di_laurea', sanitize_text_field($value));
}

function has_user_nome_corso_di_laurea($user_id) {
    return !empty(get_user_meta($user_id, 'nome_corso_di_laurea', true));
}

function get_user_nome_corso($user_id, $slug = false) {
    $term_id = get_user_meta($user_id, 'nome_corso', true);
    if ($slug && $term_id) {
        $term = get_term($term_id, 'nome_corso');
        return $term ? $term->slug : null;
    }
    return $term_id;
}

function set_user_nome_corso($user_id, $value) {
    return update_user_meta($user_id, 'nome_corso', sanitize_text_field($value));
}

function has_user_nome_corso($user_id) {
    return !empty(get_user_meta($user_id, 'nome_corso', true));
}

function get_user_nickname($user_id) {
    return get_user_meta($user_id, 'nickname', true);
}

function set_user_nickname($user_id, $nickname) {
    $nickname = sanitize_text_field($nickname);
    if (!valida_stringa($nickname, 3, 20 , '/^[a-zA-Z0-9À-ÿ\s\-\@\.]{3,20}$/')) {
        wp_send_json_error('Nickname non valido');
        wp_die();
    }
    $user = get_user_by('login', $nickname);
    if ($user) {
        wp_send_json_error('Nickname già esistente');
        wp_die();
    }
    return update_user_meta($user_id, 'nickname', sanitize_text_field($nickname));
}

function has_user_nickname($user_id) {
    return !empty(get_user_meta($user_id, 'nickname', true));
}

function get_user_anno_iscrizione($user_id) {
    return get_user_meta($user_id, 'anno_iscrizione', true);
}

function set_user_anno_iscrizione($user_id, $anno) {
    $anno = sanitize_text_field($anno);
    if (!is_numeric($anno) || intval($anno) < 1900 || intval($anno) > intval(date('Y'))) {
        wp_send_json_error('Anno non valido');
        wp_die();
    }
    return update_user_meta($user_id, 'anno_iscrizione', $anno);
}

function has_user_anno_iscrizione($user_id) {
    return !empty(get_user_meta($user_id, 'anno_iscrizione', true));
}

function get_user_lingue_extra($user_id, $slug = false) {
    $term_ids = get_user_meta($user_id, 'lingue', true);
    if ($slug && !empty($term_ids)) {
        $slugs = array();
        foreach ((array) $term_ids as $term_id) {
            $term = get_term($term_id, 'lingue');
            if ($term) {
                $slugs[] = $term->slug;
            }
        }
        return $slugs;
    }
    return (array) $term_ids;
}

function set_user_lingue_extra($user_id, $values) {
    // $values = array_map('sanitize_text_field', (array) $values);
    // return update_user_meta($user_id, 'lingue', $values);
    $lingueExtra = json_decode(stripslashes($_POST['linguaExtra']), true);
    if (!is_array($lingueExtra)) {
        wp_send_json_error('Formato delle lingue non valido');
        wp_die();
    }
    $lingueExtra = array_map('sanitize_text_field', $lingueExtra);
    $lingueTermIds = array();

    foreach ($lingueExtra as $lingua) {
        if (!valida_stringa($lingua, 1, 50, '/^[a-zA-Z\s]+$/')) {
            wp_send_json_error('Lingua non valida: ' . $lingua);
            wp_die();
        }
        $term = get_term_by('slug', $lingua, 'lingue');
        if ($term) {
            $lingueTermIds[] = $term->term_id;
            error_log(print_r($term, true));
        } else {
            wp_send_json_error('Lingua non valida: ' . $lingua);
            wp_die();
        }
    }
    return update_user_meta($user_id, 'lingue', $lingueTermIds);
}

function has_user_lingue_extra($user_id) {
    $term_ids = get_user_meta($user_id, 'lingue', true);
    return !empty($term_ids);
}

// Funzione per ottenere lo stato di verifica email di un utente
function get_email_verificata($user_id) {
    return get_user_meta($user_id, 'email_verificata', true);
}

// Funzione per impostare lo stato di verifica email di un utente
function set_email_verificata($user_id, $email_verificata) {
    update_user_meta($user_id, 'email_verificata', $email_verificata ? 1 : 0);
}

function set_email_confirmation_token($user_id, $token) {
    update_user_meta($user_id, 'email_confirmation_token', $token);
}

function get_email_confirmation_token($user_id) {
    return get_user_meta($user_id, 'email_confirmation_token', true);
}

function get_completamento_informazioni_utente($user_id) {
    return get_user_meta($user_id, 'completamento_informazioni_utente', true);
}

function set_completamento_informazioni_utente($user_id, $completamento_informazioni_utente) {
    update_user_meta($user_id, 'completamento_informazioni_utente', $completamento_informazioni_utente ? 1 : 0);
}

function get_user_first_name($user_id) {
    return get_user_meta($user_id, 'first_name', true);
}

function set_user_first_name($user_id, $value) {
    if(!valida_nome_cognome($value)){
        wp_send_json_error(['message' => 'Nome non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'first_name', sanitize_text_field($value));
}

function has_user_first_name($user_id) {
    return !empty(get_user_meta($user_id, 'first_name', true));
}

function get_user_last_name($user_id) {
    return get_user_meta($user_id, 'last_name', true);
}

function set_user_last_name($user_id, $value) {
    if(!valida_nome_cognome($value)){
        wp_send_json_error(['message' => 'Cognome non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'last_name', sanitize_text_field($value));
}

function has_user_last_name($user_id) {
    return !empty(get_user_meta($user_id, 'last_name', true));
}

function get_user_billing_phone($user_id) {
    return get_user_meta($user_id, 'billing_phone', true);
}

function set_user_billing_phone($user_id, $value) {
    if(!valida_numero_telefono($value)) {
        wp_send_json_error(['message' => 'Numero di telefono non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'billing_phone', sanitize_text_field($value));
}

function has_user_billing_phone($user_id) {
    return !empty(get_user_meta($user_id, 'billing_phone', true));
}

function get_user_lingua($user_id) {
    return get_user_meta($user_id, 'lingua', true);
}

function set_user_lingua($user_id, $lingua) {
    if(!valida_lingua_utente($lingua)) {
        wp_send_json_error(['message' => 'Lingua non valida.']);
        wp_die();
    }
    return update_user_meta($user_id, 'lingua', sanitize_text_field($lingua));
}

function has_user_lingua($user_id) {
    return !empty(get_user_meta($user_id, 'lingua', true));
}

function get_user_nazione($user_id) {
    return get_user_meta($user_id, 'nazione', true);
}

function set_user_nazione($user_id, $nazione) {
    if(!valida_nazione($nazione)) {
        wp_send_json_error(['message' => 'Nazione non valida.']);
        wp_die();
    }
    return update_user_meta($user_id, 'nazione', sanitize_text_field($nazione));
}

function has_user_nazione($user_id) {
    return !empty(get_user_meta($user_id, 'nazione', true));
}

function get_user_universita($user_id) {
    return get_user_meta($user_id, 'universita', true);
}

function set_user_universita($user_id, $universita) {
    $universita = sanitize_text_field($universita);
    if (!valida_stringa($universita,1,150, '/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ-]+(?<!-|\s)$/')) {
        wp_send_json_error('Università non valida');
        wp_die();
    }
    $universita_taxonomy_name = 'nome_istituto';
    $term = get_term_by('slug', $universita, $universita_taxonomy_name);
    if ($term) {
        update_user_meta($user_id, $universita_taxonomy_name, $term->term_id);
    } else {
        $term = wp_insert_term($universita, $universita_taxonomy_name);
        if (is_wp_error($term)) {
            wp_send_json_error('Errore durante l\'inserimento dell\'università');
            wp_die();
        }
        add_term_meta($term['term_id'], 'status', 'draft', true);
        update_user_meta($user_id, $universita_taxonomy_name, $term['term_id']);
        send_mail_approval_term($term['term_id']);
    }
}

function has_user_universita($user_id) {
    return !empty(get_user_meta($user_id, 'universita', true));
}

function get_user_corso_di_laurea($user_id) {
    return get_user_meta($user_id, 'corso_di_laurea', true);
}

function set_user_corso_di_laurea($user_id, $corsoDiLaurea) {
    $corsoDiLaurea = sanitize_text_field($corsoDiLaurea);
    if (!valida_stringa($corsoDiLaurea, 1, 150, '/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ-]+(?<!-|\s)$/')) {
        wp_send_json_error('Corso di laurea non valido');
        wp_die();
    }
    $corso_di_laurea_taxonomy_name = 'nome_corso_di_laurea';
    $term = get_term_by('slug', $corsoDiLaurea, $corso_di_laurea_taxonomy_name);
    if ($term) {
        update_user_meta($user_id, $corso_di_laurea_taxonomy_name, $term->term_id);
    } else {
        $term = wp_insert_term($corsoDiLaurea, $corso_di_laurea_taxonomy_name);
        if (is_wp_error($term)) {
            wp_send_json_error('Errore durante l\'inserimento del corso di laurea');
            wp_die();
        }
        add_term_meta($term['term_id'], 'status', 'draft', true);
        update_user_meta($user_id, $corso_di_laurea_taxonomy_name, $term['term_id']);
        send_mail_approval_term($term['term_id']);
    }
}

function has_user_corso_di_laurea($user_id) {
    return !empty(get_user_meta($user_id, 'corso_di_laurea', true));
}

function get_user_billing_first_name($user_id) {
    return get_user_meta($user_id, 'billing_first_name', true);
}
function set_user_billing_first_name($user_id, $nome) {
    $nome = sanitize_text_field($nome);
    if (!valida_nome_cognome($nome)) {
        wp_send_json_error(['message' => 'Nome non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'billing_first_name', $nome);
}

function get_user_billing_last_name($user_id) {
    return get_user_meta($user_id, 'billing_last_name', true);
}

function set_user_billing_last_name($user_id, $cognome) {
    $cognome = sanitize_text_field($cognome);
    if (!valida_nome_cognome($cognome)) {
        wp_send_json_error(['message' => 'Cognome non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'billing_last_name', $cognome);
}

function get_user_billing_country($user_id) {
    return get_user_meta($user_id, 'billing_country', true);
}

function set_user_billing_country($user_id, $nazione) {
    $nazione = sanitize_text_field($nazione);
    if (!valida_nazione($nazione)) {
        wp_send_json_error(['message' => 'Nazione non valida.']);
        wp_die();
    }
    $nazione = strtoupper($nazione);
    return update_user_meta($user_id, 'billing_country', $nazione);
}

function get_user_billing_address_1($user_id) {
    return get_user_meta($user_id, 'billing_address_1', true);
}

function set_user_billing_address_1($user_id, $indirizzo) {
    $indirizzo = sanitize_text_field($indirizzo);
    if (!valida_indirizzo($indirizzo)) {
        wp_send_json_error(['message' => 'Indirizzo non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'billing_address_1', $indirizzo);
}

function get_user_billing_address_2($user_id) {
    return get_user_meta($user_id, 'billing_address_2', true);
}

function set_user_billing_address_2($user_id, $civico) {
    $civico = sanitize_text_field($civico);
    if (!valida_numero_civico($civico)) {
        wp_send_json_error(['message' => 'Civico non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'billing_address_2', $civico);
}

function get_user_billing_city($user_id) {
    return get_user_meta($user_id, 'billing_city', true);
}

function set_user_billing_city($user_id, $citta) {
    $citta = sanitize_text_field($citta);
    return update_user_meta($user_id, 'billing_city', $citta);
}

function get_user_billing_postcode($user_id) {
    return get_user_meta($user_id, 'billing_postcode', true);
}

function set_user_billing_postcode($user_id, $cap) {
    $cap = sanitize_text_field($cap);
    if (!valida_cap($cap)) {
        wp_send_json_error(['message' => 'CAP non valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'billing_postcode', $cap);
}

function get_user_billing_codice_fiscale($user_id) {
    return get_user_meta($user_id, 'codice_fiscale', true);
}

function set_user_billing_codice_fiscale($user_id, $codice_fiscale) {
    $codice_fiscale = sanitize_text_field($codice_fiscale);
    $nazione = get_user_billing_country($user_id);
    if (!valida_codice_fiscale_o_partita_iva($codice_fiscale, $nazione)) {
        wp_send_json_error(['message' => 'Codice fiscale o partita IVA non valido.']);
        wp_die();
    }
    $codice_fiscale = strtoupper($codice_fiscale);
    return update_user_meta($user_id, 'codice_fiscale', $codice_fiscale);
}





// Funzioni per generare e gestire un hash ID univoco per gli utenti

function generate_user_hash_id($user_id) {
    $salt = 'USER_HASH_SALT'; // Cambia questo valore con una stringa unica e sicura
    $hash = hash('sha256', $salt . $user_id );
    return $hash;
}

function get_user_hash_id($user_id) {
    // Prova a recuperare l'hash già salvato, altrimenti lo genera e lo salva
    $hash = get_user_meta($user_id, 'user_hash_id', true);
    if (!$hash) {
        $hash = generate_user_hash_id($user_id);
        update_user_meta($user_id, 'user_hash_id', $hash);
    }
    return $hash;
}

function set_user_hash_id($user_id) {
    $hash = generate_user_hash_id($user_id);
    update_user_meta($user_id, 'user_hash_id', $hash);
    return $hash;
}

function get_user_id_by_hash($hash) {
    $args = array(
        'meta_key'   => 'user_hash_id',
        'meta_value' => $hash,
        'number'     => 1,
        'fields'     => 'ID',
    );
    $user_query = new WP_User_Query($args);
    if (!empty($user_query->results)) {
        return $user_query->results[0];
    }
    return null;
}

// Imposta l'hash per l'utente quando viene creato
add_action('user_register', function($user_id) {
    set_user_hash_id($user_id);
});


// Aggiungi una pagina personalizzata agli strumenti di WordPress per generare gli hash degli utenti
add_action('admin_menu', function() {
    add_management_page(
        __('Genera Hash Utenti', 'textdomain'),
        __('Genera Hash Utenti', 'textdomain'),
        'manage_options',
        'genera_hash_utenti',
        'render_genera_hash_utenti_page'
    );
});

// Renderizza la pagina per generare gli hash degli utenti
function render_genera_hash_utenti_page() {
    if (isset($_POST['genera_hash_utenti']) && check_admin_referer('genera_hash_utenti_action', 'genera_hash_utenti_nonce')) {
        $user_query = new WP_User_Query(array('fields' => 'ID', 'number' => -1));
        $user_ids = $user_query->get_results();
        $count = 0;
        foreach ($user_ids as $user_id) {
            set_user_hash_id($user_id);
            $count++;
        }
        echo '<div class="updated"><p>' . sprintf(__('Hash generati per %d utenti.', 'textdomain'), $count) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Genera Hash Utenti', 'textdomain'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('genera_hash_utenti_action', 'genera_hash_utenti_nonce'); ?>
            <p><?php _e('Clicca sul pulsante qui sotto per generare (o rigenerare) gli hash per tutti gli utenti esistenti.', 'textdomain'); ?></p>
            <p><input type="submit" name="genera_hash_utenti" class="button button-primary" value="<?php _e('Genera Hash Utenti', 'textdomain'); ?>"></p>
        </form>
    </div>
    <?php
}





?>