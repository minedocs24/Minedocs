<?php

$feature = $args['feature'];

?>

<div class="d-flex align-items-center m-1">
    <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/search/<?php echo $feature['icon']; ?>" class="icon-feature">
    <p class="mb-0 mx-2"><?php echo $feature['text']; ?></p>

</div>