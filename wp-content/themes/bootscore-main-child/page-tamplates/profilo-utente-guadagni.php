<?php

/**
 * Template Name: Profilo Utente Guadagni
 *
 * 
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    return;
}

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();
?>

<!-- Navbar con hamburger (solo mobile) -->
<!-- <nav class="navbar navbar-light bg-light d-lg-none">
    <div class="container-fluid">
        <button class="btn btn-outline-light" id="sidebarToggle">
            <span class="navbar-toggler-icon"></span>
        </button>
        <span class="navbar-brand mb-0">I tuoi guadagni</span>
    </div>
</nav> -->

<div class="d-flex overflow-hidden w-100">
    <!-- Sidebar desktop -->
    <div id="sidebarDesktop-profilo-utente" class="p-3 d-none d-lg-block flex-shrink-0" style="min-width: 250px; width: 350px;">
        <?php get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'guadagni')); ?>
    </div>

    <!-- Sidebar mobile -->
    <div id="sidebarMobile-profilo-utente" class="d-lg-none p-3">
        <?php get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'guadagni')); ?>
    </div>

    <!-- Contenuto principale -->
    <div class="flex-grow-1 overflow-auto p-4">
        <?php get_template_part('template-parts/profilo-utente/sezione-guadagni'); ?>
    </div>
</div>

<?php
get_footer();