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
                                <th class="d-none d-sm-table-cell">Descrizione</th>
                                
                                <th class="d-none d-sm-table-cell">Università</th>
                                <th class="d-none d-sm-table-cell">Corso di laurea</th>
                                <th class="d-none d-sm-table-cell">Materia</th>
                                <th class="d-none d-sm-table-cell">Anno accademico</th>
                                <th>Stato</th>
                                <th class="d-none d-sm-table-cell">Punti Pro</th>
                                <th class="d-none d-sm-table-cell">Punti Blu</th>
                                <th class="d-none d-sm-table-cell">Data caricamento</th>
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


    let currentPage = 1;
    let rowsPerPage = parseInt(numElementsPerPageSelect.val(), 10); // Numero di righe per pagina
    const numColumns = $('#file-table thead th').length; // Estrai dinamicamente il numero di colonne dalla tabella

    let postIdToDelete; // Variabile per memorizzare l'ID del file da eliminare

    



    let uploadedDocuments = [];
    let filteredUploadedDocuments = [];
    let paginatedFiles = [];
    let currentType = 'all'; // Tipo di file corrente (pro, all, free)
    let currentSearchText = ''; // Testo di ricerca corrente


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


    function fetchUploadedDocuments() {
        $.ajax({
            url: env_profilo_utente_documenti_caricati.ajax_url,
            type: 'POST',
            data: {
                action: 'get_user_uploaded_documents',
                nonce: env_profilo_utente_documenti_caricati.nonce_load_user_uploaded_documents
            },
            beforeSend: drawLoadingAnimation, // Mostra l'animazione di caricamento prima della richiesta
            success: function(response) {
                if (response.success) {
                    uploadedDocuments = response.data;
                    console.log('Uploaded documents:', uploadedDocuments); // Debugging
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


    /**
     * 
     * @param {*} testo filtro testuale che deve essere utilizzato per filtrare i file in base al contenuto
     * @param {*} tipo "pro", "all" o "free" per filtrare i file in base al tipo
     * @returns nulla
     */

    function filterFiles() {
        
        testo = currentSearchText;
        tipo = currentType;

        console.log('Filtering files with text:', testo, 'and type:', tipo); // Debugging

        let testoLower = testo.toLowerCase();
        let filteredByType = uploadedDocuments.filter(function(file) {
            return tipo == 'all' || file.tipo == tipo;
        });

        filteredUploadedDocuments = filteredByType.filter(function(file) {
            return file.name.toLowerCase().includes(testoLower) || 
                   file.description.toLowerCase().includes(testoLower) || 
                   file.nome_istituto.toLowerCase().includes(testoLower) || 
                   file.nome_corso.toLowerCase().includes(testoLower) || 
                   file.nome_corso_di_laurea.toLowerCase().includes(testoLower) || 
                   file.anno_accademico.toLowerCase().includes(testoLower);
        });

        console.log('Filtered files:', filteredUploadedDocuments); // Debugging

        const totalPages = Math.ceil(filteredUploadedDocuments.length / rowsPerPage);

        // Divide i risultati in pagine all'interno dell'array stesso

        paginatedFiles = []; // Pulisci l'array paginato prima di riempirlo
        for (let i = 0; i < totalPages; i++) {
            const startIndex = i * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            paginatedFiles.push(filteredUploadedDocuments.slice(startIndex, endIndex));
        }

        console.log('Paginated files:', paginatedFiles); // Debugging

        renderTable(); // Rendi la tabella con i file filtrati e paginati

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
            const row = `<tr data-post-id="${file.hid}" data-costo-pro="${file.costo_in_punti_pro}">
                <td class="text-center">
                    <a href="#" 
                       class="btn-actions btn-edit" 
                       data-id="${file.hid}" 
                       data-status="${file.stato_approvazione}" 
                       title="Modifica">
                        <img src="${env_profilo_utente_documenti_caricati.assets_url}/img/user/sezione-documenti-caricati/matita.png" alt="Modifica" width="16" height="16" />
                    </a>
                    <a href="${file.document_link}" target="_blank" class="btn-actions btn-link-table" title="Visualizza">
                        <img src="${env_profilo_utente_documenti_caricati.assets_url}/img/user/sezione-documenti-caricati/eye.png" alt="Apri" width="16" height="16" />
                    </a>
                    <button class="btn-actions delete-btn" data-id="${file.post_id}" title="Elimina">
                        <img src="${env_profilo_utente_documenti_caricati.assets_url}/img/user/sezione-documenti-caricati/trash.png" alt="Elimina" width="16" height="16" />
                    </button>
                </td>
                <td class="data-cell"  tooltip="${file.name}">
                    ${file.name.length > 10 ? file.name.substring(0, 10) + '...' : file.name}
                </td>
                <td class="data-cell d-none d-sm-table-cell"  tooltip="${file.description}">
                    ${file.description.length > 10 ? file.description.substring(0, 10) + '...' : file.description}
                </td>
                <td class="data-cell d-none d-sm-table-cell">${file.nome_istituto}</td>
                <td class="data-cell d-none d-sm-table-cell">${file.nome_corso_di_laurea}</td>
                <td class="data-cell d-none d-sm-table-cell">${file.nome_corso}</td>
                <td class="data-cell d-none d-sm-table-cell">${file.anno_accademico}</td>
                <td class="data-cell">
                    ${file.status === 'publish' && file.stato_approvazione === 'approvato' ? '<span class="badge bg-success">Approvato e pubblicato</span>' :
                      file.status === 'draft' && file.stato_approvazione === 'in_approvazione' ? '<span class="badge bg-secondary">In revisione</span>' :
                      file.status === 'draft' && file.stato_approvazione === 'non_approvato' ? '<span class="badge bg-danger">Non approvato</span>' :
                      file.status === 'draft' && file.stato_prodotto === 'eliminato_utente' ? '<span class="badge bg-danger">Eliminato</span>' :
                      file.status === 'draft' && file.stato_prodotto === 'eliminato_admin' ? '<span class="badge bg-danger">Eliminato</span>' :
                      file.status === 'draft' && file.stato_prodotto === 'nascosto_aggiornamento' ? '<span class="badge bg-danger">Nascosto per aggiornamento</span>' :
                      '<span class="badge bg-warning">Contatta un admin!</span>'}
                </td>
                <td class="data-cell d-none d-sm-table-cell">${file.costo_in_punti_pro}</td>
                <td class="data-cell d-none d-sm-table-cell">${file.costo_in_punti_blu}</td>
                <td class="data-cell d-none d-sm-table-cell">${file.upload_date}</td>
            </tr>`;
            $tableBody.append(row); // Aggiungi la riga alla tabella
        });
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

    fetchUploadedDocuments();


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




    function cancellaFile(hid){
        uploadedDocuments = uploadedDocuments.filter(function(file) {
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









    

    $confirmDeleteButton.on('click', function() {
        if (!postIdToDelete) return;

        $confirmDeleteButton.html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Elimina'
        );

        $.ajax({
            url: env_profilo_utente_documenti_caricati.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_user_file',
                post_id: postIdToDelete,
                nonce: env_profilo_utente_documenti_caricati.delete_nonce
            },
            success: function(response) {
                if (response.success) {
                    cancellaFile(postIdToDelete); // Rimuovi il file dall'array
                    $deleteModal.modal('hide');
                    postIdToDelete = null;
                } else {
                    $deleteModal.modal('hide');
                    // Create and show an error modal dynamically using Bootstrap
                    showErrorModal('Errore durante l\'eliminazione del file.');
                    console.error(response.data);
                }
                $confirmDeleteButton.html('Elimina');
            },
            error: function() {
                showCustomAlert('Errore', 'Errore durante la richiesta.', 'bg-danger btn-danger');
                $confirmDeleteButton.html('Elimina');
            }
        });
    });

    $tableBody.on('click', '.delete-btn', function() {
        const $row = $(this).closest('tr');
        postIdToDelete = $row.data('post-id'); // Memorizza l'ID del post

        $deleteModal.modal('show');
    });



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
        const editUrl = `${env_profilo_utente_documenti_caricati.edit_page_url}/?post_id=${postId}`;
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




});