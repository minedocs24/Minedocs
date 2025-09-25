<?php

if ( ! function_exists( 'tipo_prodotto' ) ) {

    // Register Custom Taxonomy
    function tipo_prodotto() {
    
        $labels = array(
            'name'                       => 'Tipi prodotto',
            'singular_name'              => 'Tipo prodotto',
            'menu_name'                  => 'Tipi di prodotto',
            'all_items'                  => 'Tutti i tipi di prodotto',
            'parent_item'                => 'Parent Item',
            'parent_item_colon'          => 'Parent Item:',
            'new_item_name'              => 'Nuovo tipo di prodotto',
            'add_new_item'               => 'Aggiungi tipo di prodotto',
            'edit_item'                  => 'Modifica tipo di prodotto',
            'update_item'                => 'Aggiorna tipo di prodotto',
            'view_item'                  => 'Visualizza tipo di prodotto',
            'separate_items_with_commas' => 'Separate items with commas',
            'add_or_remove_items'        => 'Add or remove items',
            'choose_from_most_used'      => 'Choose from the most used',
            'popular_items'              => 'Popular Items',
            'search_items'               => 'Search Items',
            'not_found'                  => 'Not Found',
            'no_terms'                   => 'No items',
            'items_list'                 => 'Items list',
            'items_list_navigation'      => 'Items list navigation',
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => false,
        );
        register_taxonomy( 'tipo_prodotto', array( 'product' ), $args );
    
    }
    add_action( 'init', 'tipo_prodotto', 0 );
    
    }

    // Creazione della tassonomia 'Anno Accademico'
function crea_tassonomia_anno_accademico() {
    $labels = array(
        'name' => 'Anno Accademico',
        'singular_name' => 'Anno Accademico',
        'search_items' => 'Cerca Anno Accademico',
        'all_items' => 'Tutti gli Anni Accademici',
        'edit_item' => 'Modifica Anno Accademico',
        'update_item' => 'Aggiorna Anno Accademico',
        'add_new_item' => 'Aggiungi Nuovo Anno Accademico',
        'new_item_name' => 'Nuovo Anno Accademico',
    );
    
    register_taxonomy('anno_accademico', 'product', array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'anno-accademico'),
    ));
}
add_action('init', 'crea_tassonomia_anno_accademico');


// Creazione della tassonomia 'Tipo Istituto'
function crea_tassonomia_tipo_istituto() {
    $labels = array(
        'name' => 'Tipo Istituto',
        'singular_name' => 'Tipo Istituto',
    );
    
    register_taxonomy('tipo_istituto', 'product', array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'tipo-istituto'),
    ));
}
add_action('init', 'crea_tassonomia_tipo_istituto');

// Creazione della tassonomia 'Nome Istituto'
function crea_tassonomia_nome_istituto() {
    $labels = array(
        'name' => 'Nome Istituto',
        'singular_name' => 'Nome Istituto',
    );
    
    register_taxonomy('nome_istituto', array('product', 'user'), array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'nome-istituto'),
    ));
}
add_action('init', 'crea_tassonomia_nome_istituto');

// Creazione della tassonomia 'Nome Corso'
function crea_tassonomia_nome_corso() {
    $labels = array(
        'name' => 'Nome Corso',
        'singular_name' => 'Nome Corso',
    );
    
    register_taxonomy('nome_corso', 'product', array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'nome-corso'),
    ));
}
add_action('init', 'crea_tassonomia_nome_corso');

// Creazione della tassonomia 'Nome corso di laurea'
function crea_tassonomia_nome_corso_di_laurea() {
    $labels = array(
        'name' => 'Nome Corso di Laurea',
        'singular_name' => 'Nome Corso di Laurea',
    );
    
    register_taxonomy('nome_corso_di_laurea', 'product', array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'nome-corso-di-laurea'),
    ));
}
add_action('init', 'crea_tassonomia_nome_corso_di_laurea');

// Creazione della tassonomia 'Modalità Pubblicazione'
function crea_tassonomia_modalita_pubblicazione() {
    $labels = array(
        'name' => 'Modalità Pubblicazione',
        'singular_name' => 'Modalità Pubblcazione',
    );
    
    register_taxonomy('modalita_pubblicazione', array('product', 'user'), array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'modalita-pubblicazione'),
    ));
}
add_action('init', 'crea_tassonomia_modalita_pubblicazione');


// Creazione della tassonomia 'Lingue'
function crea_tassonomia_lingue() {
    $labels = array(
        'name' => 'Lingue',
        'singular_name' => 'Lingua',
        'search_items' => 'Cerca Lingue',
        'all_items' => 'Tutte le Lingue',
        'edit_item' => 'Modifica Lingua',
        'update_item' => 'Aggiorna Lingua',
        'add_new_item' => 'Aggiungi Nuova Lingua',
        'new_item_name' => 'Nuova Lingua',
    );
    
    register_taxonomy('lingue', array('product', 'post'), array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'lingue'),
    ));
}
add_action('init', 'crea_tassonomia_lingue');

// Aggiungi metadati ai termini della tassonomia 'Lingue'
function aggiungi_metadati_lingue() {
    $lingue = array(

        'Inglese' => '🇬🇧',
        'Italiano' => '🇮🇹',
        'Francese' => '🇫🇷',
        'Spagnolo' => '🇪🇸',
        'Tedesco' => '🇩🇪',
        'Cinese' => '🇨🇳',
        'Giapponese' => '🇯🇵',
        'Russo' => '🇷🇺'

    );

    foreach ($lingue as $lingua => $flag) {
        $term = term_exists($lingua, 'lingue');
        if ($term !== 0 && $term !== null) {
            update_term_meta($term['term_id'], 'flag', $flag);
        }
    }
}
//add_action('init', 'aggiungi_metadati_lingue');

// Aggiungi campo personalizzato per il meta 'flag' nella pagina di modifica del termine
function aggiungi_campo_personalizzato_flag($term) {
    $term_id = $term->term_id;
    $flag = get_term_meta($term_id, 'flag', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="flag"><?php _e('Flag'); ?></label>
        </th>
        <td>
            <input type="text" name="flag" id="flag" value="<?php echo esc_attr($flag) ? esc_attr($flag) : ''; ?>">
            <p class="description"><?php _e('Inserisci il codice della bandiera per questa lingua.'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('lingue_edit_form_fields', 'aggiungi_campo_personalizzato_flag');

// Salva il campo personalizzato 'flag' quando il termine viene aggiornato
function salva_campo_personalizzato_flag($term_id) {
    if (isset($_POST['flag'])) {
        update_term_meta($term_id, 'flag', sanitize_text_field($_POST['flag']));
    }
}
add_action('edited_lingue', 'salva_campo_personalizzato_flag');

// Creazione dei termini per la tassonomia 'Lingue'
function crea_termini_lingue() {
    $lingue = array(
        'Italiano',
        'Inglese',
        'Francese',
        'Spagnolo',
        'Tedesco',
        'Cinese',
        'Giapponese',
        'Russo'
    );

    foreach ($lingue as $lingua) {
        if (!term_exists($lingua, 'lingue')) {
            wp_insert_term($lingua, 'lingue');
        }
    }
}
//add_action('init', 'crea_termini_lingue');