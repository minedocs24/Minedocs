<?php global $post_id; ?>
<div class="document-analysis-container">
    <div class="image-section">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/doc_analysis.png" alt="Document analysis">
    </div>

    <div class="text-section">
        <h2>Stiamo <span class="highlight">analizzando</span> il tuo documento</h2>
        <p>Il tuo documento è in fase di analisi e potrebbe volerci qualche minuto. <br>
        Monitora le notifiche per sapere quando sono stati pubblicati.</p>

        <?php if (!$post_id && PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO>0) : ?>
            <div class="points-received">
                <button class="points-button">
                    <h3 class="points-text">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/points_reward.png" alt="Points reward" class="points-image">
                        Riceverai <?php echo PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO; ?> Punti Blu appena il documento verrà approvato 😊
                    </h3>
                </button>
            </div>
        <?php endif; ?>

        <div class="navigation-buttons">
            <button id="restart" class="button-custom button-custom-blue" onclick="restart()">Carica un altro file</button>
        </div>
        <div class="mt-3">
            <a href="<?php echo home_url(); ?>" class="text-decoration-none">Torna alla home</a>
        </div>
    </div>
</div>