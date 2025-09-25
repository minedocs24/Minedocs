<?php

// Aggiunge il campo Avatar nella pagina del profilo utente
function aggiungi_campo_avatar_profilo( $user ) { 
    ?>
    <h3><?php esc_html_e('Avatar Personalizzato', 'tuo-tema'); ?></h3>
    <table class="form-table">
        <tr>
            <th>
                <label for="avatar"><?php esc_html_e('Avatar', 'tuo-tema'); ?></label>
            </th>
            <td>
                <input type="button" class="button" id="upload-avatar" value="<?php esc_attr_e('Seleziona Avatar', 'tuo-tema'); ?>" />
                <input type="hidden" name="custom_avatar" id="custom_avatar" value="<?php echo esc_attr( get_user_meta( $user->ID, 'custom_avatar', true ) ); ?>" />
                <div id="avatar-preview">
                    <?php if ( $avatar_url = get_user_meta( $user->ID, 'custom_avatar', true ) ) : ?>
                        <img src="<?php echo esc_url( $avatar_url ); ?>" style="max-width: 150px; height: auto;" />
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
    <?php 
}

add_action( 'show_user_profile', 'aggiungi_campo_avatar_profilo' );
add_action( 'edit_user_profile', 'aggiungi_campo_avatar_profilo' );

// Salva l'avatar personalizzato quando l'utente aggiorna il profilo
function salva_campo_avatar_profilo( $user_id ) {
    if ( isset( $_POST['custom_avatar'] ) ) {
        update_user_meta( $user_id, 'custom_avatar', esc_url_raw( $_POST['custom_avatar'] ) );
    }
}

add_action( 'personal_options_update', 'salva_campo_avatar_profilo' );
add_action( 'edit_user_profile_update', 'salva_campo_avatar_profilo' );

// Carica lo script per il selettore media nella pagina del profilo utente
function carica_media_scripts_per_avatar( $hook ) {
    // Controlla che sia la pagina del profilo utente o la pagina di modifica utente
    if ( $hook == 'profile.php' || $hook == 'user-edit.php' ) {
        wp_enqueue_media(); // Carica il selettore media di WordPress
        wp_enqueue_script( 'media-upload' );
    }
}

add_action( 'admin_enqueue_scripts', 'carica_media_scripts_per_avatar' );

// Aggiungi lo script JavaScript personalizzato per il selettore media
function carica_media_script_avatar_corretto() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var mediaUploader;

            $('#upload-avatar').on('click', function(e) {
                e.preventDefault();

                // Inizializza il selettore dei media se non esiste già
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: '<?php esc_html_e('Scegli un Avatar', 'tuo-tema'); ?>',
                    button: {
                        text: '<?php esc_html_e('Usa questa immagine', 'tuo-tema'); ?>'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#custom_avatar').val(attachment.url);
                    $('#avatar-preview').html('<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />');
                });

                mediaUploader.open();
            });
        });
    </script>
    <?php
}

add_action( 'admin_footer-profile.php', 'carica_media_script_avatar_corretto' );
add_action( 'admin_footer-user-edit.php', 'carica_media_script_avatar_corretto' );

// Mostra il metabox nella pagina di modifica dell'utente
function aggiungi_metabox_tassonomia_nome_istituto($user) {
    // Ottieni tutti i termini della tassonomia
    $terms = get_terms(array(
        'taxonomy' => 'nome_istituto',
        'hide_empty' => false,
    ));
    
    // Ottieni i termini assegnati all'utente
    //$user_terms = wp_get_object_terms($user->ID, 'nome_istituto', array('fields' => 'ids'));
    /*
    $user_terms = get_user_meta( $user->ID, 'nome_istituto',  true);
    $user_terms = array_map('intval', (array) $user_terms);
    */

    $user_term = get_user_meta( $user->ID, 'nome_istituto',  true);
    ?>
    <h3><?php _e('Nome Istituto', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="nome_istituto"><?php _e('Seleziona Nome Istituto', 'textdomain'); ?></label></th>
            <td>
                <select name="nome_istituto[]" id="nome_istituto" multiple="multiple">
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php echo $term->term_id == $user_term ? 'selected' : ''; ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br/>
                <span class="description"><?php _e('Seleziona il Nome Istituto per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_tassonomia_nome_istituto');
add_action('edit_user_profile', 'aggiungi_metabox_tassonomia_nome_istituto');

// Salva il Nome Istituto selezionato quando il profilo utente viene aggiornato
function salva_tassonomia_nome_istituto_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    // Verifica e assegna il termine
    $nome_istituto = isset($_POST['nome_istituto']) ? intval($_POST['nome_istituto'][0]) : null;
    update_user_meta($user_id, 'nome_istituto', $nome_istituto);
}
add_action('personal_options_update', 'salva_tassonomia_nome_istituto_utente');
add_action('edit_user_profile_update', 'salva_tassonomia_nome_istituto_utente');

// Mostra il metabox nella pagina di modifica dell'utente per la tassonomia nome_corso_di_laurea
function aggiungi_metabox_tassonomia_nome_corso_di_laurea($user) {
    // Ottieni tutti i termini della tassonomia
    $terms = get_terms(array(
        'taxonomy' => 'nome_corso_di_laurea',
        'hide_empty' => false,
    ));
    
    // Ottieni i termini assegnati all'utente
    //$user_terms = wp_get_object_terms($user->ID, 'nome_corso_di_laurea', array('fields' => 'ids'));
    $user_terms = get_user_meta( $user->ID, 'nome_corso_di_laurea',  true);
    $user_terms = array_map('intval', (array) $user_terms);
    
    ?>
    <h3><?php _e('Nome Corso di Laurea', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="nome_corso_di_laurea"><?php _e('Seleziona Nome Corso di Laurea', 'textdomain'); ?></label></th>
            <td>
                <select name="nome_corso_di_laurea[]" id="nome_corso_di_laurea" multiple="multiple">
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php echo in_array($term->term_id, $user_terms) ? 'selected' : ''; ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br/>
                <span class="description"><?php _e('Seleziona il Nome Corso di Laurea per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_tassonomia_nome_corso_di_laurea');
add_action('edit_user_profile', 'aggiungi_metabox_tassonomia_nome_corso_di_laurea');

// Salva il Nome Corso di Laurea selezionato quando il profilo utente viene aggiornato
function salva_tassonomia_nome_corso_di_laurea_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e assegna i termini
    $nome_corso_di_laurea = isset($_POST['nome_corso_di_laurea']) ? $_POST['nome_corso_di_laurea'] : array();
    $nome_corso_di_laurea = array_map('intval', $nome_corso_di_laurea);
    error_log("Nome corso di laurea: " . print_r($nome_corso_di_laurea, true));
    //wp_set_object_terms($user_id, $nome_corso_di_laurea, 'nome_corso_di_laurea', false);
    update_user_meta( $user_id, 'nome_corso_di_laurea', $nome_corso_di_laurea );
}
add_action('personal_options_update', 'salva_tassonomia_nome_corso_di_laurea_utente');
add_action('edit_user_profile_update', 'salva_tassonomia_nome_corso_di_laurea_utente');

// Mostra il metabox nella pagina di modifica dell'utente per l'anno di iscrizione
function aggiungi_metabox_anno_iscrizione($user) {
    // Ottieni l'anno di iscrizione dell'utente
    $anno_iscrizione = get_user_meta($user->ID, 'anno_iscrizione', true);
    ?>
    <h3><?php _e('Anno di Iscrizione', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="anno_iscrizione"><?php _e('Anno di Iscrizione', 'textdomain'); ?></label></th>
            <td>
                <input type="number" name="anno_iscrizione" id="anno_iscrizione" value="<?php echo esc_attr($anno_iscrizione); ?>" class="regular-text" />
                <br/>
                <span class="description"><?php _e('Inserisci l\'anno di iscrizione per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_anno_iscrizione');
add_action('edit_user_profile', 'aggiungi_metabox_anno_iscrizione');

// Salva l'anno di iscrizione quando il profilo utente viene aggiornato
function salva_anno_iscrizione_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva l'anno di iscrizione
    $anno_iscrizione = isset($_POST['anno_iscrizione']) ? intval($_POST['anno_iscrizione']) : '';
    update_user_meta($user_id, 'anno_iscrizione', $anno_iscrizione);
}
add_action('personal_options_update', 'salva_anno_iscrizione_utente');
add_action('edit_user_profile_update', 'salva_anno_iscrizione_utente');

// Mostra il metabox nella pagina di modifica dell'utente per la tassonomia lingue
function aggiungi_metabox_tassonomia_lingue($user) {
    // Ottieni tutti i termini della tassonomia
    $terms = get_terms(array(
        'taxonomy' => 'lingue',
        'hide_empty' => false,
    ));
    
    // Ottieni i termini assegnati all'utente
    //$user_terms = wp_get_object_terms($user->ID, 'lingue', array('fields' => 'ids'));
    $user_terms_str = get_user_meta( $user->ID, 'lingue',  true);
    $user_terms = array_map('intval', (array) $user_terms_str);
    ?>
    <h3><?php _e('Lingue', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="lingue"><?php _e('Seleziona Lingue', 'textdomain'); ?></label></th>
            <td>
                <select name="lingue[]" id="lingue" multiple="multiple">
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php echo in_array($term->term_id, $user_terms) ? 'selected' : ''; ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br/>
                <span class="description"><?php _e('Seleziona le lingue per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_tassonomia_lingue');
add_action('edit_user_profile', 'aggiungi_metabox_tassonomia_lingue');

// Salva le lingue scelte quando il profilo utente viene aggiornato
function salva_tassonomia_lingue_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e assegna i termini
    $lingue = isset($_POST['lingue']) ? $_POST['lingue'] : array();
    $lingue = array_map('intval', $lingue);
    //wp_set_object_terms($user_id, $lingue, 'lingue', false);
    update_user_meta( $user_id, 'lingue', $lingue );
}
add_action('personal_options_update', 'salva_tassonomia_lingue_utente');
add_action('edit_user_profile_update', 'salva_tassonomia_lingue_utente');


// Mostra il metabox nella pagina di modifica dell'utente per le sottoscrizioni
function aggiungi_metabox_sottoscrizioni_($user) {
    // Ottieni le sottoscrizioni assegnate all'utente
    $user_sottoscrizioni = get_user_meta($user->ID, 'sottoscrizioni', true);
    $user_sottoscrizioni = (array) $user_sottoscrizioni;
    
    ?>
    <h3><?php _e('Sottoscrizioni Attive', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="sottoscrizioni"><?php _e('Sottoscrizioni Attive', 'textdomain'); ?></label></th>
            <td>
                <ul>
                    <?php foreach ($user_sottoscrizioni as $sottoscrizione) : ?>
                        <li><?php echo esc_html($sottoscrizione); ?></li>
                    <?php endforeach; ?>
                </ul>
                <br/>
                <span class="description"><?php _e('Queste sono le sottoscrizioni attive per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
//add_action('show_user_profile', 'aggiungi_metabox_sottoscrizioni');
//add_action('edit_user_profile', 'aggiungi_metabox_sottoscrizioni');

// Mostra il metabox nella pagina di modifica dell'utente per le sottoscrizioni
function aggiungi_metabox_sottoscrizioni($user) {
    // Ottieni le sottoscrizioni assegnate all'utente
    $user_sottoscrizioni = get_user_meta($user->ID, 'sottoscrizioni', true);
    $user_sottoscrizioni = is_array($user_sottoscrizioni) ? $user_sottoscrizioni : array();
    
    ?>
    <h3><?php _e('Sottoscrizioni Attive', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="sottoscrizioni"><?php _e('Sottoscrizioni Attive', 'textdomain'); ?></label></th>
            <td>
                <ul>
                    <?php foreach ($user_sottoscrizioni as $sottoscrizione) : ?>
                        <li>
                            <?php try {
                                $sottoscrizione = (array) $sottoscrizione;
                            } catch (Exception $e) {
                                $sottoscrizione = array();
                            } ?>
                            <strong><?php echo esc_html($sottoscrizione['id']); ?></strong><br>
                            <?php _e('Stato:', 'textdomain'); ?> <?php echo esc_html($sottoscrizione['stato']); ?><br>
                            <?php _e('Descrizione:', 'textdomain'); ?> <?php echo esc_html($sottoscrizione['descrizione']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <br/>
                <span class="description"><?php _e('Queste sono le sottoscrizioni attive per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_sottoscrizioni');
add_action('edit_user_profile', 'aggiungi_metabox_sottoscrizioni');

// Salva le sottoscrizioni quando il profilo utente viene aggiornato
function salva_sottoscrizioni_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva le sottoscrizioni
    $sottoscrizioni = isset($_POST['sottoscrizioni']) ? $_POST['sottoscrizioni'] : array();
    $sottoscrizioni = array_map(function($sottoscrizione) {
        return array(
            'nome' => sanitize_text_field($sottoscrizione['nome']),
            'stato' => sanitize_text_field($sottoscrizione['stato']),
            'descrizione' => sanitize_textarea_field($sottoscrizione['descrizione']),
        );
    }, $sottoscrizioni);
    update_user_meta($user_id, 'sottoscrizioni', $sottoscrizioni);
}
add_action('personal_options_update', 'salva_sottoscrizioni_utente');
add_action('edit_user_profile_update', 'salva_sottoscrizioni_utente');


function ha_sottoscrizioni_attive($user_id) {
    $sottoscrizioni = get_user_meta($user_id, 'sottoscrizioni', true);
    $sottoscrizioni = is_array($sottoscrizioni) ? (array) $sottoscrizioni : array();
    foreach ($sottoscrizioni as $sottoscrizione) {
        if ($sottoscrizione['stato'] == 'active') {
            return true;
        }
    }
}

function get_sottoscrizione_sospesa($user_id) {
    $sottoscrizioni = get_user_meta($user_id, 'sottoscrizioni', true);
    $sottoscrizioni = is_array($sottoscrizioni) ? (array) $sottoscrizioni : array();
    foreach ($sottoscrizioni as $sottoscrizione) {
        if ($sottoscrizione['stato'] == 'suspend') {
            return $sottoscrizione;
        }
    }
}

function ha_sottoscrizioni_sospese($user_id) {
    $sottoscrizioni = get_user_meta($user_id, 'sottoscrizioni', true);
    $sottoscrizioni = is_array($sottoscrizioni) ? (array) $sottoscrizioni : array();
    foreach ($sottoscrizioni as $sottoscrizione) {
        if ($sottoscrizione['stato'] == 'suspend') {
            return true;
        }
    }
    return false;
}

function get_lista_sottoscrizioni_attive($user_id) {
    $sottoscrizioni = get_user_meta($user_id, 'sottoscrizioni', true);
    $sottoscrizioni = is_array($sottoscrizioni) ? (array) $sottoscrizioni : array();
    $sottoscrizioni_attive = array();
    foreach ($sottoscrizioni as $sottoscrizione) {
        if ($sottoscrizione['stato'] == 'active') {
            $sottoscrizioni_attive[] = $sottoscrizione;
        }
    }
    return $sottoscrizioni_attive;
}

// Mostra il metabox nella pagina di modifica dell'utente per l'email PayPal
function aggiungi_metabox_paypal_email($user) {
    // Ottieni l'email PayPal dell'utente
    $paypal_email = get_user_meta($user->ID, 'paypal_email', true);
    // Ottieni lo stato di conferma dell'email PayPal
    $paypal_email_confermata = get_user_meta($user->ID, 'paypal_email_confermata', true);
    ?>
    <h3><?php _e('Email PayPal', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="paypal_email"><?php _e('Email PayPal', 'textdomain'); ?></label></th>
            <td>
                <input type="email" name="paypal_email" id="paypal_email" value="<?php echo esc_attr($paypal_email); ?>" class="regular-text" />
                <br/>
                <span class="description"><?php _e('Inserisci l\'email PayPal per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="paypal_email_confermata"><?php _e('Email PayPal Confermata', 'textdomain'); ?></label></th>
            <td>
                <input type="checkbox" name="paypal_email_confermata" id="paypal_email_confermata" value="1" <?php checked($paypal_email_confermata, 1); ?> />
                <br/>
                <span class="description"><?php _e('Seleziona se l\'email PayPal è stata confermata.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_paypal_email');
add_action('edit_user_profile', 'aggiungi_metabox_paypal_email');

// Salva l'email PayPal e lo stato di conferma quando il profilo utente viene aggiornato
function salva_paypal_email_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva l'email PayPal
    $paypal_email = isset($_POST['paypal_email']) ? sanitize_email($_POST['paypal_email']) : '';
    update_user_meta($user_id, 'paypal_email', $paypal_email);

    // Verifica e salva lo stato di conferma dell'email PayPal
    $paypal_email_confermata = isset($_POST['paypal_email_confermata']) ? 1 : 0;
    update_user_meta($user_id, 'paypal_email_confermata', $paypal_email_confermata);
}
add_action('personal_options_update', 'salva_paypal_email_utente');
add_action('edit_user_profile_update', 'salva_paypal_email_utente');

// Funzione per ottenere lo stato di conferma dell'email PayPal di un utente
function get_paypal_email_confermata($user_id) {
    return get_user_meta($user_id, 'paypal_email_confermata', true);
}

// Funzione per impostare lo stato di conferma dell'email PayPal di un utente
function set_paypal_email_confermata($user_id, $paypal_email_confermata) {
    update_user_meta($user_id, 'paypal_email_confermata', $paypal_email_confermata ? 1 : 0);
}

function get_user_approval_paypal_expiration($user_id) {
    return get_user_meta($user_id, 'approval_paypal_expiration', true);
}

function set_user_approval_paypal_expiration($user_id, $expiration) {
    if (!is_numeric($expiration) || intval($expiration) <= 0) {
        wp_send_json_error(['message' => 'Expiration non è un timestamp valido.']);
        wp_die();
    }
    return update_user_meta($user_id, 'approval_paypal_expiration', sanitize_text_field($expiration));
}

function has_user_approval_paypal_expiration($user_id) {
    return !empty(get_user_meta($user_id, 'approval_paypal_expiration', true));
}

function delete_user_approval_paypal_expiration($user_id) {
    delete_user_meta($user_id, 'approval_paypal_expiration');
}

function get_user_paypal_email_temporary($user_id) {
    return get_user_meta($user_id, 'paypal_email_temporary', true);
}

function set_user_paypal_email_temporary($user_id, $email) {
    if (!valida_email($email)) {
        wp_send_json_error(['message' => 'Email non valida.']);
        wp_die();
    }
    return update_user_meta($user_id, 'paypal_email_temporary', sanitize_email($email));
}

function delete_user_paypal_email_temporary($user_id) {
    delete_user_meta($user_id, 'paypal_email_temporary');
}



// Mostra il metabox nella pagina di modifica dell'utente per la nazione
function aggiungi_metabox_nazione($user) {
    // Ottieni la nazione dell'utente
    $nazione = get_user_meta($user->ID, 'nazione', true);

    // Lista delle nazioni con i codici
    $nazioni = getNationArray(); 
    ?>
    <h3><?php _e('Nazione', 'textdomain'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="nazione"><?php _e('Seleziona Nazione', 'textdomain'); ?></label></th>
            <td>
                <select name="nazione" id="nazione">
                    <?php foreach ($nazioni as $codice => $nome) : ?>
                        <option value="<?php echo esc_attr($codice); ?>" <?php selected($nazione, $codice); ?>>
                            <?php echo esc_html($nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br/>
                <span class="description"><?php _e('Seleziona la nazione per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}

add_action('show_user_profile', 'aggiungi_metabox_nazione');
add_action('edit_user_profile', 'aggiungi_metabox_nazione');

// Salva la nazione quando il profilo utente viene aggiornato
function salva_nazione_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva la nazione
    $nazione = isset($_POST['nazione']) ? sanitize_text_field($_POST['nazione']) : '';
    update_user_meta($user_id, 'nazione', $nazione);
}
add_action('personal_options_update', 'salva_nazione_utente');
add_action('edit_user_profile_update', 'salva_nazione_utente');

// Mostra il metabox nella pagina di modifica dell'utente per la lingua
function aggiungi_metabox_lingua($user) {
    // Ottieni la lingua dell'utente
    $lingua = get_user_meta($user->ID, 'lingua', true);
    $lingue = getLanguagesArray();
    ?>
    <h3><?php _e('Lingua', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="lingua"><?php _e('Seleziona Lingua', 'textdomain'); ?></label></th>
            <td>
                <select name="lingua" id="lingua">
                    <option value=""><?php _e('Seleziona una lingua', 'textdomain'); ?></option>
                    <?php foreach ($lingue as $codice => $nome) : ?>
                        <option value="<?php echo esc_attr($codice); ?>" <?php selected($lingua, $codice); ?>>
                            <?php echo esc_html($nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br/>
                <span class="description"><?php _e('Seleziona la lingua per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_lingua');
add_action('edit_user_profile', 'aggiungi_metabox_lingua');

// Salva la lingua quando il profilo utente viene aggiornato
function salva_lingua_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva la lingua
    $lingua = isset($_POST['lingua']) ? sanitize_text_field($_POST['lingua']) : '';
    update_user_meta($user_id, 'lingua', $lingua);
}
add_action('personal_options_update', 'salva_lingua_utente');
add_action('edit_user_profile_update', 'salva_lingua_utente');

// Mostra il metabox nella pagina di modifica dell'utente per la verifica email
function aggiungi_metabox_verifica_email($user) {
    // Ottieni lo stato di verifica email dell'utente
    $email_verificata = get_user_meta($user->ID, 'email_verificata', true);
    ?>
    <h3><?php _e('Verifica Email', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="email_verificata"><?php _e('Email Verificata', 'textdomain'); ?></label></th>
            <td>
                <input type="checkbox" name="email_verificata" id="email_verificata" value="1" <?php checked($email_verificata, 1); ?> />
                <br/>
                <span class="description"><?php _e('Seleziona se l\'email dell\'utente è stata verificata.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_verifica_email');
add_action('edit_user_profile', 'aggiungi_metabox_verifica_email');

// Salva lo stato di verifica email quando il profilo utente viene aggiornato
function salva_verifica_email_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva lo stato di verifica email
    $email_verificata = isset($_POST['email_verificata']) ? 1 : 0;
    update_user_meta($user_id, 'email_verificata', $email_verificata);
}
add_action('personal_options_update', 'salva_verifica_email_utente');
add_action('edit_user_profile_update', 'salva_verifica_email_utente');


function verifica_password_login($user, $username, $password) {
    // error_log("verifica_password_login - Verifica password: " . print_r($user, true));
    if (empty($username) || is_wp_error($user) || !$user) {
        error_log("verifica_password_login - Utente non trovato");
        return $user;
    }

    // Verifica la password dell'utente
    if (!wp_check_password($password, $user->data->user_pass, $user->ID)) {
        error_log("verifica_password_login - Password non corretta");
        return new WP_Error('incorrect_credentials', __('Username non valido o password non corretta. Riprova.', 'textdomain'));
    }

    error_log("verifica_password_login - Password corretta");
    return $user;
}

add_filter('effettua_verifiche_utente', 'verifica_password_login' , 24, 3);



function controlla_email_verificata_login($user, $username, $password) {
    // error_log("controlla_email_verificata_login - Verifica email: " . print_r($user, true));
    if (empty($username) || is_wp_error($user) || !$user) {
        error_log("controlla_email_verificata_login - Utente non trovato");
        return $user;
    }

    // error_log("Autenticazione: " . print_r($user, true));
    
    // error_log("controlla_email_verificata_login - Verifica email: " . print_r($user, true));
    $email_verificata = get_email_verificata($user->ID);
    if (!$email_verificata) {
        error_log("controlla_email_verificata_login - Email non verificata");
        return new WP_Error('email_not_verified', 'L\'email non è verificata. Controlla la tua casella di posta.');
    }

    error_log("controlla_email_verificata_login - Email verificata");   
    return $user;
}


add_filter('effettua_verifiche_utente', 'controlla_email_verificata_login' , 25, 3);


function verifica_esistenza_utente($user, $username, $password) {
        // Trova l'utente tramite l'indirizzo email
        error_log('USERNAME: ' . $username);
        $user = get_user_by('login', $username);
        if (!$user) {
            $user = get_user_by('email', $username);
            if(!$user){
                $user = new WP_Error('incorrect_credentials', __('Username non valido o password non corretta. Riprova.', 'textdomain'));
            }
        }

        return $user;
}

add_filter('effettua_verifiche_utente', 'verifica_esistenza_utente' , 22, 3);
/*
add_action('init', function() {
    global $wp_filter;

    if (isset($wp_filter['authenticate'])) {
        error_log(print_r($wp_filter['authenticate'], true));
    } else {
        error_log('Nessun filtro registrato per authenticate.');
    }
});*/



// Funzione AJAX per ottenere lo stato di verifica email dato l'indirizzo email
/*function ajax_get_email_verificata() {
    // Verifica il nonce per la sicurezza
    check_ajax_referer('verifica_email', 'verifica_email_nonce');

    // Ottieni l'indirizzo email dalla richiesta
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';

    // Trova l'utente tramite l'indirizzo email
    $user = get_user_by('email', $email);
    if (!$user) {
        $username = sanitize_text_field($_POST['email']);
        $user = get_user_by('login', $username);
        if($user){
            $email = $user->user_email;
        }
    }
    
    if ($user) {
        $user = apply_filters('effettua_verifiche_utente', $user, $username, $password);
        error_log("Utente: " . print_r($user, true));
        // Verifica che la password sia corretta
        if (is_wp_error($user)) {
            error_log("Errore: " . print_r($user, true));
            wp_send_json_error(array('message' => __($user->errors['blocked'][0], 'textdomain')));
            // wp_send_json_error(array('message' => __('Utente non trovato o password non corretta.', 'textdomain')));
            exit;
        }

        // Ottieni lo stato di verifica email
        $email_verificata = boolval( get_email_verificata($user->ID));
        wp_send_json_success(array('email_verificata' => $email_verificata, 'email' => $email));
        exit;
    } else {
        wp_send_json_error(array('message' => __('Utente non trovato o password non corretta.', 'textdomain')));
        exit;
    }
}

// Registra la funzione AJAX per utenti autenticati e non autenticati
add_action('wp_ajax_get_email_verificata', 'ajax_get_email_verificata');
add_action('wp_ajax_nopriv_get_email_verificata', 'ajax_get_email_verificata');
*/

// Mostra il metabox nella pagina di modifica dell'utente per l'accettazione della privacy policy
function aggiungi_metabox_privacy_policy($user) {
    // Ottieni lo stato di accettazione della privacy policy e la data di accettazione
    $privacy_policy_accettata = get_user_meta($user->ID, 'privacy_policy_accettata', true);
    $data_accettazione_privacy_policy = get_user_meta($user->ID, 'data_accettazione_privacy_policy', true);
    ?>
    <h3><?php _e('Privacy Policy', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="privacy_policy_accettata"><?php _e('Accettazione Privacy Policy', 'textdomain'); ?></label></th>
            <td>
                <input type="checkbox" name="privacy_policy_accettata" id="privacy_policy_accettata" value="1" <?php checked($privacy_policy_accettata, 1); ?> />
                <br/>
                <span class="description"><?php _e('Seleziona se l\'utente ha accettato la privacy policy.', 'textdomain'); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="data_accettazione_privacy_policy"><?php _e('Data di Accettazione', 'textdomain'); ?></label></th>
            <td>
                <input type="text" name="data_accettazione_privacy_policy" id="data_accettazione_privacy_policy" value="<?php echo esc_attr($data_accettazione_privacy_policy); ?>" class="regular-text" readonly />
                <br/>
                <span class="description"><?php _e('Data in cui l\'utente ha accettato la privacy policy.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_privacy_policy');
add_action('edit_user_profile', 'aggiungi_metabox_privacy_policy');

// Salva l'accettazione della privacy policy e la data quando il profilo utente viene aggiornato
function salva_privacy_policy_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva l'accettazione della privacy policy
    $privacy_policy_accettata = isset($_POST['privacy_policy_accettata']) ? 1 : 0;
    update_user_meta($user_id, 'privacy_policy_accettata', $privacy_policy_accettata);

    // Salva la data di accettazione se la privacy policy è stata accettata
    if ($privacy_policy_accettata) {
        $data_accettazione_privacy_policy = get_user_meta($user_id, 'data_accettazione_privacy_policy', true);
        if (empty($data_accettazione_privacy_policy)) {
            $data_accettazione_privacy_policy = current_time('mysql');
            update_user_meta($user_id, 'data_accettazione_privacy_policy', $data_accettazione_privacy_policy);
        }
    } else {
        delete_user_meta($user_id, 'data_accettazione_privacy_policy');
    }
}
add_action('personal_options_update', 'salva_privacy_policy_utente');
add_action('edit_user_profile_update', 'salva_privacy_policy_utente');

// Funzione per ottenere lo stato di accettazione della privacy policy di un utente
function get_privacy_policy_accettata($user_id) {
    return get_user_meta($user_id, 'privacy_policy_accettata', true);
}

// Funzione per impostare lo stato di accettazione della privacy policy di un utente
function set_privacy_policy_accettata($user_id, $privacy_policy_accettata) {
    update_user_meta($user_id, 'privacy_policy_accettata', $privacy_policy_accettata ? 1 : 0);
}

// Funzione per ottenere la data di accettazione della privacy policy di un utente
function get_data_accettazione_privacy_policy($user_id) {
    return get_user_meta($user_id, 'data_accettazione_privacy_policy', true);
}

// Funzione per impostare la data di accettazione della privacy policy di un utente
function set_data_accettazione_privacy_policy($user_id, $data_accettazione_privacy_policy) {
    update_user_meta($user_id, 'data_accettazione_privacy_policy', $data_accettazione_privacy_policy);
}

// Funzione per aggiungere il campo 'commissioni disattivate' al profilo utente
function add_commissioni_disattivate_field($user) {
    $commissioni_disattivate = get_user_meta($user->ID, 'commissioni_disattivate', true);
    ?>
    <h3><?php _e('Impostazioni Commissioni', 'your_textdomain'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="commissioni_disattivate"><?php _e('Disattiva Commissioni', 'your_textdomain'); ?></label></th>
            <td>
                <input type="checkbox" name="commissioni_disattivate" id="commissioni_disattivate" value="1" <?php checked($commissioni_disattivate, '1'); ?> />
                <span class="description"><?php _e('Seleziona per disattivare le commissioni.', 'your_textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'add_commissioni_disattivate_field');
add_action('edit_user_profile', 'add_commissioni_disattivate_field');

// Funzione per salvare il campo 'commissioni disattivate'
function save_commissioni_disattivate_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    update_user_meta($user_id, 'commissioni_disattivate', isset($_POST['commissioni_disattivate']) ? '1' : '0');
}
add_action('personal_options_update', 'save_commissioni_disattivate_field');
add_action('edit_user_profile_update', 'save_commissioni_disattivate_field');

// Funzione per ottenere il valore di 'commissioni disattivate'
function get_commissioni_disattivate($user_id) {
    return get_user_meta($user_id, 'commissioni_disattivate', true);
}

// Funzione per impostare il valore di 'commissioni disattivate'
function set_commissioni_disattivate($user_id, $value) {
    update_user_meta($user_id, 'commissioni_disattivate', $value ? '1' : '0');
}

// Mostra il metabox nella pagina di modifica dell'utente per la protezione login
function aggiungi_metabox_protezione_login($user) {
    // Ottieni la data di blocco dell'account e il login failure stage dell'utente
    $login_block_until = get_user_meta($user->ID, 'login_block_until', true);
    $login_failure_stage = get_user_meta($user->ID, 'login_failure_stage', true);
    ?>
    <h3><?php _e('Protezione Login', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="login_block_until"><?php _e('Data di Blocco Account', 'textdomain'); ?></label></th>
            <td>
                <input type="datetime-local" name="login_block_until" id="login_block_until" value="<?php echo esc_attr($login_block_until); ?>" class="regular-text" />
                <br/>
                <span class="description"><?php _e('Inserisci la data fino a quando l\'account sarà bloccato.', 'textdomain'); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="login_failure_stage"><?php _e('Login Failure Stage', 'textdomain'); ?></label></th>
            <td>
                <input type="number" name="login_failure_stage" id="login_failure_stage" value="<?php echo esc_attr($login_failure_stage); ?>" class="regular-text" />
                <br/>
                <span class="description"><?php _e('Inserisci il login failure stage per questo utente.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_protezione_login');
add_action('edit_user_profile', 'aggiungi_metabox_protezione_login');

// Salva la protezione login quando il profilo utente viene aggiornato
function salva_protezione_login_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva la data di blocco dell'account
    $login_block_until = isset($_POST['login_block_until']) ? sanitize_text_field($_POST['login_block_until']) : '';
    update_user_meta($user_id, 'login_block_until', $login_block_until);

    // Verifica e salva il login failure stage
    $login_failure_stage = isset($_POST['login_failure_stage']) ? intval($_POST['login_failure_stage']) : 0;
    update_user_meta($user_id, 'login_failure_stage', $login_failure_stage);
}
add_action('personal_options_update', 'salva_protezione_login_utente');
add_action('edit_user_profile_update', 'salva_protezione_login_utente');

// Funzione per ottenere la data di blocco dell'account di un utente
function get_login_block_until($user_id) {
    return get_user_meta($user_id, 'login_block_until', true);
}

// Funzione per impostare la data di blocco dell'account di un utente
function set_login_block_until($user_id, $login_block_until) {
    update_user_meta($user_id, 'login_block_until', $login_block_until);
}

// Funzione per ottenere il login failure stage di un utente
function get_login_failure_stage($user_id) {
    return get_user_meta($user_id, 'login_failure_stage', true);
}

// Funzione per impostare il login failure stage di un utente
function set_login_failure_stage($user_id, $login_failure_stage) {
    update_user_meta($user_id, 'login_failure_stage', intval($login_failure_stage));
}


// Mostra il metabox nella pagina di modifica dell'utente per l'account Google collegato
function aggiungi_metabox_account_google_collegato($user) {
    // Ottieni lo stato dell'account Google collegato dell'utente
    $account_google_collegato = get_user_meta($user->ID, 'account_google_collegato', true);
    ?>
    <h3><?php _e('Account Google Collegato', 'textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="account_google_collegato"><?php _e('Account Google Collegato', 'textdomain'); ?></label></th>
            <td>
                <input type="checkbox" name="account_google_collegato" id="account_google_collegato" value="1" <?php checked($account_google_collegato, 1); ?> />
                <br/>
                <span class="description"><?php _e('Seleziona se l\'account Google è collegato.', 'textdomain'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'aggiungi_metabox_account_google_collegato');
add_action('edit_user_profile', 'aggiungi_metabox_account_google_collegato');

// Salva lo stato dell'account Google collegato quando il profilo utente viene aggiornato
function salva_account_google_collegato_utente($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Verifica e salva lo stato dell'account Google collegato
    $account_google_collegato = isset($_POST['account_google_collegato']) ? 1 : 0;
    update_user_meta($user_id, 'account_google_collegato', $account_google_collegato);
}
add_action('personal_options_update', 'salva_account_google_collegato_utente');
add_action('edit_user_profile_update', 'salva_account_google_collegato_utente');

// Funzione per ottenere lo stato dell'account Google collegato di un utente
function get_account_google_collegato($user_id) {
    $account_collegato = get_user_meta($user_id, 'account_google_collegato', true);
    if(isset($account_collegato) && $account_collegato != ''){
        return $account_collegato;
    } else {
        return false;
    }
}

// Funzione per impostare lo stato dell'account Google collegato di un utente
function set_account_google_collegato($user_id, $account_google_collegato) {
    update_user_meta($user_id, 'account_google_collegato', $account_google_collegato ? 1 : 0);
}
