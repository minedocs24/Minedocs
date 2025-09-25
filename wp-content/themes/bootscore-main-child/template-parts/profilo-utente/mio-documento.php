<?php
    $sezione = $args['sezione'];
    $documento = $args['documento'];
    $term_istituto_documento = wp_get_object_terms($documento->ID, 'nome_istituto')[0] ?? (object) ['name' => 'N/A'];
    $rating = getPostAverageRating([$documento->ID]);
    $rating_display = $rating > 0 ? '⭐ ' . $rating : '<span class="text-muted small">Ancora nessuna recensione</span>';
    $classe_bordo = $args['classe-bordo'] ?? '';
    $classe_button = $args['classe-button'] ?? 'btn-primary';
    $testo_button = $args['testo-button'] ?? 'Apri documento';
    $icona_button = $args['icona-button'] ?? 'fas fa-file-alt';
?>

<div class="mb-4">
    <div class="card card-mini-documento h-100 shadow-sm border-0">
        <div class="position-relative">
            <?php if (has_post_thumbnail($documento->ID)) : ?>
                <img src="<?php echo get_the_post_thumbnail_url($documento->ID, 'medium'); ?>" class="card-img-top card-img-top-mini-documento <?php echo esc_attr($classe_bordo); ?>" alt="Documento">
            <?php else : ?>
                <img src="<?php echo esc_url(DEFAULT_THUMBNAIL); ?>" class="card-img-top-mini-documento" alt="Documento">
            <?php endif; ?>
            <div class="overlay-mini-documento" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
                <div class="overlay-text-mini-documento text-white"><?php echo wp_trim_words(get_the_content(null, null, $documento->ID), 20, '...'); ?></div>
            </div>
            <?php if ($rating > 0) : ?>
                <span class="badge bg-dark position-absolute bottom-0 end-0 m-2"><?php echo $rating_display; ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <h6 class="card-title text-truncate mb-1"><?php echo esc_html($documento->post_title); ?></h6>
            <p class="card-text text-muted small mb-0" style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"><?php echo esc_html($term_istituto_documento->name); ?></p>
            <div class="button-mini-documento">
                <a href="<?php echo esc_url(get_permalink($documento->ID)); ?>" class="btn <?php echo esc_attr($classe_button); ?> btn-sm w-100"><i class="<?php echo esc_attr($icona_button); ?>"></i> <?php echo esc_html($testo_button); ?></a>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.card-mini-documento').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.querySelector('.overlay-mini-documento').style.opacity = '1';
        });
        card.addEventListener('mouseleave', () => {
            card.querySelector('.overlay-mini-documento').style.opacity = '0';
        });
    });
</script>
