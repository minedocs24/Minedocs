<?php

//use WC_Coupon;

// Genera un codice alfanumerico univoco
function generate_unique_code() {
    return strtoupper(bin2hex(random_bytes(12))); // Genera un codice di 8 caratteri
}

// Aggiungi il codice univoco all'ordine quando viene creato
add_action('woocommerce_new_order', 'add_unique_code_to_order', 20, 2);
function add_unique_code_to_order($order, $data) {
    $unique_code = generate_unique_code();
    $data->update_meta_data('_unique_code', $unique_code);
}

// Aggiungi una colonna personalizzata alla lista ordini
add_filter('manage_edit-shop_order_columns', 'aggiungi_colonna_codice_univoco');
function aggiungi_colonna_codice_univoco($columns) {
    $columns['codice_univoco'] = 'Codice Univoco';
    return $columns;
}

// Popola la colonna con il codice univoco
add_action('manage_shop_order_posts_custom_column', 'popola_colonna_codice_univoco', 10, 2);
function popola_colonna_codice_univoco($column, $post_id) {
    if ($column === 'codice_univoco') {
        $order = wc_get_order($post_id);
        $codice_univoco = $order->get_meta('_unique_code');
        echo esc_html($codice_univoco ? $codice_univoco : 'N/A');
    }
}

// Aggiungi una sezione personalizzata nei dettagli dell'ordine
add_action('woocommerce_admin_order_data_after_order_details', 'mostra_codice_univoco_admin');
function mostra_codice_univoco_admin($order) {
    $codice_univoco = $order->get_meta('_unique_code');
    if ($codice_univoco) {
        echo '
        <div><p><strong>Codice Univoco:</strong> ' . esc_html($codice_univoco) . '</p></div>';
    }
}

// Aggiungi opzioni di abbonamento ai prodotti
add_action('woocommerce_product_options_general_product_data', function() {
    woocommerce_wp_checkbox([
        'id' => '_is_recurring',
        'label' => __('Abbonamento Ricorrente', 'textdomain'),
    ]);
    woocommerce_wp_select([
        'id' => '_recurring_interval_unit',
        'label' => __('Unità di Rinnovo', 'textdomain'),
        'options' => [
            'day' => 'Giorno',
            'week' => 'Settimana',
            'month' => 'Mese',
            'year' => 'Anno',
        ],
    ]);
    woocommerce_wp_text_input([
        'id' => '_recurring_interval',
        'label' => __('Intervallo di Rinnovo', 'textdomain'),
        'type' => 'number',
        'custom_attributes' => ['min' => '1'],
    ]);
    woocommerce_wp_checkbox( [
        'id' => '_complete_automatically',
        'label' => 'Completa automaticamente l\'ordine (se ci sono solo prodotti con completamento automatico)',
    ] );
    woocommerce_wp_text_input ([
        'id' => '_id_prodotto_stripe',
        'label' => 'ID Prodotto Stripe',


    ]);
});

// Salva i metadati del prodotto
add_action('woocommerce_process_product_meta', function($post_id) {
    $is_recurring = isset($_POST['_is_recurring']) ? 'yes' : 'no';
    update_post_meta($post_id, '_is_recurring', $is_recurring);

    if (isset($_POST['_recurring_interval_unit'])) {
        update_post_meta($post_id, '_recurring_interval_unit', sanitize_text_field($_POST['_recurring_interval_unit']));
    }

    if (!empty($_POST['_recurring_interval'])) {
        update_post_meta($post_id, '_recurring_interval', absint($_POST['_recurring_interval']));
    }

    $complete_automatically = isset($_POST['_complete_automatically']) ? 'yes' : 'no';
    update_post_meta($post_id, '_complete_automatically', $complete_automatically);

    if (!empty($_POST['_id_prodotto_stripe'])) {
        update_post_meta($post_id, '_id_prodotto_stripe', sanitize_text_field($_POST['_id_prodotto_stripe']));
    }
});


// Aggiungi campi personalizzati per numero_versione e id_base_prodotto ai prodotti
add_action('woocommerce_product_options_general_product_data', function() {
    woocommerce_wp_text_input([
        'id' => '_numero_versione',
        'label' => __('Numero Versione', 'textdomain'),
        'type' => 'number',
        'custom_attributes' => ['step' => 'any'],
    ]);
    woocommerce_wp_text_input([
        'id' => '_id_base_prodotto',
        'label' => __('ID Base Prodotto', 'textdomain'),
        'type' => 'text',
    ]);
});

// Salva i metadati del prodotto per numero_versione e id_base_prodotto
add_action('woocommerce_process_product_meta', function($post_id) {
    if (!empty($_POST['_numero_versione'])) {
        update_post_meta($post_id, '_numero_versione', sanitize_text_field($_POST['_numero_versione']));
    }

    if (!empty($_POST['_id_base_prodotto'])) {
        update_post_meta($post_id, '_id_base_prodotto', sanitize_text_field($_POST['_id_base_prodotto']));
    }
});

function get_numero_versione($post_id) {
    return get_post_meta($post_id, '_numero_versione', true);
}

function get_id_base_prodotto($post_id) {
    return get_post_meta($post_id, '_id_base_prodotto', true);
}

// Aggiungi campo personalizzato per lo stato di approvazione ai prodotti
add_action('post_submitbox_misc_actions', function() {
    global $post;
    if ($post->post_type == 'product') {
        $value = get_post_meta($post->ID, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true);
        ?>
        <div class="misc-pub-section">
            <label for="stato_approvazione"><?php _e('Stato di Approvazione', 'textdomain'); ?></label>
            <select name="<?php echo META_KEY_STATO_APPROVAZIONE_PRODOTTO; ?>" id="stato_approvazione">
                <option value="non_impostato" <?php selected($value, 'non_impostato'); ?>><?php _e('Non impostato', 'textdomain'); ?></option>
                <option value="in_approvazione" <?php selected($value, 'in_approvazione'); ?>><?php _e('In approvazione', 'textdomain'); ?></option>
                <option value="approvato" <?php selected($value, 'approvato'); ?>><?php _e('Approvato', 'textdomain'); ?></option>
                <option value="non_approvato" <?php selected($value, 'non_approvato'); ?>><?php _e('Non approvato', 'textdomain'); ?></option>
            </select>
        </div>
        <?php
    }
});

// Salva i metadati del prodotto per lo stato di approvazione
add_action('woocommerce_process_product_meta', function($post_id) {
    if (isset($_POST[META_KEY_STATO_APPROVAZIONE_PRODOTTO])) {
        update_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, sanitize_text_field($_POST[META_KEY_STATO_APPROVAZIONE_PRODOTTO]));
    }
});

function get_stato_approvazione_prodotto($post_id) {
    return get_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true);
}

/**
 * Imposta lo stato di approvazione del prodotto
 *
 * @param int $post_id ID del prodotto
 * @param string $stato Stato di approvazione [non_impostato, in_approvazione, approvato, non_approvato]
 */

function set_stato_approvazione_prodotto($post_id, $stato) {
    update_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, $stato);
}


//--------CAMPO AUTORE NEL PRODOTTO----------


// Aggiungi un metabox per visualizzare il nome e il link dell'autore del prodotto
add_action('add_meta_boxes', function() {
    add_meta_box(
        'product_author_meta_box',
        __('Autore del Prodotto', 'textdomain'),
        'render_product_author_meta_box',
        'product',
        'side',
        'default'
    );
});

function render_product_author_meta_box($post) {
    $author_id = $post->post_author;
    $author_name = get_the_author_meta('display_name', $author_id);
    $author_url = get_author_posts_url($author_id);
    echo '<p><strong>' . __('Nome Autore:', 'textdomain') . '</strong> ' . esc_html($author_name) . '</p>';
    echo '<p><a href="' . esc_url($author_url) . '" target="_blank">' . __('Visualizza il profilo dell\'autore', 'textdomain') . '</a></p>';
}

// Aggiungi una colonna personalizzata alla lista prodotti
add_filter('manage_edit-product_columns', function($columns) {
    $columns['product_author'] = __('Autore del Prodotto', 'textdomain');
    return $columns;
});

// Popola la colonna con il nome e il link dell'autore
add_action('manage_product_posts_custom_column', function($column, $post_id) {
    if ($column === 'product_author') {
        $author_id = get_post_field('post_author', $post_id);
        $author_name = get_the_author_meta('display_name', $author_id);
        $author_url = get_author_posts_url($author_id);
        echo '<a href="' . esc_url($author_url) . '" target="_blank">' . esc_html($author_name) . '</a>';
    }
}, 10, 2);

// --------------- FUNZIONI PER GESTIRE GLI ABBONAMENTI ----------------

// Aggiungi un campo personalizzato per lo stato del prodotto nel metabox di pubblicazione
add_action('post_submitbox_misc_actions', function() {
    global $post;
    if ($post->post_type == 'product') {
        $value = get_post_meta($post->ID, 'stato_prodotto', true);

        ?>
        <div class="misc-pub-section">
            <label for="stato_prodotto"><?php _e('Stato del Prodotto', 'textdomain'); ?></label>
            <select name="stato_prodotto" id="stato_prodotto">
                <option value="pubblicato" <?php selected($value, 'pubblicato'); ?>><?php _e('Pubblicato', 'textdomain'); ?></option>
                <option value="eliminato_utente" <?php selected($value, 'eliminato_utente'); ?>><?php _e('Eliminato dall\'utente', 'textdomain'); ?></option>
                <option value="eliminato_admin" <?php selected($value, 'eliminato_admin'); ?>><?php _e('Eliminato da admin', 'textdomain'); ?></option>
                <option value="eliminato_cancellazione_utente" <?php selected($value, 'eliminato_cancellazione_utente'); ?>><?php _e('Eliminato per cancellazione utente', 'textdomain'); ?></option>
                <option value="nascosto_aggiornamento" <?php selected($value, 'nascosto_aggiornamento'); ?>><?php _e('Nascosto per aggiornamento', 'textdomain'); ?></option>
            </select>
        </div>
        <?php
    }
});

// Salva i metadati del prodotto per lo stato del prodotto
add_action('woocommerce_process_product_meta', function($post_id) {
    if (isset($_POST['stato_prodotto'])) {
        update_post_meta($post_id, 'stato_prodotto', sanitize_text_field($_POST['stato_prodotto']));
    }
});

function imposta_stato_prodotto($post_id, $stato) {
    update_post_meta($post_id, 'stato_prodotto', $stato);
}

function get_stato_prodotto($post_id) {
    return get_post_meta($post_id, 'stato_prodotto', true);
}

function calcola_moltiplicatore_punti_per_coupon($order) {
    $coupon_code = $order->get_coupon_codes();
    error_log('Coupon codes: ' . print_r($coupon_code, true));
    if(empty($coupon_code)) {
        return 1;
    } else {
        $coupon_codes = array_map('strtoupper', $coupon_code);
        if(in_array('LEVANTEFOR25', $coupon_codes)){
            return 1.1;
        }

    }
    return 1;
}

// Aggiungi punti pro all'utente quando l'ordine viene completato
add_action('woocommerce_order_status_completed', 'aggiungi_punti_pro_all_utente');
function aggiungi_punti_pro_all_utente($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    $moltiplicatore_punti = calcola_moltiplicatore_punti_per_coupon($order);
    error_log('aggiungi_punti_pro_all_utente: ' . $user_id);
    $data = array();
    $data['user_id'] = $user_id;
    $data['order_id'] = $order_id;
    $data['hidden_to_user'] = false;
    if ($user_id) {
        $trial_end_date = $order->get_meta('_stripe_trial_end');
        error_log('trial_end_date: ' . $trial_end_date);
        if ($trial_end_date && $trial_end_date > time()) {
            error_log('trial_end_date in: ' . $trial_end_date);
            global $sistemiPunti;
            foreach ($sistemiPunti as $key => $sistema) {
                $current_system = explode('_', $sistema->get_meta_key())[1];
                get_sistema_punti($current_system)->aggiungi_punti($user_id, get_punti_prova($sistema));                
            }
            update_user_meta( $user_id, 'periodo_prova_disponibile', false );
            extend_abbonamento($user_id, get_durata_periodo_prova());
            
        } else {   
            
            error_log(print_r($order->get_items(), true));

            $totale_punti_accreditati = 0;
            $pagato_totale_per_punti_pro = 0;

            
            
            foreach ($order->get_items() as $item) {

                $product = $item->get_product();
                $product_id = $product->get_id();
                $product_name = $product->get_name();
                $quantity = $item->get_quantity();

                error_log('product_id: ' . $product_id);
                error_log('product_name: ' . $product_name);
                error_log('price: ' . $product->get_price());


                if ($product && $product->get_sku() == SKU_ABBONAMENTO_30_GIORNI) {
                    $subscription_id = $order->get_meta('_stripe_subscription_id');
                    $data['related_subscription_id'] = $subscription_id;
                    $data['description'] = 'Accredito punti Pro per acquisto abbonamento 30 giorni';
                    $data['expiring_date'] = date('Y-m-d', strtotime('+30 days'));
                    $data['moltiplicatore'] = $moltiplicatore_punti;
                    update_expire_date_subscription($subscription_id, date('Y-m-d', strtotime('+30 days')));
                    get_sistema_punti('pro')->aggiungi_punti($user_id, AMOUNT_PUNTI_PRO_ABBPRO030 * $moltiplicatore_punti, $data);
                    $totale_punti_accreditati += $quantity * AMOUNT_PUNTI_PRO_ABBPRO030 * $moltiplicatore_punti;
                    $pagato_totale_per_punti_pro += $product->get_price() * $quantity;
                    extend_abbonamento($user_id, 30, $data);
                   
                } elseif ($product && $product->get_sku() == SKU_ABBONAMENTO_90_GIORNI) {
                    $subscription_id = $order->get_meta('_stripe_subscription_id');
                    $data['related_subscription_id'] = $subscription_id;
                    $data['description'] = 'Accredito punti Pro per acquisto abbonamento 90 giorni';
                    $data['expiring_date'] = date('Y-m-d', strtotime('+90 days'));
                    $data['moltiplicatore'] = $moltiplicatore_punti;
                    update_expire_date_subscription($subscription_id, date('Y-m-d', strtotime('+90 days')));
                    get_sistema_punti('pro')->aggiungi_punti($user_id, AMOUNT_PUNTI_PRO_ABBPRO090 * $moltiplicatore_punti, $data);
                    $totale_punti_accreditati += $quantity * AMOUNT_PUNTI_PRO_ABBPRO090 * $moltiplicatore_punti;
                    $pagato_totale_per_punti_pro += $product->get_price() * $quantity;
                    extend_abbonamento($user_id, 90, $data);

                } elseif ($product && $product->get_sku() == SKU_ABBONAMENTO_365_GIORNI) {
                    $subscription_id = $order->get_meta('_stripe_subscription_id');
                    $data['related_subscription_id'] = $subscription_id;
                    $data['description'] = 'Accredito punti Pro per acquisto abbonamento 365 giorni';
                    $data['expiring_date'] = date('Y-m-d', strtotime('+365 days'));
                    $data['moltiplicatore'] = $moltiplicatore_punti;
                    update_expire_date_subscription($subscription_id, date('Y-m-d', strtotime('+365 days')));
                    get_sistema_punti('pro')->aggiungi_punti($user_id, AMOUNT_PUNTI_PRO_ABBPRO365 * $moltiplicatore_punti, $data);
                    $totale_punti_accreditati += $quantity * AMOUNT_PUNTI_PRO_ABBPRO365 * $moltiplicatore_punti;
                    $pagato_totale_per_punti_pro += $product->get_price() * $quantity;
                    extend_abbonamento($user_id, 365, $data);

                } elseif ($product && $product->get_sku() == SKU_PUNTI_PRO_150) {
                    $data['description'] = 'Accredito punti Pro per acquisto pacchetto 150 punti Pro';
                    get_sistema_punti('pro')->aggiungi_punti($user_id, $quantity * 150, $data);
                    $totale_punti_accreditati += $quantity * 150;
                    $pagato_totale_per_punti_pro += $product->get_price() * $quantity;

                } elseif ($product && $product->get_sku() == SKU_PUNTI_PRO_500) {
                    $data['description'] = 'Accredito punti Pro per acquisto pacchetto 500 punti Pro';
                    get_sistema_punti('pro')->aggiungi_punti($user_id, $quantity * 500, $data);
                    $totale_punti_accreditati += $quantity * 500;
                    $pagato_totale_per_punti_pro += $product->get_price() * $quantity;

                } elseif ($product && $product->get_sku() == SKU_PUNTI_PRO_1000) {
                    $data['description'] = 'Accredito punti Pro per acquisto pacchetto 1000 punti Pro';
                    get_sistema_punti('pro')->aggiungi_punti($user_id, $quantity * 1000, $data);
                    $totale_punti_accreditati += $quantity * 1000;
                    $pagato_totale_per_punti_pro += $product->get_price() * $quantity;
                }

               
            }

            error_log('[PROBLEMA SCONTO] order: ' . print_r($order, true));
            $discount_amount = $order->get_discount_total();
            error_log('[PROBLEMA SCONTO] discount_amount: ' . $discount_amount);
            error_log('[PROBLEMA SCONTO] pagato_totale_per_punti_pro: ' . $pagato_totale_per_punti_pro);
            error_log('[PROBLEMA SCONTO] totale_punti_accreditati: ' . $totale_punti_accreditati);
            error_log('[PROBLEMA SCONTO] VALORE_PUNTI_PRO: ' . VALORE_PUNTI_PRO);
            $ricarico_punti_pro = $pagato_totale_per_punti_pro - $totale_punti_accreditati * VALORE_PUNTI_PRO - $discount_amount;
            if($ricarico_punti_pro != 0) {
                error_log('ricarico_punti_pro: ' . $ricarico_punti_pro);
                do_action('trattieni_ricarico_su_punti_pro', $user_id, $ricarico_punti_pro, $data);
            }
            //do_action('trattieni_ricarico_su_punti_pro', $user_id, $ricarico_punti_pro, $data);
        }
    }
}


// L'utente non può acquistare un abbonamento PRO se ne ha già uno attivo
add_filter('woocommerce_add_to_cart_validation', 'verifica_prodotto_nel_carrello', 10, 3);
function verifica_prodotto_nel_carrello($passed, $product_id, $quantity) {
    $product = wc_get_product($product_id);
    if ($product && ($product->get_sku() == SKU_ABBONAMENTO_30_GIORNI || $product->get_sku() == SKU_ABBONAMENTO_90_GIORNI || $product->get_sku() == SKU_ABBONAMENTO_365_GIORNI) && is_abbonamento_attivo(get_current_user_id())) {
        wc_add_notice('Non puoi acquistare un abbonamento PRO in quanto hai già un abbonamento attivo.', 'error');
        return false;
    }
    return $passed;
}

add_action('woocommerce_order_status_processing', 'completa_ordine_automaticamente_se_necessario');
function completa_ordine_automaticamente_se_necessario($order_id) {
    $order = wc_get_order($order_id);
    $completa_automaticamente = true;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->get_meta('_complete_automatically') !== 'yes') {
            $completa_automaticamente = false;
            break;
        }
    }

    if ($completa_automaticamente) {
        $order->update_status('completed');
    }
}

function get_user_review_for_product($product_id, $user_id) {
    // Ottieni l'ID dell'utente corrente
    
    if (!$user_id) {
        return null; // Nessun utente loggato
    }

    // Recupera i commenti (recensioni) per il prodotto specificato
    $args = array(
        'post_id' => $product_id, // ID del prodotto
        'user_id' => $user_id,    // ID dell'utente corrente
        'number'  => 1,           // Solo una recensione
        'status'  => 'approve',   // Solo recensioni approvate
    );
    $comments = get_comments($args);

    if (!empty($comments)) {
        $review = $comments[0]; // Prendi la prima recensione (ce n'è solo una)
        $rating = get_comment_meta($review->comment_ID, 'rating', true); // Ottieni la valutazione in stelle
        return array(
            'review_text' => $review->comment_content,
            'rating'      => $rating,
        );
    }

    return null; // Nessuna recensione trovata
}

// ELIMINA IL FILE CARICATO DALL'UTENTE SU RICHIESTA DELL'UTENTE STESSO
function delete_user_file() {
    if(get_current_user_id(  )==0){
        wp_send_json_error(array('message' => 'Accedi per poter cancellare il documento!'));
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_file_nonce')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    // Verifica il nonce (opzionale ma consigliato per la sicurezza)
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(['message' => 'ID del file non valido.']);
    }

    $post_hid = sanitize_text_field($_POST['post_id']);
    $post_id = get_product_id_by_hash($post_hid);
    if (!$post_id) {
        wp_send_json_error(['message' => 'ID del file non valido.']);
    }

    // Controlla se l'utente ha i permessi per eliminare
    if (!current_user_can('delete_post', $post_id) && get_current_user_id() != get_post($post_id)->post_author) {
        wp_send_json_error(['message' => 'Permessi insufficienti.']);
    }

    //rendi il file una bozza e rinomina il titolo anteponendo "Eliminato - "
    $post = get_post($post_id);
    imposta_stato_prodotto($post_id, 'eliminato_utente');
    //$post->post_title = 'ELIMINATO - ' . $post->post_title;
    $post->post_status = 'draft';
    $post = wp_update_post($post);

    if ($post) {
        wp_send_json_success(['message' => 'File eliminato con successo.']);
    } else {
        wp_send_json_error(['message' => 'Errore durante l\'eliminazione del file.']);
    }
}

// Registra l'azione Ajax per utenti autenticati
add_action('wp_ajax_delete_user_file', 'delete_user_file');


function generate_product_hash_id($product_id) {
    $salt = 'PRODUCT_HASH_SALT'; // Cambia questo valore con una stringa unica
    $hash = hash('sha256', $salt . $product_id );
    return $hash;
}

function get_product_hash_id($product_id) {
    $hash = generate_product_hash_id($product_id);
    return $hash;
}

function set_product_hash_id($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) {
        return;
    }
    $hash = generate_product_hash_id($product_id);
    /*$product->set_meta_data(array(
        'key' => 'product_hash_id',
        'value' => $hash,
    ));
    $product->save();*/

    update_post_meta($product_id, 'product_hash_id', $hash);

}


function get_product_id_by_hash($hash) {
    $args = array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => 'product_hash_id',
                'value' => $hash,
                'compare' => '='
            )
        ),
        'post_status' => array('publish', 'draft'), // Corretto da 'status' a 'post_status'
        'posts_per_page' => 1, // Limita a un solo prodotto
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $post = $query->posts[0]; // Prendi il primo prodotto trovato
        return $post->ID; // Restituisci l'ID del prodotto
    }
    return null; // Nessun prodotto trovato con l'hash specificato
}

// Aggiungi una pagina personalizzata agli strumenti di WordPress
add_action('admin_menu', function() {
    add_management_page(
        __('Reimposta Hash Prodotti', 'textdomain'),
        __('Reimposta Hash Prodotti', 'textdomain'),
        'manage_woocommerce',
        'reset_product_hashes',
        'render_reset_product_hashes_page'
    );
});

// Renderizza la pagina per reimpostare gli hash dei prodotti
function render_reset_product_hashes_page() {
    if (isset($_POST['reset_hashes']) && check_admin_referer('reset_product_hashes_action', 'reset_product_hashes_nonce')) {
        $products = wc_get_products(['limit' => -1]);
        foreach ($products as $product) {
            set_product_hash_id($product->get_id());
        }
        echo '<div class="updated"><p>' . __('Hash dei prodotti reimpostati con successo.', 'textdomain') . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Reimposta Hash Prodotti', 'textdomain'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('reset_product_hashes_action', 'reset_product_hashes_nonce'); ?>
            <p><?php _e('Clicca sul pulsante qui sotto per reimpostare gli hash per tutti i prodotti.', 'textdomain'); ?></p>
            <p><input type="submit" name="reset_hashes" class="button button-primary" value="<?php _e('Reimposta Hash', 'textdomain'); ?>"></p>
        </form>
    </div>
    <?php
}

// Aggiungi un metabox per visualizzare l'hash del prodotto nella pagina di modifica
add_action('add_meta_boxes', function() {
    add_meta_box(
        'product_hash_meta_box',
        __('Hash del Prodotto', 'textdomain'),
        'render_product_hash_meta_box',
        'product',
        'normal',
        'default'
    );
});

function render_product_hash_meta_box($post) {
    $product_id = $post->ID;
    $hash = get_product_hash_id($product_id);
    echo '<p><strong>' . __('Hash:', 'textdomain') . '</strong></p>';
    echo '<p>' . esc_html($hash) . '</p>';
}

// Imposta l'hash del prodotto quando viene creato
add_action('woocommerce_new_product', function($product_id) {
    set_product_hash_id($product_id);
});

function get_prezzi_punti_pro() {
    return array(10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 400, 500, 600, 700);
}

// Getter e Setter per il titolo del post
// function get_post_title($post_id) {
//     return get_the_title($post_id);
// }

// function set_post_title($post_id, $title) {
//     wp_update_post([
//         'ID' => $post_id,
//         'post_title' => sanitize_text_field($title),
//     ]);
// }

// // Getter e Setter per la descrizione del post
// function get_post_description($post_id) {
//     $post = get_post($post_id);
//     return $post ? $post->post_content : '';
// }

// function set_post_description($post_id, $description) {
//     wp_update_post([
//         'ID' => $post_id,
//         'post_content' => wp_kses_post($description),
//     ]);
// }

// Getter e Setter per i termini delle tassonomie
function get_post_terms($post_id, $taxonomy) {
    return wp_get_post_terms($post_id, $taxonomy, ['fields' => 'names']);
}

function set_post_terms($post_id, $terms, $taxonomy) {
    wp_set_post_terms($post_id, array_map('sanitize_text_field', (array) $terms), $taxonomy);
}

// Getter e Setter per la tassonomia 'nome_istituto'
function get_nome_istituto($post_id, $returnSlug = false) {
    $terms = get_post_terms($post_id, 'nome_istituto');
    if ($returnSlug) {
        $term = get_term_by('name', $terms[0] ?? '', 'nome_istituto');
        return $term ? $term->slug : null;
    }
    return $terms[0] ?? null;
}

function set_nome_istituto($post_id, $terms) {
    set_post_terms($post_id, $terms, 'nome_istituto');
}

// Getter e Setter per la tassonomia 'nome_corso_di_laurea'
function get_nome_corso_di_laurea($post_id, $returnSlug = false) {
    $terms = get_post_terms($post_id, 'nome_corso_di_laurea');
    if ($returnSlug) {
        $term = get_term_by('name', $terms[0] ?? '', 'nome_corso_di_laurea');
        return $term ? $term->slug : null;
    }
    return $terms[0] ?? null;
}

function set_nome_corso_di_laurea($post_id, $terms) {
    set_post_terms($post_id, $terms, 'nome_corso_di_laurea');
}

// Getter e Setter per la tassonomia 'nome_corso'
function get_nome_corso($post_id, $returnSlug = false) {
    $terms = get_post_terms($post_id, 'nome_corso');
    if ($returnSlug) {
        $term = get_term_by('name', $terms[0] ?? '', 'nome_corso');
        return $term ? $term->slug : null;
    }
    return $terms[0] ?? null;
}

function set_nome_corso($post_id, $terms) {
    set_post_terms($post_id, $terms, 'nome_corso');
}

// Getter e Setter per la tassonomia 'anno_accademico'
function get_anno_accademico($post_id, $returnSlug = false) {
    $terms = get_post_terms($post_id, 'anno_accademico');
    if ($returnSlug) {
        $term = get_term_by('name', $terms[0] ?? '', 'anno_accademico');
        return $term ? $term->slug : null;
    }
    return $terms[0] ?? null;
}

function set_anno_accademico($post_id, $terms) {
    set_post_terms($post_id, $terms, 'anno_accademico');
}

// Getter e Setter per la tassonomia 'tipo_prodotto'
function get_tipo_prodotto($post_id, $returnSlug = false) {
    $terms = get_post_terms($post_id, 'tipo_prodotto');
    error_log('get_tipo_prodotto: ' . print_r($terms, true));
    if ($returnSlug) {
        $term = get_term_by('name', $terms[1] ?? '', 'tipo_prodotto');
        return $term ? $term->slug : null;
    }
    return $terms[1] ?? null;
}

function set_tipo_prodotto($post_id, $terms) {
    set_post_terms($post_id, $terms, 'tipo_prodotto');
}

// Getter e Setter per la tassonomia 'modalita_pubblicazione'
function get_modalita_pubblicazione($post_id, $returnSlug = false) {
    $terms = get_post_terms($post_id, 'modalita_pubblicazione');
    if ($returnSlug) {
        $term = get_term_by('name', $terms[0] ?? '', 'modalita_pubblicazione');
        return $term ? $term->slug : null;
    }
    return $terms[0] ?? null;
}

function set_modalita_pubblicazione($post_id, $terms) {
    set_post_terms($post_id, $terms, 'modalita_pubblicazione');
}

// Getter per il prezzo del prodotto
function get_prezzo_prodotto($post_id) {
    if (get_modalita_pubblicazione($post_id) == 'vendi') {
        return get_post_meta($post_id, '_costo_in_punti_pro', true);
    } else {
        return PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO;
    }
}

/**
 * Coupon unico con percentuali diverse per prodotto/variazione
 * Usa il filtro ufficiale dei coupon di WooCommerce.
 */
add_filter('woocommerce_coupon_get_discount_amount', 'mappa_sconti_per_prodotto', 10, 5);
function mappa_sconti_per_prodotto($discount, $discounting_amount, $cart_item, $single, $coupon) {

    // 1) Limita al tuo codice coupon
    $target_code = 'PROMOLANCIOPRO'; // <-- cambia qui
    if (strcasecmp($coupon->get_code(), $target_code) !== 0) {
        return $discount; // lascia intatto ogni altro coupon
    }

    // 2) Mappa percentuali per prodotto/variazione (ID => percentuale)
    //    usa l'ID della variazione se vuoi colpire solo quella, altrimenti l'ID del semplice/prodotto padre.
    $percentuali = [
        SKU_ABBONAMENTO_30_GIORNI => 0.15, // Prodotto (o variazione) ID 123 → 15%
        SKU_ABBONAMENTO_90_GIORNI => 0.10, // ID 456 → 10%
        SKU_ABBONAMENTO_365_GIORNI => 0.05, // ID 789 → 5%
    ];

    // 3) Ricava l'ID "effettivo" dell'item (variazione se presente, altrimenti prodotto)
    $product_id    = isset($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
    $product = wc_get_product($product_id);
    $sku = $product->get_sku();
    $variation_id  = !empty($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : 0;
    $effective_id  = $variation_id > 0 ? $variation_id : $product_id;

    // 4) Se non è nella mappa, niente sconto da questo coupon
    if (!isset($percentuali[$sku])) {
        return 0;
    }

    // 5) Calcola l'importo scontato per *questa riga* in base al prezzo che Woo fornisce
    //    ($discounting_amount è l'importo su cui calcolare lo sconto, già coerente con imposte/impostazioni)
    $percent = (float) $percentuali[$sku];
    $custom_discount = $discounting_amount * $percent;

    // Arrotonda ai decimali di WooCommerce
    $precision = wc_get_price_decimals();
    return round($custom_discount, $precision);
}


