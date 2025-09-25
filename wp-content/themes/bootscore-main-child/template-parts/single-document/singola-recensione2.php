<?php
$review = $args['review']; // Otteniamo i dati della recensione passati come argomento
$rating = get_comment_meta($review->comment_ID, 'rating', true); // Prendiamo il voto (1-5)
$avatar = get_avatar_url($review->comment_author_email, ['size' => 60]); // Otteniamo l'immagine profilo dell'autore
?>

<div class="review-card p-3 mb-3 bg-white rounded shadow-sm">
    <div class="d-flex align-items-center">
        <!-- Immagine autore -->
        <img src="<?php echo esc_url($avatar); ?>" class="rounded-circle me-3" width="50" height="50" alt="Avatar">

        <div>
            <h6 class="mb-1 fw-bold"><?php echo esc_html($review->comment_author); ?></h6>
            <p class="text-muted mb-0 small"><?php echo date_i18n('d M Y', strtotime($review->comment_date)); ?></p>
        </div>
    </div>

    <!-- Stelle della recensione -->
    <div class="review-stars mt-2">
        <?php for ($i = 1; $i <= 5; $i++) : ?>
            <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-secondary'; ?>"></i>
        <?php endfor; ?>
    </div>

    <!-- Contenuto della recensione -->
    <p class="mt-2 text-muted"><?php echo esc_html($review->comment_content); ?></p>
</div>
<style>
/* .review-card {
    border: 1px solid #ddd;
    transition: all 0.3s ease-in-out;
}

.review-card:hover {
    transform: scale(1.02);
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
}

.review-stars i {
    font-size: 18px;
}

.rounded-circle {
    border: 2px solid #ffc107;
} */
</style>