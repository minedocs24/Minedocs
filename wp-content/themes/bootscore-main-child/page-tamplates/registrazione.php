<?php

/**
 * Template Name: Registrazione
 *
 * 
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>

<?php
get_header();
?>
<script>var baseImageUrl = "<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/";</script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/registrazione.js"></script>


<div class="container mt-5">

    <!-- <div class="text-center">
        <img src="<?php /* echo MINEDOCS_LOGO; */ ?>" alt="Minedocs" width="150" height="32">
    </div> -->

    <?php   // Verifica se l'utente è loggato
    if (!is_user_logged_in()) {
        echo 'Errore: devi essere loggato per visualizzare questa schermata.</div>';
        return;
    }?>

    <!-- Icona -->
    <div  class="emoji text-center">
        <img id="emoji-step-caricamento" src="" alt="" style="width: 150px; height: 150px;" />
    </div>

    <!-- Dot Navigation -->
    <div class="dots-container">
        <span class="dot active" data-step="1"></span>
        <span class="dot" data-step="2"></span>
        <span class="dot" data-step="3"></span>
    </div>

    <!-- Step Content -->
    <form id="form-registrazione">
        <div id="step1" class="step-content active">
            <?php get_template_part('template-parts/registrazione-utente/sezione1'); ?>
        </div>
        <div id="step2" class="step-content active">
            <?php get_template_part('template-parts/registrazione-utente/sezione2'); ?>
        </div>
        <div id="step3" class="step-content active">
            <?php get_template_part('template-parts/registrazione-utente/sezione3'); ?>
        </div>
    </form>
</div>


<?php get_footer(); ?>
