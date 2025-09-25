<?php

/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Bootscore
 * @version 6.0.0
 */


// Exit if accessed directly
defined('ABSPATH') || exit;
global $sistemiPunti;

$term_istituto = wp_get_object_terms(get_the_ID(), 'nome_istituto');
$term_tipo_istituto = wp_get_object_terms(get_the_ID(), 'tipo_istituto');
$term_anno = wp_get_object_terms(get_the_ID(), 'anno_accademico');
$term_corso = wp_get_object_terms(get_the_ID(), 'nome_corso');
$modalita = wp_get_object_terms(get_the_ID(), 'modalita_pubblicazione')[0];
// $costo_punti = get_post_meta(get_the_ID(), '_costo_in_punti_pro', true);
$tipo_punti = "";
$icona_punti = "";
if ($modalita->slug == 'vendi') {
    $costo_punti = get_post_meta(get_the_ID(),'_costo_in_punti_pro', true);
    $tipo_punti = "Punti Pro";
    $icona_punti = basename($sistemiPunti['pro']->get_icon());
} else if ($modalita->slug == 'condividi') {
    $costo_punti = get_post_meta(get_the_ID(),'_costo_in_punti_blu', true);
    $tipo_punti = "Punti Blu";
    $icona_punti = basename($sistemiPunti['blu']->get_icon());
}
$n_pagine = get_post_meta(get_the_ID(), '_num_pagine', true);

$term_istituto = isset($term_istituto[0]) ? $term_istituto[0]->name : '';
$term_tipo_istituto = isset($term_tipo_istituto[0]) ? $term_tipo_istituto[0]->name : '';
$term_anno = isset($term_anno[0]) ? $term_anno[0]->name : '';
$term_corso = isset($term_corso[0]) ? $term_corso[0]->name : '';
$n_pagine = $n_pagine ? $n_pagine : '1';
$costo_punti = $costo_punti ? $costo_punti : '';

$current_product = wc_get_product(get_the_ID());
$media_recensioni = 0; 
if ($current_product) {
    $media_recensioni = $current_product->get_average_rating();
}

$info_recensioni = get_product_review_info($post->ID);
$media_recensioni = $info_recensioni['average_rating'];
$n_recensioni = $info_recensioni['total_reviews'];
if ($media_recensioni == 0 or $media_recensioni == null) {
    $media_recensioni = "Nessuna recensione";
} else {
    $media_recensioni = number_format($media_recensioni, 1, '.', '');
}

$numero_download = get_product_purchase_count($post->ID) ?? 0;
$post_hid = get_product_hash_id($post->ID);

$features = array (
    array('icon' => 'istituto.svg', 'text' => $term_istituto , 'link' => get_term_link( $term_istituto , 'nome_istituto' ) ),
    array('icon' => 'libro.svg', 'text' => $term_corso , 'link' => get_term_link( $term_corso , 'nome_corso' )),
    array('icon' => 'stella.svg', 'text' => $media_recensioni),
    array('icon' => 'calendario.svg', 'text' => $term_anno , 'link' => get_term_link( $term_anno , 'anno_accademico' )),
    array('icon' => 'penna.svg', 'text' => $n_pagine.' pagine'),
    array('icon' => 'download.svg', 'text' => $numero_download.' Download'),
    array('icon' => $icona_punti, 'text' => $costo_punti.' '.$tipo_punti),
)


?>


<li class="list-group-item list-group-item-action">
    <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark">
        <div class="d-flex align-items-center">
            <img src="<?php the_post_thumbnail_url('img-item-search'); ?>" alt="<?php the_title(); ?>" class="img-fluid"
                                                style="width:50px; height:50px; margin-right:10px; border-radius: 5px;">
                                            <div class="d-flex flex-column">
                                                <h5 class="mb-1"><?php the_title(); ?></h5>
                                                <div class="d-flex flex-wrap justify-content-between  mt-2">
                                                    <span class="badge bg-primary mx-2"><?php echo $term_anno; ?></span>
                                                    <span class="badge bg-primary mx-2"><?php echo $term_corso; ?></span>
                                                    <span class="badge bg-primary mx-2"><?php echo $term_corso_di_laurea; ?></span>
                                                    <span class="badge bg-primary mx-2"><?php echo $term_istituto; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
