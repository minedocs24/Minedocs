<?php
// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: effettua prima il login per visualizzare questa pagina.';
    return;
}
?>

<main id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class="container py-3 pb-5">

        <?php get_template_part("template-parts/profilo-utente/sezione-utente", null, array('')); ?>
        <?php get_template_part("template-parts/profilo-utente/sezione-situazione-punti", null, array('')); ?>

        <?php get_template_part("template-parts/profilo-utente/sezione-miei-documenti", null, array('')); ?>

        <?php get_template_part("template-parts/profilo-utente/sezione-potrebbe-interessarti", null, array('')); ?>

        <?php //get_template_part("template-parts/profilo-utente/sezione-documenti-caricati", null, array('')); ?> 
    </div>
    <div class="container text-center mt-4">
        <a href="<?php echo UPLOAD_PAGE; ?>" class="btn btn-primary upload-btn">Carica un documento</a>
        <p class="text-muted d-block text-center mt-2" style="font-size: 0.9rem;">Amplia la tua raccolta</p>
    </div>
    <!-- </div> -->
    
</main>
