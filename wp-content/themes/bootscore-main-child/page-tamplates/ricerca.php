<?php

/**
 * Template Name: Ricerca
 *
 * @package Bootscore
 * @version 6.0.0
 */


// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="page-ricerca-wrapper">
                <h1 class="display-4 text-center mb-4">Cerca un documento</h1>
                <p class="lead text-center text-muted">Trova i documenti che ti servono per il tuo percorso di studi</p>
                
                <div class="search-section-wrapper mb-5">
                    <?php get_template_part('template-parts/search/search-bar-new'); ?>
                </div>

                <?php get_template_part('template-parts/search/modal-filtri'); ?>
                
                <div id="risultati-ricerca" class="mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 mb-0">Risultati della ricerca</h2>
                        <div id="num-results" class="text-muted"></div>
                    </div>
                    
                    <div id="search-results" class="row g-4">
                        <!-- I risultati della ricerca verranno inseriti qui -->
                    </div>
                    
                    <div id="pagination" class="mt-4" hidden></div>
                </div>

                <div class="mt-5">
                    <?php get_template_part("template-parts/profilo-utente/sezione-potrebbe-interessarti", null, array('')); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();

?>