<?php
/**
 * Template part to display the call to action banner on the home page
 *
 * @package Bootscore
 * @version 6.0.0
 */

$elementi = array(
    array(
        "img" => "robot.webp",
        "text" => "Studia con l'AI",
        "shape_svg" => "shape-smooth1.svg",
        "size-classes" => "col-lg-4 col-md-6 mb-4 mb-lg-0"
    ),
    array(
        "img" => "books.webp",
        "text" => "Sfoglia i documenti",
        "shape_svg" => "shape-smooth2.svg",
        "size-classes" => "col-lg-4 col-md-6 mb-4 mb-lg-0"
    ),
    array(
        "img" => "idea.webp",
        "text" => "Segui i corsi",
        "shape_svg" => "shape-smooth3.svg",
        "size-classes" => "col-lg-4 col-md-6"
    )
);
?>

<section class="cta-banner">
    <div class="cta-banner-wave cta-banner-wave-top">
        <object type="image/svg+xml" data="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/home/banner-pink-up.svg"></object>
    </div>
    
    <div class="cta-banner-content">
        <div class="container">
            <div class="row justify-content-center">
                <?php foreach($elementi as $elemento): ?>
                    <div class="<?php echo esc_attr($elemento['size-classes']); ?>">
                        <?php get_template_part('template-parts/home/elemento-fascia', null, array('elemento' => $elemento)); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cta-banner-stats text-center py-5">
                <h2 class="display-3 fw-bold text-white mb-0">Abbiamo supportato oltre 10.000 utenti</h2>
            </div>
        </div>
    </div>

    <div class="cta-banner-wave cta-banner-wave-bottom">
        <object type="image/svg+xml" data="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/home/banner-pink-down.svg"></object>
    </div>
</section>
