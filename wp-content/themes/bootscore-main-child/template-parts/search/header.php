<section class="py-5 my-5 position-relative">
<img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/search/macchia-verde.svg"
class="fixed-left-svg" alt="Macchia Verde">

    <div>
        <h1 class="text-center">
            <?php
    /* translators: %s: search query. */
    printf(esc_html__('Risultati di ricerca', 'bootscore'));
    ?>
        </h1>
    </div>
    <?php get_template_part('template-parts/search/search-bar'); ?>


</section>

<style>

.fixed-left-svg {
    position: absolute;
    left: -50px;
    top: 50px;
    transform: rotate(90deg);
    z-index: -1;

}

</style>

