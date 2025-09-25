<?php
/**
 * Template part to display the testimonials slider
 *
 * @package Bootscore
 * @version 6.0.0
 */

$recensioni = array(
    array(
        'titolo' => 'da Silvia',
        'testo' => 'Ragazzi super disponibili. Servizio top! consiglio',
        'stelle' => "4.5",
        'url' => "https://t.me/panieri_unipegaso/531/20497"
    ),
    array(
        'titolo' => 'da Davide',
        'testo' => 'Super disponibili, competenti, veloci, gentili! Che dire, mi sono trovato molto bene e sicuramente mi affiderò a loro per la magistrale! 🔝 ❤️',
        'stelle' => "5",
        'url' => "https://t.me/panieri_unipegaso/531/24679"
    ),
    array(
        'titolo' => 'da Alessandro',
        'testo' => 'Super disponibili, e veloci con le richieste </br>Grandiosii❤️',
        'stelle' => "5",
        'url' => "https://t.me/panieri_unipegaso/531/24577"
    ),
    array(
        'titolo' => 'da Gianluca',
        'testo' => 'Super efficienti e rapidissimi nel contattarti per fornirti quanto richiesto. 🔝',
        'stelle' => "4.5",
        'url' => "https://t.me/panieri_unipegaso/531/24201"
    ),
    array(
        'titolo' => 'da Max',
        'testo' => 'Consigliatissimi. Velocità e serietà!',
        'stelle' => "4",
        'url' => "https://t.me/panieri_unipegaso/531/20497"
    ),
    array(
        'titolo' => 'da Francesca',
        'testo' => 'Favolosi 🥰 gentili, disponibilissimi e super efficienti..grazie davvero',
        'stelle' => "5",
        'url' => "https://t.me/panieri_unipegaso/531/20025"
    )
);
?>

<div class="testimonials-slider">
    <div class="testimonials-slider__wrapper">
        <div class="testimonials-slider__track">
            <?php foreach($recensioni as $recensione): ?>
                <div class="testimonials-slider__slide">
                    <?php get_template_part('template-parts/home/recensione', null, array('recensione' => $recensione)); ?>
                </div>
            <?php endforeach; ?>
            
            <?php foreach($recensioni as $recensione): ?>
                <div class="testimonials-slider__slide">
                    <?php get_template_part('template-parts/home/recensione', null, array('recensione' => $recensione)); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
:root {
    --testimonials-slides: <?php echo count($recensioni) * 2; ?>;
    --testimonials-slides-view: 4;
    --testimonials-slide-width: calc(100% / var(--testimonials-slides-view));
    --testimonials-animation-duration: 30s;
}

.testimonials-slider {
    position: relative;
    padding: 2rem 0;
    overflow: hidden;
}

.testimonials-slider__wrapper {
    width: calc(var(--testimonials-slides-view) * var(--testimonials-slide-width));
    margin: 0 auto;
    overflow: hidden;
}

.testimonials-slider__track {
    display: flex;
    animation: testimonials-scroll var(--testimonials-animation-duration) linear infinite;
}

.testimonials-slider__slide {
    flex: 0 0 var(--testimonials-slide-width);
    padding: 0 1rem;
}

@keyframes testimonials-scroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(calc(var(--testimonials-slide-width) * var(--testimonials-slides) * -1));
    }
}

@media (max-width: 991.98px) {
    :root {
        --testimonials-slides-view: 3;
    }
}

@media (max-width: 767.98px) {
    :root {
        --testimonials-slides-view: 2;
    }
}

@media (max-width: 575.98px) {
    :root {
        --testimonials-slides-view: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.testimonials-slider');
    const track = document.querySelector('.testimonials-slider__track');
    
    slider.addEventListener('mouseenter', () => {
        track.style.animationPlayState = 'paused';
    });
    
    slider.addEventListener('mouseleave', () => {
        track.style.animationPlayState = 'running';
    });
});
</script>