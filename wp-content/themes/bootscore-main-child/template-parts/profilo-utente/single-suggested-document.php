<?php
    $sezione = $args['sezione'];
    $documento = $args['documento'];
    $term_istituto_documento = wp_get_object_terms($documento->ID, 'nome_istituto')[0] ?? (object) ['name' => 'N/A'];
    $rating = getPostAverageRating([$documento->ID]);
    if ($rating == 0) {
        $rating = '<span class="text-muted">Ancora nessuna recensione</span>';
    } else {
        $rating = '<span class="text-warning">' . '⭐ ' . $rating . '</span>';
    }
?>

<div class="col-md-3-single-doc mb-4">
    <div class="card h-100 border-0">
        <?php if (has_post_thumbnail($documento->ID)) : ?>
        <img src="<?php echo get_the_post_thumbnail_url($documento->ID, 'medium'); ?>" class="card-img-top p-0" alt="Documento">
        <?php else : ?>
        <img src="default-thumbnail.jpg" class="card-img-top p-0" alt="Documento">
        <?php endif; ?>
        <div class="card-body">
            <h6 class="card-title-single-doc text-truncate"><?php echo $documento->post_title; ?></h6>
            <p class="card-text-single-doc text-muted small mb-2"><?php echo $term_istituto_documento->name; ?></p>
            <a href="<?php echo get_permalink($documento->ID); ?>" class="btn btn-outline-primary-single-doc btn-sm">Sblocca</a>
        </div>
    </div>
</div>

<!-- CSS -->
<style>
    .card {
        min-width: 250px; /* Larghezza minima */
        max-width: 300px; /* Larghezza massima */
        flex: 1 1 auto; /* Permette il ridimensionamento fluido */
        border: none; /* Rimuove bordi aggiuntivi */
        background-color: transparent; /* Sfondo trasparente */
    }

    .card-img-top {
        border-radius: 20px; /* Bordi arrotondati */
        border: 5px solid #38C68B; /* Mantieni il colore del bordo */
        padding: 6px; /* Spaziatura attorno all'immagine */
        object-fit: cover; /* Garantisce che l'immagine si adatti */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Aggiunge ombra solo all'immagine */
    }

    .card-body {
        padding: 1rem;
        text-align: left; /* Allinea il testo al centro */
        background-color: transparent; /* Sfondo trasparente */
    }

    .card-title-single-doc {
        font-size: 16px; /* Dimensione del titolo */
        font-weight: 600; /* Testo in grassetto */
        color: #0066cc; /* Colore del testo */
        margin-bottom: 8px; /* Spaziatura inferiore */
        white-space: nowrap; /* Testo su una sola riga */
        overflow: hidden; /* Nasconde il testo in eccesso */
        text-overflow: ellipsis; /* Troncamento con "..." */
    }

    .card-text-single-doc {
        font-size: 16px;
        color: #6E6E6E;
        margin-bottom: 16px;
    }

    .btn-outline-primary-single-doc {
        padding: 4px 8px;
        font-size: 14px;
        color: #1A6AFF; /* Colore del testo originale */
        font-weight: bold;
        border: 1px solid #1A6AFF; /* Colore del bordo */
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.3s ease;
        display: block;
        margin: 0 auto;
    }

    .btn-outline-primary-single-doc:hover {
        background-color: #ffffff; /* Sfondo bianco */
        color: #000000; /* Testo nero */
        border-color: #000000; /* Bordo nero */
    }

    .col-md-3-single-doc {
        display: flex;
        justify-content: center; /* Centra le card nella griglia */
    }
</style>
