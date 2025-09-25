<?php

/**
 * Template Name: Profilo Utente Documenti Caricati
 *
 * 
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    wp_redirect( home_url('/login') );
    return;
}

// Exit if accessed directly
defined('ABSPATH') || exit;

$class_padding_admin = current_user_can('administrator') ? 'pt-5' : '';

get_header();
?>

<!-- Navbar con hamburger (solo mobile) -->
<!-- <nav class="navbar navbar-light bg-light d-lg-none">
  <div class="container-fluid">
    <button class="btn btn-outline-light" id="sidebarToggle">
      <span class="navbar-toggler-icon"></span>
    </button>
    <span class="navbar-brand mb-0">Documenti Caricati</span>
  </div>
</nav> -->

<!-- Overlay -->
<div id="overlay-profilo-utente"></div>

<div class="d-flex overflow-hidden w-100">
  <!-- Sidebar desktop -->
  <div id="sidebarDesktop-profilo-utente" class="p-3 d-none d-lg-block flex-shrink-0" style="min-width: 250px; width: 350px;">
    <?php get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'documenti-caricati')); ?>
  </div>

  <!-- Sidebar mobile -->
  <div id="sidebarMobile-profilo-utente" class="d-lg-none p-3">
    <?php get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'documenti-caricati')); ?>
  </div>
  
    <!-- Contenuto principale -->
    <div class="flex-grow-1 overflow-auto p-4">
    <?php get_template_part('template-parts/profilo-utente/sezione-documenti-caricati'); ?>
  </div>


</div>

<?php
get_footer();

?>

