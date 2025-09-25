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

$features = array(
    array(
        'icon' => 'university',
        'text' => $term_istituto,
        'link' => get_term_link($term_istituto, 'nome_istituto'),
        'color' => 'primary'
    ),
    array(
        'icon' => 'book',
        'text' => $term_corso,
        'link' => get_term_link($term_corso, 'nome_corso'),
        'color' => 'success'
    ),
    array(
        'icon' => 'star',
        'text' => $media_recensioni,
        'color' => 'warning'
    ),
    array(
        'icon' => 'calendar-alt',
        'text' => $term_anno,
        'link' => get_term_link($term_anno, 'anno_accademico'),
        'color' => 'info'
    ),
    array(
        'icon' => 'file-alt',
        'text' => $n_pagine.' pagine',
        'color' => 'secondary'
    ),
    array(
        'icon' => 'download',
        'text' => $numero_download.' Download',
        'color' => 'primary'
    ),
    array(
        'icon' => 'coins',
        'text' => $costo_punti.' '.$tipo_punti,
        'color' => 'warning'
    )
);
?>

<article id="<?php echo $post_hid; ?>" class="search-result-card">
    <div class="card border-0 shadow-sm hover-shadow">
        <div class="row g-0">
            <?php if (has_post_thumbnail()) : ?>
            <div class="col-12 col-md-3">
                <div class="position-relative">
                    <a href="<?php the_permalink(); ?>" class="d-block">
                        <?php the_post_thumbnail('img-item-search', array(
                            'class' => 'img-fluid rounded-start',
                            'style' => 'width: 100%; height: 100%; object-fit: cover; -webkit-object-fit: cover;'
                        )); ?>
                        <div class="overlay">
                            <span class="btn btn-primary">
                                <i class="fas fa-eye me-2"></i>
                                Visualizza
                            </span>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-12 col-md-9">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <?php bootscore_category_badge(); ?>
                            <h2 class="h4 mb-2">
                                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark hover-primary">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary d-none d-md-flex align-items-center gap-2" style="white-space: nowrap;">
                            <i class="fas fa-arrow-right"></i>
                            Vai al documento
                        </a>
                    </div>

                    <div class="features-grid">
                        <?php foreach ($features as $feature) : ?>
                        <div class="feature-item">
                            <div class="feature-icon bg-<?php echo $feature['color']; ?>-subtle">
                                <i class="fas fa-<?php echo $feature['icon']; ?> text-<?php echo $feature['color']; ?>"></i>
                            </div>
                            
                            <span class="feature-text"><?php echo esc_html($feature['text']); ?></span>

                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?php the_permalink(); ?>" class="btn btn-primary d-flex d-md-none align-items-center gap-2 justify-content-center">
                            <i class="fas fa-arrow-right"></i>
                            Vai al documento
                    </a>
                    <?php bootscore_tags(); ?>
                </div>
            </div>
        </div>
    </div>
</article>


