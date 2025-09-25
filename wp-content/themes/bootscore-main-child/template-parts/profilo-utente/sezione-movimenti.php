<?php
// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: devi essere loggato per visualizzare questa schermata.';
    return;
}

$transazioni = Transaction::get_transactions_for_view(array('user_id' => get_current_user_id(), 'hidden_to_user' => false));
?>

<div id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class="my-5">
        <div class="table-card">
            <div class="table-card-header d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <h2 class="table-card-header-text text-center text-sm-start">Cronologia delle Transazioni</h2>
                <a href="<?php echo PACCHETTI_PUNTI_PAGE; ?>" class="upload-btn-table btn-cta btn-responsive text-nowrap mt-2 mt-sm-0">Ricarica Pro</a>
            </div>
            <div class="card-body">
                <div class="menu-card filtro-tipologia-file d-flex flex-column flex-sm-row justify-content-start">
                    <button id="filter-all" class="menu-item active mb-2 mb-sm-0">Tutti i movimenti</button>
                    <button id="filter-incomes" class="menu-item mb-2 mb-sm-0">Entrate</button>
                    <button id="filter-outcomes" class="menu-item">Uscite</button>
                </div>
                <div class="filter-box d-flex flex-column mb-3">
                    <input type="text" id="search-input" placeholder="Cerca tra i movimenti" class="ricerca-file">
                </div>
                <div class="table-responsive">
                    <table class="table table-profilo align-middle" id="transaction-table">
                        <thead class="table-dark">
                            <tr>
                                <th data-column="data">Data</th>
                                <th data-column="tipo">Tipo di transazione</th>
                                <th data-column="entrata">Le tue entrate</th>
                                <th data-column="uscita">Le tue uscite</th>
                                <th>Dettagli</th>
                            </tr>
                        </thead>
                        <tbody id="transaction-table-body">
                            <?php foreach ($transazioni as $transazione): ?>
                            <tr data-entrata="<?php echo esc_attr($transazione['entrata']); ?>" data-uscita="<?php echo esc_attr($transazione['uscita']); ?>">
                                <td><?php echo esc_html($transazione['data']); ?></td>
                                <td><?php echo esc_html($transazione['tipo']); ?></td>
                                <td class="text-success"><?php echo esc_html($transazione['entrata']); ?></td>
                                <td class="text-danger"><?php echo esc_html($transazione['uscita']); ?></td>
                                <td class="text-center">
                                    <button class="btn-actions btn-link-table" title="Dettagli">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/eye.png" alt="Dettagli" width="16" height="16" />
                                    </button>
                                </td>
                            </tr>
                            <tr class="details-row" style="display: none;">
                                <td colspan="5" class="cella-dettagli-transazione">
                                    <div class="details-content contenuto-dettagli-transazione">
                                        <p><strong>Descrizione:</strong> <?php echo esc_html($transazione['descrizione']); ?></p>
                                        <p><strong>Saldo progressivo Punti Pro:</strong> <?php echo esc_html($transazione['saldo_pro']); ?></p>
                                        <p><strong>Saldo progressivo Punti Blu:</strong> <?php echo esc_html($transazione['saldo_blu']); ?></p>
                                        <p><strong>Saldo progressivo Vendite:</strong> <?php echo esc_html($transazione['saldo_vendite']); ?></p>
                                        <?php if (!empty($transazione['ordine'])): ?>
                                        <p><strong>Riferimento ordine:</strong> <?php if ($transazione['link_ordine']): ?>
                                            Ordine <?php echo esc_html($transazione['ordine']); ?>
                                            <?php else: ?>
                                            <?php echo esc_html($transazione['ordine']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-controls">
                    <img id="prev-page" class="table-button-prev" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Precedente" width="16" height="16" />
                    <span id="page-info"></span>
                    <img id="next-page" class="table-button-next" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Successivo" width="16" height="16" />
                </div>
            </div>
        </div>
    </div>
</div>