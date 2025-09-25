<?php
// Funzioni per le tassonomie
function get_taxonomy_terms($post_id, $taxonomy) {
    $terms = wp_get_post_terms($post_id, $taxonomy);
    if (!is_wp_error($terms) && !empty($terms)) {
        return wp_list_pluck($terms, 'name');
    }
    return [];
}

function set_taxonomy_terms($post_id, $taxonomy, $terms) {
    if (!is_array($terms)) {
        $terms = [$terms];
    }
    return wp_set_post_terms($post_id, $terms, $taxonomy);
}

function has_taxonomy_terms($post_id, $taxonomy) {
    $terms = get_taxonomy_terms($post_id, $taxonomy);
    return !empty($terms);
}

// Getter e setter per il titolo del post
function get_post_title($post_id) {
    return get_the_title($post_id);
}

function set_post_title($post_id, $title) {
    $post_data = [
        'ID' => $post_id,
        'post_title' => $title,
    ];
    return wp_update_post($post_data);
}

// Getter e setter per il contenuto del post
function get_post_content($post_id) {
    $post = get_post($post_id);
    return $post ? $post->post_content : '';
}

function set_post_content($post_id, $content) {
    $post_data = [
        'ID' => $post_id,
        'post_content' => $content,
    ];
    return wp_update_post($post_data);
}

// Esempio di utilizzo per le tassonomie specificate
function get_nome_istituto_terms($post_id) {
    return get_taxonomy_terms($post_id, 'nome_istituto');
}

function set_nome_istituto_terms($post_id, $terms) {
    return set_taxonomy_terms($post_id, 'nome_istituto', $terms);
}

function has_nome_istituto_terms($post_id) {
    return has_taxonomy_terms($post_id, 'nome_istituto');
}

// Ripeti lo stesso schema per le altre tassonomie
function get_nome_corso_di_laurea_terms($post_id) {
    return get_taxonomy_terms($post_id, 'nome_corso_di_laurea');
}

function set_nome_corso_di_laurea_terms($post_id, $terms) {
    return set_taxonomy_terms($post_id, 'nome_corso_di_laurea', $terms);
}

function has_nome_corso_di_laurea_terms($post_id) {
    return has_taxonomy_terms($post_id, 'nome_corso_di_laurea');
}

function get_nome_corso_terms($post_id) {
    return get_taxonomy_terms($post_id, 'nome_corso');
}

function set_nome_corso_terms($post_id, $terms) {
    return set_taxonomy_terms($post_id, 'nome_corso', $terms);
}

function has_nome_corso_terms($post_id) {
    return has_taxonomy_terms($post_id, 'nome_corso');
}

function get_anno_accademico_terms($post_id) {
    return get_taxonomy_terms($post_id, 'anno_accademico');
}

function set_anno_accademico_terms($post_id, $terms) {
    return set_taxonomy_terms($post_id, 'anno_accademico', $terms);
}

function has_anno_accademico_terms($post_id) {
    return has_taxonomy_terms($post_id, 'anno_accademico');
}

function get_tipo_prodotto_terms($post_id) {
    return get_taxonomy_terms($post_id, 'tipo_prodotto');
}

function set_tipo_prodotto_terms($post_id, $terms) {
    return set_taxonomy_terms($post_id, 'tipo_prodotto', $terms);
}

function has_tipo_prodotto_terms($post_id) {
    return has_taxonomy_terms($post_id, 'tipo_prodotto');
}

function get_modalita_pubblicazione_terms($post_id) {
    return get_taxonomy_terms($post_id, 'modalita_pubblicazione');
}

function set_modalita_pubblicazione_terms($post_id, $terms) {
    return set_taxonomy_terms($post_id, 'modalita_pubblicazione', $terms);
}

function has_modalita_pubblicazione_terms($post_id) {
    return has_taxonomy_terms($post_id, 'modalita_pubblicazione');
}