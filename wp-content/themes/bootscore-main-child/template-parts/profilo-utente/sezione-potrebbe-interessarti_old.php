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
?>

<div id="sezione-potrebbe-interessarti" class="row mt-5">
    <h3 class="section-title mb-4">Potrebbe interessarti anche...</h3>
    <?php if (!empty($product_ids)):
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post__not_in' => $product_ids,
            'orderby' => 'rand', // Ordina in modo casuale
            'post_status' => 'publish', // Mostra solo post pubblicati
            'tax_query' => array(
            array(
                'taxonomy' => 'tipo_prodotto',
                'field' => 'slug',
                'terms' => 'documento',
            ),
            ),
        );
        $documenti = get_posts($args);
    ?>
        <!-- Carosello -->
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php foreach ($documenti as $documento) : ?>
                    <div class="swiper-slide">
                        <?php get_template_part('/template-parts/profilo-utente/single-suggested-document', null, array('documento' => $documento, 'sezione' => 'suggeriti')); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Frecce di navigazione -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    <?php else : ?>
        <p>Ancora nessun documento acquistato.</p>
    <?php endif; ?>
</div>


<!-- CSS per il carosello -->
<style>

    /* Contenitore del carosello */
    .swiper-container {
        position: relative; /* Necessario per posizionamento assoluto delle frecce */
        width: 100%; /* Occupa tutto lo spazio disponibile */
        height: 300px; /* Imposta l'altezza per il carosello */
        overflow: hidden; /* Nasconde le slide che vanno oltre i bordi */
    }

    /* Wrapper delle slide con padding a sinistra */
    .swiper-wrapper {
        padding-left: 0; /* Aggiunge un margine a sinistra */
        display: flex; /* Mantiene il layout flessibile */
        gap: 10px; /* Spaziatura tra le slide */
    }

    /* Gradiente per il bordo sinistro */
    .swiper-container.show-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 80px; /* Larghezza del gradiente a sinistra */
        height: 100%;
        background: linear-gradient(to right, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
        z-index: 2; /* Sovrascrive la parte visibile del carosello */
        pointer-events: none; /* Non interferisce con i clic */
    }

    /* Gradiente per il bordo destro */
    .swiper-container::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px; /* Larghezza del gradiente a destra */
        height: 100%;
        background: linear-gradient(to left, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
        z-index: 2; /* Assicurarsi che si sovrapponga alle slide */
        pointer-events: none; /* Non interferisce con i clic */
    }

    /* Frecce di navigazione */
    .swiper-button-next,
    .swiper-button-prev {
        position: absolute;
        top: 50%; /* Centrate verticalmente */
        transform: translateY(-50%); /* Regolazione per l'allineamento verticale */
        z-index: 10; /* Assicurarsi che le frecce siano visibili */
        color: #fff; /* Colore del testo o icone */
        padding: 10px; /* Spaziatura interna */
        border-radius: 50%; /* Forma circolare */
        cursor: pointer; /* Indicatore di cliccabilità */
    }

    /* Freccia sinistra */
    .swiper-button-prev {
        left: -40px; /* Posizionata leggermente fuori dal carosello */
    }

    /* Freccia destra */
    .swiper-button-next {
        right: -40px; /* Posizionata leggermente fuori dal carosello */
    }

    /* Slide */
    .swiper-slide {
        margin: 0; /* Evita margini aggiuntivi */
        flex: 0 0 auto; /* Evita che le slide si ridimensionino */
    }

    /* Responsive */
    @media (max-width: 768px) {
        .swiper-button-next,
        .swiper-button-prev {
            padding: 8px; /* Dimensioni più piccole per mobile */
        }
    }

</style>

<!-- JavaScript per il carosello -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const swiperContainer = document.querySelector('.swiper-container');
        const prevButton = document.querySelector('.swiper-button-prev');
        const nextButton = document.querySelector('.swiper-button-next');

        const swiper = new Swiper('.swiper-container', {
            slidesPerView: 3,
            spaceBetween: 10,
            slidesOffsetBefore: 80, // Aggiunge 80px di spazio prima della prima slide
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                640: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 3,
                },
                1024: {
                    slidesPerView: 4,
                },
            },
            on: {
                init: function () {
                    // Nascondi il pulsante "prev" inizialmente
                    prevButton.style.display = 'none';
                    swiperContainer.classList.remove('show-gradient');
                },
            },
        });

        // Mostra gradiente e pulsante "prev" quando si clicca su "next"
        nextButton.addEventListener('click', function () {
            swiperContainer.classList.add('show-gradient');
            prevButton.style.display = 'flex'; // Mostra il pulsante "prev"
        });

        // Nascondi gradiente e pulsante "prev" quando si torna al primo elemento
        prevButton.addEventListener('click', function () {
            if (swiper.activeIndex === 0) {
                swiperContainer.classList.remove('show-gradient');
                prevButton.style.display = 'none'; // Nascondi il pulsante "prev"
            }
        });

        // Sincronizza la visibilità dei pulsanti durante la navigazione
        swiper.on('slideChange', function () {
            if (swiper.activeIndex === 0) {
                prevButton.style.display = 'none'; // Nascondi "prev" al primo elemento
                swiperContainer.classList.remove('show-gradient');
            } else {
                prevButton.style.display = 'flex'; // Mostra "prev" dopo il primo elemento
                swiperContainer.classList.add('show-gradient');
            }
        });
    });

</script>
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
