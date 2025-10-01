<?php

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: devi essere loggato per visualizzare i tuoi file.';
    return;
}
?>

<div id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class=" my-5">
        <div class="table-card">
            <div class="table-card-header d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <h2 class="table-card-header-text text-center text-sm-start">Documenti caricati</h2>
                <a href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>" class="upload-btn-table btn-cta mt-2 mt-sm-0">Carica file</a>
            </div>
            <div class="card-body">
                <div class="menu-card filtro-tipologia-file d-flex flex-column flex-sm-row justify-content-start">
                    <button id="filter-all" class="menu-item active mb-2 mb-sm-0">Tutti i file</button>
                    <button id="filter-pro" class="menu-item mb-2 mb-sm-0">File a pagamento</button>
                    <button id="filter-blu" class="menu-item">File gratuiti</button>
                </div>

                <div class="filter-box d-flex flex-column mb-3">
                    <input type="text" id="search-input" placeholder="Cerca un file" class="ricerca-file">


                </div>

                <div id="table-container" class="table-responsive"></div>
                <div id="pagination-controls" class="pagination-controls">
                    <img id="prev-page" class="table-button-prev" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Precedente" width="16" height="16" />
                    <span id="page-info"></span>
                    <img id="next-page" class="table-button-next" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Successivo" width="16" height="16" />
                

                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-end align-items-center">
                    <span class="me-2 mb-2 mb-sm-0 mt-4 mt-sm-0">Elementi per pagina:</span>
                    <select id="itemsPerPageSelect" class="form-select numero-elementi-per-pagina">
                        <option value="5">5 elementi per pagina</option>
                        <option value="10" selected>10 elementi per pagina</option>
                        <option value="20">20 elementi per pagina</option>
                        <option value="50">50 elementi per pagina</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal per confermare l'eliminazione -->
<div class="modal fade" id="deleteFileModal" tabindex="-1" aria-labelledby="deleteFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFileModalLabel">Conferma Eliminazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Sei sicuro di voler eliminare questo file?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Elimina</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal per avviso modifica -->
<?php 
    get_template_part('modals/confirm_edit_document');
    get_template_part('modals/prohibit_edit_document');
