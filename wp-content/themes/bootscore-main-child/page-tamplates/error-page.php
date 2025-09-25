<?php

/**
 * Template Name: Error Page
 *
 * 
 *
 * @package Bootscore
 * @version 6.0.0
 */
get_header();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page-content">
                <h1 class="display-4 mb-4">Si è verificato un errore</h1>
                <p class="lead mb-4">Ci scusiamo per l'inconveniente. Il nostro team è stato notificato e sta lavorando per risolvere il problema.</p>
                
                <div class="error-actions mt-5">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary me-3">
                        <i class="fas fa-home"></i> Torna alla Home
                    </a>
                    <a href="<?php echo esc_url(home_url('/contatti')); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-envelope"></i> Contattaci
                    </a>
                </div>
                
                <div class="mt-4">
                    <p>Se il problema persiste, non esitare a contattarci.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer(); 