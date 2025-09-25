<!-- Slider main container -->
<?php


$current_user = wp_get_current_user();
$customer_orders = wc_get_orders(array(
    'customer_id' => $current_user->ID,
    'status' => 'completed',
    'limit' => -1,
));
$product_ids = array();
foreach ($customer_orders as $order) {
    foreach ($order->get_items() as $item) {
        $product_ids[] = $item->get_product_id();
    }
}

$documenti = array();

if (!empty($product_ids)) {
        $args = array(
            'post_type' => 'product',
            'post__in' => $product_ids,
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'tipo_prodotto',
                    'field' => 'slug',
                    'terms' => 'documento',
                    'include_children' => true,
                ),
            ),
        );
        $documenti = get_posts($args);
    }
    ?>

<div id="sezione-i-miei-documenti" class="mt-5">
    <div class="">
    <h3 class="">I miei documenti</h3>
    <?php if (!empty($documenti)) : ?>
    <div id="swiper-container-miei-documenti" class="swiper-container swiper-container-miei-documenti">
        <div id="swiper-wrapper-miei-documenti" class="swiper-wrapper swiper-wrapper-miei-documenti">
            <?php foreach ($documenti as $documento) { ?>
                <div class="swiper-slide swiper-slide-miei-documenti">
                        <?php get_template_part('/template-parts/profilo-utente/mio-documento', null, array('documento' => $documento, 'sezione' => 'miei-documenti', 'classe-bordo'=>'mini-documento-bordo-verde', 'classe-button' => 'btn-outline-secondary')); ?>
                    </div>
            <?php } ?>
        </div>
        <div class="swiper-pagination-container swiper-pagination-container-miei-documenti">
            <div id="swiper-pagination-miei-documenti" class="swiper-pagination swiper-pagination-miei-documenti"></div>
        </div>

        <div class="swiper-button-prev-container swiper-button-prev-container-miei-documenti">
            <div id="swiper-button-prev-miei-documenti" class="swiper-button-prev swiper-button-prev-miei-documenti"></div>
        </div>
        <div class="swiper-button-next-container swiper-button-next-container-miei-documenti">
            <div id="swiper-button-next-miei-documenti" class="swiper-button-next swiper-button-next-miei-documenti"></div>
        </div>
        <div class="swiper-scrollbar-container swiper-scrollbar-container-miei-documenti" hidden>
            <div id="swiper-scrollbar-miei-documenti" class="swiper-scrollbar swiper-scrollbar-miei-documenti"></div>
        </div>
    </div>
    <?php else : ?>
    <p>Ancora nessun documento acquistato.</p>
    <?php endif; ?>
</div>
</div>



<!-- Initialize Swiper -->
<script>
var swiper = new Swiper('#swiper-container-miei-documenti', {
    slidesPerView: 1,
    spaceBetween: 40,

    navigation: {
        nextEl: '#swiper-button-next-miei-documenti',
        prevEl: '#swiper-button-prev-miei-documenti',
    },
    pagination: {
        el: '#swiper-pagination-miei-documenti',
        clickable: true,
    },
    scrollbar: {
        el: '#swiper-scrollbar-miei-documenti',
    },

    breakpoints: {
        800: {
            slidesPerView: 2,
            spaceBetween: 40,
        },
        1050: {
            slidesPerView: 3,
            spaceBetween: 40,
        },
        1300: {
            slidesPerView: 4,
            spaceBetween: 40,
        },
    },
    freeMode: true,
    effect: 'slide',
    scrollbar: {
        el: '#swiper-scrollbar-miei-documenti',
        hide: true,
    },




});
</script>

<style>


</style>

