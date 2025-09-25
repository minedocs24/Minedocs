<?php

/**
 * Template Name: Pacchetti punti
 *
 * 
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();

?>
<div id="content">
<?php

get_template_part( 'template-parts/points-packs/header' );

get_template_part( 'template-parts/points-packs/plans' );

get_template_part( 'template-parts/points-packs/bottom-decoration' );
?>

</div>  

<?php


get_footer( );