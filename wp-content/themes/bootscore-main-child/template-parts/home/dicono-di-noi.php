<?php
/**
 * Template part to display the testimonials section header
 *
 * @package Bootscore
 * @version 6.0.0
 */
?>

<div class="testimonials-header text-center mb-5">
    <h2 class="display-4 fw-bold mb-3">Ciò che dicono di noi</h2>
    <h3 class="h5 text-muted fw-light mb-4">Alcune delle oltre 1000 recensioni</h3>
    <div class="testimonials-divider">
        <span class="testimonials-divider-line"></span>
        <i class="fas fa-star testimonials-divider-icon"></i>
        <span class="testimonials-divider-line"></span>
    </div>
</div>

<style>
.testimonials-header {
    position: relative;
}

.testimonials-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
}

.testimonials-divider-line {
    height: 2px;
    width: 100px;
    background: linear-gradient(90deg, transparent, var(--primary), transparent);
}

.testimonials-divider-icon {
    color: var(--primary);
    font-size: 1.25rem;
}

@media (max-width: 767.98px) {
    .testimonials-header h2 {
        font-size: 2rem;
    }
    
    .testimonials-divider-line {
        width: 60px;
    }
}
</style>


