jQuery(document).ready(function($) {
    
    
    
    const tableContainer = $('#table-container');
    const numElementsPerPageSelect = $('#itemsPerPageSelect');
    
    function drawTableSkeleton() {
        const skeleton = `
                        <table class="table table-profilo align-middle" id="file-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Azioni</th>
                                    <th>Nome</th>
                                    <th>Descrizione</th>
                                    <th>Materia</th>
                                    <th>Anno accademico</th>
                                    <th>Recensione</th>
                                    <th>Data caricamento</th>
                                </tr>
                            </thead>
                            <tbody id="file-table-body">
                                
                            </tbody>
                        </table>`;
        tableContainer.html(skeleton);


    }

    drawTableSkeleton();


    const $searchInput = $('#search-input');
    const $prevPageBtn = $('#prev-page');
    const $nextPageBtn = $('#next-page');
    const $pageInfo = $('#page-info');
    const $tableBody = $('#file-table-body');
    const $deleteModal = $('#deleteFileModal');
    const $confirmDeleteButton = $('#confirmDeleteButton');
    const $editModal = $('#editFileModal');
    const $notEditableModal = $('#notEditableModal');
    const $notEditableModalBody = $notEditableModal.find('.modal-body');
    const $confirmEditButton = $('#confirmEditButton');
    const menuItems = document.querySelectorAll(".menu-item");
    const filterProBtn = document.getElementById("filter-pro");
    const filterAllBtn = document.getElementById("filter-all");
    const filterFreeBtn = document.getElementById("filter-blu");
    const pagination = $('#pagination-controls');   
    const filterGroupsDropdown = $('#filter-dropdown');


    let currentPage = 1;
    let rowsPerPage = parseInt(numElementsPerPageSelect.val(), 10); // Numero di righe per pagina
    const numColumns = $('#file-table thead th').length; // Estrai dinamicamente il numero di colonne dalla tabella

    let postIdToDelete; // Variabile per memorizzare l'ID del file da eliminare

    



    let purchasedDocuments = [];
    let filteredpurchasedDocuments = [];
    let paginatedFiles = [];
    let groups = []; // Array per memorizzare i gruppi di file paginati
    let currentType = 'all'; // Tipo di file corrente (pro, all, free)
    let currentSearchText = ''; // Testo di ricerca corrente
    let currentGroup = 'tutti'; // Gruppo corrente (tutti o specifico)


    function drawLoadingAnimation() {
        $tableBody.html(`
            <tr>
            <td colspan="${numColumns}" class="text-center">
                <div class="loading-animation my-5">
                <img src="${env_ricerca.logo}" alt="Caricamento..." class="loading-logo" />
                <p class="loading-text">Caricamento in corso...</p>
                </div>
            </td>
            </tr>
        `);

        $('.loading-logo').css({
            'animation': 'pulse 1s infinite',
            'display': 'block',
            'margin': '0 auto',
            'width': '200px',
        });

        $('<style>')
            .prop('type', 'text/css')
            .html('@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }')
            .appendTo('head');
    }


    function fetchpurchasedDocuments() {
        $.ajax({
            url: env_profilo_utente_documenti_acquistati.ajax_url,
            type: 'POST',
            data: {
                action: 'get_user_purchased_files',
                nonce: env_profilo_utente_documenti_acquistati.user_purchased_nonce
            },
            beforeSend: drawLoadingAnimation, // Mostra l'animazione di caricamento prima della richiesta
            success: function(response) {
                if (response.success) {
                    purchasedDocuments = response.data;

                    console.log('Uploaded documents:', purchasedDocuments); // Debugging
                   
                    //caricaGruppi();
                    //renderTable(currentPage);
                } else {
                    showCustomAlert('Errore', 'Errore durante il recupero dei documenti.', 'bg-danger btn-danger');
                    console.error(response.data);
                }
                filterFiles(); // Filtra i file dopo il recupero
            },
            error: function() {
                showCustomAlert('Errore', 'Errore durante la richiesta.', 'bg-danger btn-danger');
            }
        });
    }

    function caricaGruppi() {
        groups = [...new Set(filteredpurchasedDocuments.map(file => file.gruppo))];
        console.log('Gruppi:', groups); // Debugging
        filterGroupsDropdown.empty(); // Pulisci il dropdown prima di aggiungere i nuovi gruppi
        filterGroupsDropdown.append('<option value="tutti">Tutti</option>'); // Aggiungi l'opzione "Tutti"

        groups.forEach(group => {
            filterGroupsDropdown.append(`<option value="${group}">${group}</option>`); // Aggiungi ogni gruppo come opzione
        });
    }


    /**
     * 
     * @param {*} testo filtro testuale che deve essere utilizzato per filtrare i file in base al contenuto
     * @param {*} tipo "pro", "all" o "free" per filtrare i file in base al tipo
     * @returns nulla
     */

    function filterFiles() {
        
        testo = currentSearchText;
        tipo = currentType;
        gruppo = currentGroup;
        


        console.log('Filtering files with text:', testo, 'and type:', tipo, 'and group:', gruppo); // Debugging

        let testoLower = testo.toLowerCase();
        let filteredByType = purchasedDocuments.filter(function(file) {
            return tipo == 'all' || file.tipo == tipo;
        });

        filteredpurchasedDocuments = filteredByType.filter(function(file) {
            return file.name.toLowerCase().includes(testoLower) || 
                   file.description.toLowerCase().includes(testoLower) || 
                   file.nome_istituto.toLowerCase().includes(testoLower) || 
                   file.nome_corso.toLowerCase().includes(testoLower) || 
                   file.nome_corso_di_laurea.toLowerCase().includes(testoLower) || 
                   file.anno_accademico.toLowerCase().includes(testoLower);
        });

        // Filtra in base al gruppo selezionato
        if (gruppo !== 'tutti') {
            filteredpurchasedDocuments = filteredpurchasedDocuments.filter(function(file) {
                return file.gruppo === gruppo;
            });
        }

        console.log('Filtered files:', filteredpurchasedDocuments); // Debugging

        const totalPages = Math.ceil(filteredpurchasedDocuments.length / rowsPerPage);

        // Divide i risultati in pagine all'interno dell'array stesso

        paginatedFiles = []; // Pulisci l'array paginato prima di riempirlo
        for (let i = 0; i < totalPages; i++) {
            const startIndex = i * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            paginatedFiles.push(filteredpurchasedDocuments.slice(startIndex, endIndex));
        }

        console.log('Paginated files:', paginatedFiles); // Debugging
        caricaGruppi(); // Carica i gruppi per il dropdown



        renderTable(); // Rendi la tabella con i file filtrati e paginati
        filterGroupsDropdown.val(gruppo); // Imposta il valore del dropdown al gruppo corrente

    }

    function renderTable() {

        //carica paginazione
        renderPagination();

        if(paginatedFiles.length === 0) {
            $tableBody.html(`<tr><td colspan="${numColumns}" class="text-center">Nessun file trovato.</td></tr>`);
        } else {
            $tableBody.empty(); // Pulisci la tabella prima di aggiungere i nuovi file
            renderPage(currentPage)
        }


        
    }

    function renderPagination() {
        const totalPages = paginatedFiles.length;
        currentPage = Math.max(Math.min(currentPage, totalPages),1); // Assicurati che la pagina corrente non superi il numero totale di pagine
        $prevPageBtn.prop('disabled', currentPage === 1 || totalPages === 0);
        $nextPageBtn.prop('disabled', currentPage === totalPages || totalPages === 0);

        if (totalPages === 0) {
            $pageInfo.text(`Pagina 1 di 1`);;
            return;
        }
        $pageInfo.text(`Pagina ${currentPage} di ${totalPages}`);;

        // Mostra/nascondi i controlli di paginazione
        //pagination.toggle(totalPages > 1);


    }

    function renderPage(page) {
        if (page < 1 || page > paginatedFiles.length) return; // Controlla se la pagina è valida

        currentPage = page; // Aggiorna la pagina corrente
        const filesToShow = paginatedFiles[page - 1]; // Ottieni i file da mostrare per la pagina corrent
        $tableBody.empty(); // Pulisci la tabella prima di aggiungere i nuovi file
        filesToShow.forEach(function(file) {
            const row = `<tr data-key-gruppo="${file.key}" 
                           data-post-id="${file.hid}" 
                           data-costo-pro="${file.costo_in_punti_pro}">
                <td class="text-center">
                    <a href="${file.document_link}" target="_blank" 
                       class="btn-actions btn-link-table" title="Visualizza">
                        <img src="${env_profilo_utente_documenti_acquistati.assets_url}/img/user/sezione-documenti-caricati/eye.png" 
                             alt="Apri" width="16" height="16" />
                    </a>
                    ${file.costo_in_punti_pro > 0 ? file.pulsante_fatturazione : ''}
                </td>
                <td class="data-cell" tooltip="${file.name}">
                    ${file.name.length > 10 ? file.name.substring(0, 10) + '...' : file.name}
                </td>
                <td class="data-cell" tooltip="${file.description}">
                    ${file.description.length > 10 ? file.description.substring(0, 10) + '...' : file.description}
                </td>
                <td>${file.nome_corso}</td>
                <td>${file.anno_accademico}</td>
                <td>
                    <div id="rating-cell-post-${file.hid}" class="rating">
                        ${file.recensione_utente_voto ? 
                            `<div class="rating-stars">⭐ ${file.recensione_utente_voto}</div>` : 
                            `<button class="btn-actions btn-mini-scrivi-recensione" 
                                data-post-id="${file.hid}" 
                                title="Recensisci" 
                                data-title="${file.name}">
                                <img src="${env_profilo_utente_documenti_acquistati.assets_url}img/user/Icone_Minedocs_review_white.svg" 
                                     alt="Recensisci" width="24" height="24" />
                             </button>`}
                    </div>
                </td>
                <td>${file.upload_date}</td>
            </tr>`;
            $tableBody.append(row); // Aggiungi la riga alla tabella
        });


        caricaControlli();
    }

    // Gestione del click sul pulsante "Pagina precedente"
    $prevPageBtn.on('click', function() {
        if (currentPage > 1) {
            renderPage(currentPage - 1); // Passa alla pagina precedente
            renderPagination(); // Rendi la paginazione
        }
    });

    // Gestione del click sul pulsante "Pagina successiva"
    $nextPageBtn.on('click', function() {
        if (currentPage < paginatedFiles.length) {
            renderPage(currentPage + 1); // Passa alla pagina successiva
            renderPagination(); // Rendi la paginazione
        }
    });

    fetchpurchasedDocuments();


    filterAllBtn.addEventListener("click", function() {
        setActiveButton(this);
        currentType = 'all'; // Aggiorna il tipo corrente
        filterFiles();
    });
    filterProBtn.addEventListener("click", function() {
        setActiveButton(this);
        currentType = 'pro'; // Aggiorna il tipo corrente
        filterFiles();
    });
    filterFreeBtn.addEventListener("click", function() {
        setActiveButton(this);
        currentType = 'blu'; // Aggiorna il tipo corrente
        filterFiles();
    });


    $searchInput.on('input', function() {
        const searchText = $(this).val().toLowerCase();
        currentSearchText = searchText; // Aggiorna il testo di ricerca corrente
        filterFiles();
    });

    numElementsPerPageSelect.on('change', function() {
        const selectedValue = parseInt($(this).val(), 10);
        if (!isNaN(selectedValue) && selectedValue > 0) {
            rowsPerPage = selectedValue; // Aggiorna il numero di righe per pagina
            filterFiles(); // Rendi la tabella aggiornata
        }
    });


    filterGroupsDropdown.on('change', function() {
        const selectedGroup = $(this).val();
        currentGroup = selectedGroup; // Aggiorna il gruppo corrente

        if (selectedGroup === 'tutti') {
            currentGroup = 'tutti'; // Se l'utente seleziona "Tutti", imposta il gruppo su "tutti"
        } else {
            currentGroup = selectedGroup; // Altrimenti, imposta il gruppo selezionato
        }
        filterFiles(); // Rendi la tabella aggiornata
    });







    function cancellaFile(hid){
        purchasedDocuments = purchasedDocuments.filter(function(file) {
            return file.hid !== hid;
        });
        console.log('File cancellato:', hid); // Debugging
        filterFiles(); // Rendi la tabella aggiornata

    }


    // Funzione per mostrare un modal di errore
    function showErrorModal(message) {
        const $errorModal = $(`
            <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Errore</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ${message}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
                </div>
            </div>
            </div>
        `);

        $('body').append($errorModal);
        $errorModal.modal('show');

        $errorModal.on('hidden.bs.modal', function () {
            $errorModal.remove(); // Rimuovi il modal dal DOM dopo la chiusura
        });
    }







    // Funzione per gestire lo stato 'active'
    function setActiveButton(button) {
        menuItems.forEach(item => item.classList.remove("active"));
        button.classList.add("active");
    }

    

    // Gestione del click sul pulsante "Modifica"
    $('#file-table-body').on('click', '.btn-edit', function(e) {
        e.preventDefault();
        const postId = $(this).data('id');
        const status = $(this).data('status');
        const editUrl = `${env_profilo_utente_documenti_acquistati.edit_page_url}/?post_id=${postId}`;
        console.log('Status:', status); // Debugging
        if (status === 'in_approvazione') {
            // Mostra il modal informativo per documenti in revisione
            $notEditableModalBody.text('Questo documento sarà modificabile solo dopo l\'approvazione da parte degli amministratori.');
            $notEditableModal.modal('show');
        } else if (status === 'non_approvato') {
            // Mostra il modal informativo per documenti rifiutati
            $notEditableModalBody.text('Questo documento è stato rifiutato dagli amministratori e non può essere modificato.');
            $notEditableModal.modal('show');
        } else if (status === 'approvato'){
            // Imposta il link di conferma nel modal
            $confirmEditButton.attr('href', editUrl);

            // Mostra il modal di conferma modifica
            $editModal.modal('show');
        } else if (status === null || status === 'non_impostato') {
            // Mostra il modal informativo per documenti con stato non definito
            $notEditableModalBody.text('Impossibile modificare questo documento. Si prega di contattare gli amministratori per ulteriori informazioni.');
            $notEditableModal.modal('show');
        }
    });


    function caricaControlli() {
    
        // Aggiungi un listener a tutti i pulsanti per aprire la modal
        document.querySelectorAll('.btn-mini-scrivi-recensione').forEach(button => {
            button.addEventListener('click', function () {
                const postId = this.getAttribute('data-post-id'); // Ottieni il post ID
                const postTitle = this.getAttribute('data-title'); // Ottieni il titolo del post
                openReviewModal(postId, postTitle);
            });
        });

        caricaPulsantiFatturazione();

    }

    
    function openReviewModal(postId, postTitle) {
        // Rimuovi eventuali modali già esistenti
        const existingModal = document.getElementById('reviewModal');
        if (existingModal) {
            existingModal.remove();
        }
    
        // Crea il contenuto HTML della modal
        const modalHTML = `
            <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Inserisci la tua recensione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <form id="reviewForm">
    
                        <h6><strong>Recensisci il documento ${postTitle}</strong></h6>
                        <div class="mb-3">
                            
                            <label for="reviewRating" class="form-label">Voto</label>
    
                            <div id="reviewRating" class="rating-box">
                                <input type="radio" id="star5" name="rating" value="5">
                                <label for="star5" title="5 stars" tooltip="Eccezionale 🤩">&#9733;</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4" title="4 stars" tooltip="Molto buono 😄">&#9733;</label>                              
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3" title="3 stars" tooltip="Migliorabile 😕">&#9733;</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2" title="2 stars" tooltip="Deludente ☹️">&#9733;</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1" title="1 star" tooltip="Davvero scadente 😵‍💫">&#9733;</label> 
                            </div>
                        </div>
                        <div class="mb-3">
                        
                        <label for="reviewTextarea" class="form-label">Scrivi la tua recensione</label>
                        <textarea class="form-control" id="reviewTextarea" rows="4" placeholder="Inserisci la tua recensione qui..."></textarea>
                        </div>
                        
    
                        <input type="hidden" id="postHid" value="${postId}">
                    </form>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="submitReviewButton-${postId}">
                        <div id="icon-loading-send-review" class="btn-loader mx-2" hidden style="display: inline-block;">
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                        Invia Recensione</button>
                    </div>
                </div>
                </div>
            </div>
        `;
    
        // Aggiungi la modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    
        // Inizializza la modal con Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
        modal.show();
    
        // Aggiungi il listener per inviare la recensione
        document.getElementById('submitReviewButton-' + postId).addEventListener('click', function () {
            submitReview();
        });
    }
    
    function submitReview() {
        const reviewText = document.getElementById('reviewTextarea').value;
        const postHid = document.getElementById('postHid').value;
    
        if (!reviewText.trim()) {
            showCustomAlert('Errore', 'La recensione non può essere vuota!', 'bg-danger btn-danger');
            return;
        }
    
        const rating = document.querySelector('input[name="rating"]:checked');
        if (!rating) {
            showCustomAlert('Errore', 'Seleziona un voto!', 'bg-danger btn-danger');
            return;
        }
    
        const ratingValue = rating.value;

                // Chiudi la modal dopo l'invio (puoi sostituire con una chiamata AJAX reale)
        const modal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
                //modal.hide();
            
                // Rimuovi la modal dal DOM
                
        const iconLoadingSendReview = $('#icon-loading-send-review');
        iconLoadingSendReview.prop('hidden', false);
    
    
        // Effettua una chiamata AJAX per inviare la recensione
        $.ajax({
            url: env_profilo_utente_documenti_acquistati.ajax_url,
            type: 'POST',
            data: {
                action: 'submit_review',
                comment_post_Hid: postHid,
                comment: reviewText,
                rating: ratingValue,
                nonce: env_profilo_utente_documenti_acquistati.write_review_nonce
            },
            success: function(response) {
                
                if (response.success) {
                    showCustomAlert('Recensione inviata con successo!', 'Ti ringraziamo per la tua recensione', 'bg-success btn-success');
                    //alert('Recensione inviata con successo!');

                    // Aggiorna la pagina per visualizzare la recensione appena inviata
                    const reviewCell = $('#rating-cell-post-' + postHid);
                    reviewCell.html(`<div class="rating-stars">⭐ ${ratingValue}</div>`);


                } else {
                    showCustomAlert('Errore durante l\'invio della recensione', 'Si è verificato un errore durante l\'invio della recensione.', 'bg-danger btn-danger');
                    //alert('Errore durante l\'invio della recensione: ' + response.data);
                }
            },
            error: function() {
                
                showCustomAlert('Errore durante l\'invio della recensione', 'Si è verificato un errore durante l\'invio della recensione.', 'bg-danger btn-danger');
                //alert('Si è verificato un errore durante l\'invio della recensione.');
            },
            complete: function() {
                iconLoadingSendReview.prop('hidden', true);
                // Chiudi la modal dopo l'invio (puoi sostituire con una chiamata AJAX reale)
                modal.hide();
                // Rimuovi la modal dal DOM
                //modal.dispose();
            }
        });

    
    }


});