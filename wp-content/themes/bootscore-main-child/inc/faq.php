<?php
// Registra il Custom Post Type FAQ
function create_faq_cpt() {

    $labels = array(
        'name' => _x( 'FAQ', 'Nome Generale del Post Type', 'textdomain' ),
        'singular_name' => _x( 'FAQ', 'Nome Singolare del Post Type', 'textdomain' ),
        'menu_name' => _x( 'FAQ', 'Testo del Menu Admin', 'textdomain' ),
        'name_admin_bar' => _x( 'FAQ', 'Aggiungi Nuovo nella Toolbar', 'textdomain' ),
        'archives' => __( 'Archivio FAQ', 'textdomain' ),
        'attributes' => __( 'Attributi FAQ', 'textdomain' ),
        'parent_item_colon' => __( 'FAQ Genitore:', 'textdomain' ),
        'all_items' => __( 'Tutte le FAQ', 'textdomain' ),
        'add_new_item' => __( 'Aggiungi Nuova FAQ', 'textdomain' ),
        'add_new' => __( 'Aggiungi Nuova', 'textdomain' ),
        'new_item' => __( 'Nuova FAQ', 'textdomain' ),
        'edit_item' => __( 'Modifica FAQ', 'textdomain' ),
        'update_item' => __( 'Aggiorna FAQ', 'textdomain' ),
        'view_item' => __( 'Visualizza FAQ', 'textdomain' ),
        'view_items' => __( 'Visualizza FAQ', 'textdomain' ),
        'search_items' => __( 'Cerca FAQ', 'textdomain' ),
        'not_found' => __( 'Non trovato', 'textdomain' ),
        'not_found_in_trash' => __( 'Non trovato nel Cestino', 'textdomain' ),
        'featured_image' => __( 'Immagine in Evidenza', 'textdomain' ),
        'set_featured_image' => __( 'Imposta immagine in evidenza', 'textdomain' ),
        'remove_featured_image' => __( 'Rimuovi immagine in evidenza', 'textdomain' ),
        'use_featured_image' => __( 'Usa come immagine in evidenza', 'textdomain' ),
        'insert_into_item' => __( 'Inserisci nella FAQ', 'textdomain' ),
        'uploaded_to_this_item' => __( 'Caricato in questa FAQ', 'textdomain' ),
        'items_list' => __( 'Elenco FAQ', 'textdomain' ),
        'items_list_navigation' => __( 'Navigazione elenco FAQ', 'textdomain' ),
        'filter_items_list' => __( 'Filtra elenco FAQ', 'textdomain' ),
    );
    $args = array(
        'label' => __( 'FAQ', 'textdomain' ),
        'description' => __( 'Domande Frequenti', 'textdomain' ),
        'labels' => $labels,
        'supports' => array( 'title', 'editor' ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );
    register_post_type( 'faq', $args );

}
add_action( 'init', 'create_faq_cpt', 0 );

// Registra la Tassonomia Custom
function create_faq_category_taxonomy() {

    $labels = array(
        'name' => _x( 'Categorie FAQ', 'Nome Generale della Tassonomia', 'textdomain' ),
        'singular_name' => _x( 'Categoria FAQ', 'Nome Singolare della Tassonomia', 'textdomain' ),
        'menu_name' => __( 'Categorie FAQ', 'textdomain' ),
        'all_items' => __( 'Tutte le Categorie', 'textdomain' ),
        'parent_item' => __( 'Categoria Genitore', 'textdomain' ),
        'parent_item_colon' => __( 'Categoria Genitore:', 'textdomain' ),
        'new_item_name' => __( 'Nome Nuova Categoria', 'textdomain' ),
        'add_new_item' => __( 'Aggiungi Nuova Categoria', 'textdomain' ),
        'edit_item' => __( 'Modifica Categoria', 'textdomain' ),
        'update_item' => __( 'Aggiorna Categoria', 'textdomain' ),
        'view_item' => __( 'Visualizza Categoria', 'textdomain' ),
        'separate_items_with_commas' => __( 'Separa le categorie con le virgole', 'textdomain' ),
        'add_or_remove_items' => __( 'Aggiungi o rimuovi categorie', 'textdomain' ),
        'choose_from_most_used' => __( 'Scegli tra le più usate', 'textdomain' ),
        'popular_items' => __( 'Categorie Popolari', 'textdomain' ),
        'search_items' => __( 'Cerca Categorie', 'textdomain' ),
        'not_found' => __( 'Non Trovato', 'textdomain' ),
        'no_terms' => __( 'Nessuna categoria', 'textdomain' ),
        'items_list' => __( 'Elenco Categorie', 'textdomain' ),
        'items_list_navigation' => __( 'Navigazione elenco Categorie', 'textdomain' ),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
    );
    register_taxonomy( 'categoria_faq', array( 'faq' ), $args );

}
add_action( 'init', 'create_faq_category_taxonomy', 0 );



function get_faqs_json() {
    $faqs = array();
    $categories = get_terms(array(
        'taxonomy' => 'categoria_faq',
        'hide_empty' => true,
    ));

    foreach ($categories as $category) {
        $faqs[$category->slug] = array(
            'name' => $category->name,
            'description' => $category->description,
            'faqs' => array()
        );
        $query = new WP_Query(array(
            'post_type' => 'faq',
            'tax_query' => array(
                array(
                    'taxonomy' => 'categoria_faq',
                    'field' => 'term_id',
                    'terms' => $category->term_id,
                ),
            ),
            'post_status' => 'publish',
        ));

        while ($query->have_posts()) {
            $query->the_post();
            $faqs[$category->slug]['faqs'][] = array(
                'domanda' => get_the_title(),
                'domanda_slug' => get_post_field('post_name', get_post()),
                'risposta' => get_the_content(),
            );
        }
        wp_reset_postdata();
    }

    return json_encode($faqs);
}

