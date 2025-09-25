<?php

$traguardi = $args['traguardi'];

?>



<div class="d-flex flex-column justify-content-around flex-grow-1">
    <?php
            foreach ($traguardi as $traguardo) {
                get_template_part('template-parts/home/traguardo', null, array('elemento' => $traguardo));
            }
            ?>
</div>


