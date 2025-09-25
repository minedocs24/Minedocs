

<?php

$elemento = $args['elemento'];

?>

<div class="elemento-fascia align-content-center zoom <?php echo $elemento['size-classes']?>" style='background-image: url("<?php echo get_stylesheet_directory_uri( );?>/assets/img/home/<?php echo $elemento['shape_svg']?>"); filter: drop-shadow(3px 3px 8px #0000004d);'>
    <!-- Verifica se il testo è "Studia con l'AI" -->
    <?php if ($elemento['text'] === "Studia con l'AI"): ?>
        <a href="<?php echo home_url('/coming-soon-AI'); ?>" >
            <img src="<?php echo get_stylesheet_directory_uri( )?>\assets\img\home\<?php echo $elemento['img']; ?>" alt="Immagine" />
            <p class="back-link"><?php echo $elemento['text']; ?></p>
        </a>
    <?php elseif ($elemento['text'] === "Sfoglia i documenti"): ?>
        <a href="<?php echo home_url('/negozio'); ?>" >
            <img src="<?php echo get_stylesheet_directory_uri( )?>\assets\img\home\<?php echo $elemento['img']; ?>" alt="Immagine" />
            <p class="back-link"><?php echo $elemento['text']; ?></p>
        </a>
    <?php elseif ($elemento['text'] === "Segui i corsi"): ?>
        <a href="<?php echo home_url('/coming-soon-courses'); ?>" >
            <img src="<?php echo get_stylesheet_directory_uri( )?>\assets\img\home\<?php echo $elemento['img']; ?>" alt="Immagine" />
            <p class="back-link"><?php echo $elemento['text']; ?></p>
        </a>
    <?php else: ?>
        <img src="<?php echo get_stylesheet_directory_uri( )?>\assets\img\home\<?php echo $elemento['img']; ?>" alt="Immagine" />
        <p class="back-link"><?php echo $elemento['text']; ?></p>
    <?php endif; ?>
</div>
