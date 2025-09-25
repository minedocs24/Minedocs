<!-- Slider main container -->
<?php

$current_user = wp_get_current_user();

$corsi_dell_utente = get_user_meta( $current_user->ID, 'nome_corso_di_laurea',  true);
$corsi_dell_utente = array_map('intval', (array) $corsi_dell_utente);

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
        'posts_per_page' => -1,
        'post__not_in' => $product_ids,
        'orderby' => 'rand', // Ordina in modo casuale
        'post_status' => 'publish', // Mostra solo post pubblicati
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'tipo_prodotto',
                'field' => 'slug',
                'terms' => 'documento',
                'include_children' => true,
            ),
            array(
                'taxonomy' => 'nome_corso_di_laurea',
                'field' => 'term_id',
                'terms' => $corsi_dell_utente,
                'include_children' => true,
            )
        ),
        'author__not_in' => array($current_user->ID) // Esclude i post dell'autore corrente
    );
    $documenti = get_posts($args);
}
?>

<div id="sezione-potrebbe-interessarti" class=" mt-5">
    <div class="">
    <h3 class="">Potrebbe interessarti anche...</h3>
    <?php if (!empty($documenti)) : ?>
    <div id="swiper-container-potrebbe-interessarti" class="swiper-container swiper-container-potrebbe-interessarti">
        <div id="swiper-wrapper-potrebbe-interessarti" class="swiper-wrapper swiper-wrapper-potrebbe-interessarti">
            <?php foreach ($documenti as $documento) { ?>
                <div class="swiper-slide swiper-slide-potrebbe-interessarti">
                        <?php get_template_part('/template-parts/profilo-utente/mio-documento', null, array(
                            'documento' => $documento, 
                            'sezione' => 'potrebbe-interessarti', 
                            'classe-button' => 'btn-outline-primary', 
                            'testo-button' => 'Sblocca', 
                            'icona-button' => 'fas fa-unlock-alt'
                            )); ?>
                    </div>
            <?php } ?>
        </div>
        <div class="swiper-pagination-container swiper-pagination-container-potrebbe-interessarti">
            <div id="swiper-pagination-potrebbe-interessarti" class="swiper-pagination swiper-pagination-potrebbe-interessarti"></div>
        </div>

        <div class="swiper-button-prev-container swiper-button-prev-container-potrebbe-interessarti">
            <div id="swiper-button-prev-potrebbe-interessarti" class="swiper-button-prev swiper-button-prev-potrebbe-interessarti"></div>
        </div>
        <div class="swiper-button-next-container swiper-button-next-container-potrebbe-interessarti">
            <div id="swiper-button-next-potrebbe-interessarti" class="swiper-button-next swiper-button-next-potrebbe-interessarti"></div>
        </div>
        <div class="swiper-scrollbar-container swiper-scrollbar-container-potrebbe-interessarti" hidden>
            <div id="swiper-scrollbar-potrebbe-interessarti" class="swiper-scrollbar swiper-scrollbar-potrebbe-interessarti"></div>
        </div>
    </div>
    <?php else : ?>
    <p>Al momento non sappiamo cosa suggerirti! Hai una richiesta in particolare? Faccelo sapere! 😊</p>
    <div class="text-center mt-4">
        <a href="<?php echo CONTATTI_PAGE; ?>" class="btn btn-primary upload-btn">Contattaci!</a>
    </div>
    <?php endif; ?>
    </div>
</div>  

<!-- Initialize Swiper -->
<script>
var swiper = new Swiper('#swiper-container-potrebbe-interessarti', {
    slidesPerView: 0.75,
    spaceBetween: 20,

    navigation: {
        nextEl: '#swiper-button-next-potrebbe-interessarti',
        prevEl: '#swiper-button-prev-potrebbe-interessarti',
    },
    pagination: {
        el: '#swiper-pagination-potrebbe-interessarti',
        clickable: true,
    },
    scrollbar: {
        el: '#swiper-scrollbar-potrebbe-interessarti',
    },

    breakpoints: {
        640: { // Adjusted for mobile
            slidesPerView: 0.75, // Increased slide width on mobile
            spaceBetween: 10,
            centeredSlides: true,
        },
        800: {
            slidesPerView: 2,
            spaceBetween: 10,
        },
        1050: {
            slidesPerView: 3,
            spaceBetween: 20,
        },
        1300: {
            slidesPerView: 4,
            spaceBetween: 40,
        },
    },
    freeMode: true,
    effect: 'slide',
    scrollbar: {
        el: '#swiper-scrollbar-potrebbe-interessarti',
        hide: true,
    },
});
</script>
