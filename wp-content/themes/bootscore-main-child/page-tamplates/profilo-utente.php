<?php

/**
 * Template Name: Profilo Utente
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


  <style>



  </style>


<!-- Navbar con hamburger (solo mobile) -->
<!-- <nav class="navbar navbar-light bg-light d-lg-none">
  <div class="container-fluid">
    <button class="btn btn-outline-light" id="sidebarToggle">
      <span class="navbar-toggler-icon"></span>
    </button>
    <span class="navbar-brand mb-0">Il mio profilo</span>
  </div>
</nav> -->

<!-- Overlay -->
<div id="overlay-profilo-utente"></div>

<div class="d-flex">
  <!-- Sidebar desktop -->
  <div id="sidebarDesktop-profilo-utente" class="p-3 d-none d-lg-block">
    <?php get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'profilo-utente')); ?>
  </div>

  <!-- Sidebar mobile -->
  <!-- <div id="sidebarMobile-profilo-utente" class="d-lg-none p-3">
    <?php //get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'profilo-utente')); ?>
  </div> -->

  <!-- Contenuto principale -->
  <div class="flex-grow-1 p-4 d-none d-lg-block" style="max-width: 75%;">


    <?php get_template_part('template-parts/profilo-utente/sezione-utente-2'); ?>
  </div>

    <!-- Contenuto principale -->
    <div class="flex-grow-1 p-4 d-lg-none" style="max-width: 100%;">
   

    <?php get_template_part('template-parts/profilo-utente/sezione-utente-2'); ?>
  </div>

</div>

<script>

</script>

<?php


get_footer( );
