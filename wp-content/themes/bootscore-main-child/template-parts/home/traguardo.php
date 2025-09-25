<?php
$traguardo = $args['elemento'];
$numero = $traguardo['numero'];
$testo = $traguardo['testo'];
$background_color = $traguardo['background-color'];

?>

<div class="py-3">
    <p class="display-2 counter" style="font-weight: 600; margin-bottom: 5px;"><?php echo $numero; ?></p>
    <span class="badge badge-pill" style="border-radius: 20px; font-size: 1.5em; font-weight: 100; background-color: <?php echo $background_color; ?>"><?php echo $testo; ?></span>
    
</div>


