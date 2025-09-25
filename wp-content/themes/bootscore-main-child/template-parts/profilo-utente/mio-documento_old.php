<?php
    $sezione = $args['sezione'];
    $documento = $args['documento'];
    $term_istituto_documento = wp_get_object_terms($documento->ID, 'nome_istituto')[0] ?? (object) ['name' => 'N/A'];
    $rating = getPostAverageRating([$documento->ID]);
    if ($rating == 0) {
        $rating = '<span class="text-muted small">Ancora nessuna recensione</span>';
    } else {
        $rating = '<span class="text-warning">' . '⭐ ' . $rating . '</span>';
    }
?>

<div class="col-md-3 mb-4 d-flex">
    <div class="card h-100">
        <?php if (has_post_thumbnail($documento->ID)) : ?>
            <img src="<?php echo get_the_post_thumbnail_url($documento->ID, 'medium'); ?>" class="card-img-top" alt="Documento">
        <?php else : ?>
            <img src="default-thumbnail.jpg" class="card-img-top" alt="Documento">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
            <h6 class="card-title text-truncate mb-2"><?php echo $documento->post_title; ?></h6>
            <p class="card-text text-muted small"><?php echo $term_istituto_documento->name; ?></p>
            <div class="mt-auto">
                <a href="<?php echo get_permalink($documento->ID); ?>" class="btn btn-outline-primary btn-sm w-100">Apri documento</a>
            </div>
        </div>
    </div>
</div>
