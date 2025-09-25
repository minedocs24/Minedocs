<?php
$current_user = wp_get_current_user();
$user_name = $current_user->user_firstname;
?>

<div class="registration-section active" id="section-1">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h2 class="display-6 text-center mb-4">Ciao <?php echo $user_name;?>, benvenuto/a a bordo!</h2>
            <p class="lead text-center text-muted mb-5">Completiamo assieme 3 semplici passaggi per migliorare la tua esperienza di studio</p>

            <!-- Selezione 0: Nickname -->
            <div class="mb-4">
                <label for="nickname" class="form-label d-flex align-items-center gap-2">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/Icone_Minedocs_nickname.svg" alt="Icona Nickname" class="img-fluid" style="width: 35px; height: 35px;">
                    <span>Con quale nickname vuoi apparire agli utenti su Minedocs?</span>
                </label>
                <div class="input-group">
                    <input type="text" id="nickname" name="nickname" class="form-control custom-input" placeholder="Scegli il tuo nickname" style="transition: width 0.5s ease;">
                    <div id="icon-loading-nickname" class="border-spinning-icon" hidden data-bs-toggle="tooltip" data-bs-placement="top" title="Verifica dell'univocità del nickname in corso..." onload="this.previousElementSibling.style.width = 'calc(100% - 30px)';" onanimationend="this.previousElementSibling.style.width = '100%';">
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                </div>
                <style>
                    .border-spinning-icon {
                        display: flex;
                        align-items: center;
                        padding: 0.375rem 0.75rem;
                        transition: opacity 0.3s ease; /* Aggiunta di transizione per l'opacità */
                    }
                </style>
                <small id="campo-nickname-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
                <small id="campo-nickname-already-used-error" class="text-danger" style="display: none;">Nickname già esistente. Sceglierne un altro e riprovare</small>
            </div>

            <!-- Selezione 1: Dove studi? -->
            <div class="mb-4">
                <label for="istituto" class="form-label d-flex align-items-center gap-2">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/Icone_Minedocs_scuola.svg" alt="Icona Istituto" class="img-fluid" style="width: 35px; height: 35px;">
                    <span>In quale scuola o università studi?</span>
                </label>
                <?php get_template_part('template-parts/upload/campo-universita', null, array('istituto' => $istituto)); ?>
                <small id="campo-universita-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
                <?php wp_nonce_field("profilazione-utente", "nonce-profilazione"); ?>
            </div>

            <!-- Selezione 2: Area di studio -->
            <div class="mb-4">
                <label for="area" class="form-label d-flex align-items-center gap-2">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/Icone_Minedocs_corso.svg" alt="Icona Area di studio" class="img-fluid" style="width: 35px; height: 35px;">
                    <span>Quale area di studio o corso di laurea stai seguendo?</span>
                </label>
                <?php get_template_part('template-parts/upload/campo-corso-di-laurea', null, array('show_title' => false)); ?>
                <small id="campo-corso-di-laurea-error" class="text-danger" style="display: none;">Questo campo è obbligatorio.</small>
            </div>

            <!-- Pulsanti di navigazione -->
            <div class="d-flex justify-content-center mt-5">
                <a id="next1" class="btn btn-primary px-4 py-2" onclick="saveProfilationData()">
                    <div id="icon-loading-next1" class="btn-loader mx-2" hidden style="display: inline-block;">
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                    Avanti
                </a>
            </div>
        </div>
    </div>
</div>
