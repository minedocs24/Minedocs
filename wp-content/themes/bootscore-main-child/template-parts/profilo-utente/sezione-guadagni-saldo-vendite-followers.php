
<div class="row justify-content-center">
    <div class="col-12 col-md-3 text-center mb-3 mb-md-0">
        <div class="info-card">
            <div class="info-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <p class="info-title">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/pagina-utente-guadagni/saldo.svg"
                    style="width: 20px; height: 20px; margin-right: 8px; margin-top: -2px;">Saldo attuale:
            </p>
            <p id="saldo_utente" class="info-value">€ <?php echo number_format((float)$args['saldo_utente'], 2, ',', '.'); ?></p>
        </div>
    </div>
    <div class="col-12 col-md-3 text-center">
        <div class="info-card">
            <div class="info-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <p class="info-title"><img
                    src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/pagina-utente-guadagni/vendite.svg"
                    style="width: 20px; height: 20px; margin-right: 8px; margin-top: -2px;">Vendite totali:</p>
            <p id="num_documenti_venduti" class="info-value"><?php echo $args['num_doc_venduti']; ?></p>
        </div>
    </div>
</div>
