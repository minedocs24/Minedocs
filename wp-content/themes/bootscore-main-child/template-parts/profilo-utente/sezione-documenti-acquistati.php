<?php

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: devi essere loggato per visualizzare i tuoi file.';
    return;
}

// Ottieni l'ID dell'utente loggato

?>

<div id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class="my-5">
        <div class="table-card ">
            <div class="table-card-header d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <h2 class="table-card-header-text text-center text-sm-start">I miei documenti acquistati</h2>
                <a href="<?php echo RICERCA_PAGE; ?>" class="upload-btn-table btn-cta mt-2 mt-sm-0">Cerca un file</a>
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
                    <select id="itemsPerPageSelect" class="form-select numero-elementi-per-pagina w-auto">
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

<style>
    .rating-box {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    align-items: center;
    font-size: 2rem;
    /* Dimensione delle stelle */
    gap: 0.5rem;
    /* Spazio tra le stelle */
}

/* Nascondi gli input radio */
.rating-box input[type="radio"] {
    display: none;
}

/* Stile base delle stelle */
.rating-box label {
    color: #ccc;
    /* Colore stelle inattive */
    cursor: pointer;
    transition: color 0.3s;
}

/* Cambia il colore delle stelle al passaggio del mouse (solo se non readonly) */
.rating-box:not(.readonly) label:hover,
.rating-box:not(.readonly) label:hover~label {
    color: #ffcc00;
    /* Colore stelle al passaggio del mouse */
}

/* Cambia il colore delle stelle selezionate */
.rating-box input[type="radio"]:checked~label {
    color: #ffcc00;
    /* Colore stelle selezionate */
}

/* Gestione della classe readonly per evitare modifiche */
.rating-box.readonly label {
    cursor: default;
    pointer-events: none;
    /* Disabilita interazioni */
}

/* Assicura che le stelle selezionate siano sempre visibili anche in readonly */
.rating-box.readonly input[type="radio"]:checked~label {
    color: #ffcc00;
    /* Colore stelle selezionate */
}


</style>