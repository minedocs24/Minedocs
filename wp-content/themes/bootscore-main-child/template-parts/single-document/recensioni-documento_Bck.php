<?php
$recensioni = $args['recensioni'];

?>

<div class="row striscia-recensioni">
    <div class="col-12">
        <div class="carousel__wrapper">

            <?php
                foreach($recensioni as $recensione) {
                ?>
            <div class="carousel__slide">
                <?php get_template_part('template-parts/single-document/recensione', null, array('recensione' => $recensione)); ?>

            </div>

            <?php
                }
                ?>


        </div>
    </div>
</div>
