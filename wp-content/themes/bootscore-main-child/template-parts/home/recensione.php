<?php
/**
 * Template part to display a single testimonial
 *
 * @package Bootscore
 * @version 6.0.0
 */

$recensione = $args['recensione'];
?>

<a href="<?php echo esc_url($recensione['url']); ?>" class="testimonial-card" target="_blank" rel="noopener">
    <div class="testimonial-card__content">
        <div class="testimonial-card__rating">
            <span class="testimonial-card__stars">
                <?php for($i = 0; $i < floor($recensione['stelle']); $i++): ?>
                    <i class="fas fa-star"></i>
                <?php endfor; ?>
                <?php if($recensione['stelle'] - floor($recensione['stelle']) >= 0.5): ?>
                    <i class="fas fa-star-half-alt"></i>
                <?php endif; ?>
            </span>
            <span class="testimonial-card__score"><?php echo esc_html($recensione['stelle']); ?></span>
        </div>
        <h5 class="testimonial-card__title"><?php echo esc_html($recensione['titolo']); ?></h5>
        <p class="testimonial-card__text"><?php echo wp_kses_post($recensione['testo']); ?></p>
    </div>
</a>

<style>
.testimonial-card {
    display: block;
    text-decoration: none;
    color: inherit;
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    height: 100%;
    transition: var(--transition);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-lg);
    color: inherit;
    text-decoration: none;
}

.testimonial-card__content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.testimonial-card__rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.testimonial-card__stars {
    color: var(--warning);
}

.testimonial-card__score {
    font-weight: 600;
    color: var(--gray-700);
}

.testimonial-card__title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.75rem;
}

.testimonial-card__text {
    color: var(--gray-600);
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 0;
}
</style>