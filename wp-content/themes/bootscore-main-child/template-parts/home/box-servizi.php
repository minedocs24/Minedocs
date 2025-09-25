<?php

$servizi = $args['servizi'];

?>



<div class="d-flex flex-column justify-content-around flex-grow-1">
    <?php
            foreach ($servizi as $servizio) {
                get_template_part('template-parts/home/servizio', null, array('elemento' => $servizio));
            }
            ?>
</div>