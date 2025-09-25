<?php
 /**
  * The template for displaying 404 pages (not found)
  *
  * @package Bootscore Child
  * @version 1.0.0
  */
 
 // Exit if accessed directly
 defined('ABSPATH') || exit;
 
 get_header();
 ?>
 <div id="content" class="site-content <?= apply_filters('bootscore/class/container', 'container', '404'); ?> <?= apply_filters('bootscore/class/content/spacer', 'pt-5 pb-5', '404'); ?>">
     <div id="primary" class="content-area">
         <main id="main" class="site-main">
             <section class="error-404 not-found text-center">
                 <div class="page-404 py-5">
                     <div class="row justify-content-center">
                         <div class="col-md-8">
                             <h1 class="display-1 fw-bold text-primary mb-4">404</h1>
                             <h2 class="h3 mb-4"><?php esc_html_e('Oops! Pagina non trovata', 'bootscore'); ?></h2>
                             <div class="error-description mb-5">
                                 <p class="lead"><?php esc_html_e('Ci dispiace, ma la pagina che stai cercando sembra essere scomparsa nel cyberspazio!', 'bootscore'); ?></p>
                                 <p><?php esc_html_e('Prova a utilizzare la ricerca o torna alla homepage.', 'bootscore'); ?></p>
                             </div>
                             
                             <!-- Search form -->
                             <div class="search-404 mb-5">
                                 <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                                     <div class="input-group">
                                         <input type="search" class="form-control" placeholder="<?php esc_attr_e('Cerca...', 'bootscore'); ?>" value="<?php echo get_search_query(); ?>" name="s">
                                         <button class="btn btn-primary" type="submit"><?php esc_html_e('Cerca', 'bootscore'); ?></button>
                                     </div>
                                 </form>
                             </div>
 
                             <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                 <a class="btn btn-primary px-4" href="<?= esc_url(home_url()); ?>" role="button">
                                     <i class="fas fa-home me-2"></i><?php esc_html_e('Torna alla Homepage', 'bootscore'); ?>
                                 </a>
                                 <a class="btn btn-outline-primary px-4" href="javascript:history.back()" role="button">
                                     <i class="fas fa-arrow-left me-2"></i><?php esc_html_e('Torna Indietro', 'bootscore'); ?>
                                 </a>
                             </div>
 
                             <?php if (is_active_sidebar('404-page')) : ?>
                                 <div class="mt-5"><?php dynamic_sidebar('404-page'); ?></div>
                             <?php endif; ?>
                         </div>
                     </div>
                 </div>
             </section>
         </main>
     </div>
 </div>
 
 <style>
 .error-404 {
     min-height: 60vh;
     display: flex;
     align-items: center;
 }
 .page-404 {
     width: 100%;
 }
 .error-404 h1 {
     font-size: 8rem;
     text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
 }
 .search-404 .form-control {
     border-radius: 50rem 0 0 50rem;
 }
 .search-404 .btn {
     border-radius: 0 50rem 50rem 0;
 }
 </style>
 
 <?php
 get_footer(); 