<?php

$plans_info = array(
    'pacchetto-150-punti' => array('prezzo-originale' => '11,90', 'prezzo-scontato' => '9,90', 'punti-pro' => '150', 'titolo-html' => '150 punti <span style="color: rgb(234, 96, 32);">Pro</span>'),
    'pacchetto-500-punti' => array('prezzo-originale' => '39,90', 'prezzo-scontato' => '29,90', 'punti-pro' => '500', 'titolo-html' => '500 punti <span style="color: rgb(56, 198, 139);">Pro</span>'),
    'pacchetto-1000-punti' => array('prezzo-originale' => '79,90', 'prezzo-scontato' => '69,90', 'punti-pro' => '1000', 'titolo-html' => '1000 punti <span style="color: rgb(0, 168, 255);">Pro</span>'),

);

$query = new WP_Query(array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'ASC',
));




?>

<section class="position-relative"> 
    <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/points-packs/shape-smooth1.svg"
    class="shape-smooth1" style="filter: drop-shadow(1px 1px 3px #0000004d);">
    <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/points-packs/shape-smooth2.svg"
    class="shape-smooth2" style="filter: drop-shadow(1px 1px 3px #0000004d);">
    <div class="container ">

        <div class="row mb-3 text-center position-relative">
        <span> <?php $query->have_posts() ?></span>
            <?php            
            if ($query->have_posts()) : 
                while ($query->have_posts()) : $query->the_post();
                    $product_slug = get_post_field('post_name', get_the_ID());
                    if (array_key_exists($product_slug, $plans_info)) {
                        $product_info = $plans_info[$product_slug];

                        get_template_part('template-parts/points-packs/plan', null, array('product_info' => $product_info, 'product' => $post));

                    }
                endwhile;
                wp_reset_postdata();
            endif;
            ?>


        </div>
    </div>
</section>

<style>
.card-custom {
    border-radius: 20px;
    border: none;
    box-shadow: 0 0px 6px rgba(0, 0, 0, 0.3) !important;
    margin: 10px;
}

.card-custom .card-body {
    padding: 1.25rem;
}

.card-custom .card-header {
    border-bottom: 1px solid gray;
    background-color: transparent;
    border-radius: 20px 20px 0 0;
    padding: 20px;
    margin-left: 50px;
    margin-right: 50px;
}

.card-custom .card-header h3 {
    font-size: 2rem;
    margin-bottom: 0;
}

.card-custom .card-title {
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
    font-weight: 300;
}

.card-custom .card-text {
    font-size: 0.875rem;
}

.card-custom ul {
    list-style: none;
    padding: 0;
}

.card-custom ul li {
    padding: 0.25rem 0;
}

.highlight {
    margin-top: -20px;
    padding-top: 10px;
    padding-bottom: 30px;
    margin-left: -5px;
    margin-right: -5px;
}


.btn-custom {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: none;
    width: auto;
    max-width: 100%;
    background-color: rgb(0, 168, 255) !important;
    font-weight: 700;
}

.btn-custom:hover {
    background-color: white !important;
    color: black !important;
    border: 1px solid black;
}


.razzo-pro-header {
    position: absolute;
    top: -130px;
    left: -50px;
    height: 200px;
    width: auto;
    transform: rotate(35deg);
    transition: transform 0.5s ease;
    z-index: 1;
    fill: #00A8FF;

}


/* Media queries per spostare l'immagine a destra su schermi più piccoli */
@media (max-width: 1200px) {
    .razzo-pro-header {

        transform: scale(1)rotate(35deg);
    }
}

@media (max-width: 992px) {
    .razzo-pro-header {

        transform: scale(0.7)rotate(35deg);
    }
}

@media (max-width: 768px) {
    .razzo-pro-header {

        transform: scale(0.6)rotate(35deg);
    }
}

@media (max-width: 576px) {
    .razzo-pro-header {
        top: -100px;

        transform: scale(0.4)rotate(35deg);
    }
}

.shape-smooth1 {
        position: absolute;
        right: 70px;
        top: 140px;
        transform: scale(1.1);
        transition: right 0.5s ease, transform 0.5s ease;
        z-index: -1;

    }

.shape-smooth2 {
    position: absolute;
    left: 692px;
    top: -40px;
    transform: scale(1.1);
    transform: rotate(90deg);
    transition: right 0.5s ease, transform 0.5s ease;
    z-index: -1;

}
</style>