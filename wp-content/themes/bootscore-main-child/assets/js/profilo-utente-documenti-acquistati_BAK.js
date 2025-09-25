jQuery(document).ready(function($) {
    const $searchInput = $('#search-input');
    const $prevPageBtn = $('#prev-page');
    const $nextPageBtn = $('#next-page');
    const $pageInfo = $('#page-info');
    const $tableBody = $('tbody');
    const $deleteModal = $('#deleteFileModal');
    const $confirmDeleteButton = $('#confirmDeleteButton');

    let currentPage = 1;
    const rowsPerPage = 5;
    let $filteredFiles = $tableBody.children('tr.file-row').not('.details-row').not('.group-header');
    let postIdToDelete; // Variabile per memorizzare l'ID del file da eliminare

    function renderTable(page) {
        $tableBody.children('tr').hide();

        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        let visibleRowCount = 0;
        $filteredFiles.each(function() {
            const $row = $(this);
            if (!$row.hasClass('details-row')) {
                if (visibleRowCount >= start && visibleRowCount < end) {
                    $row.show();
                } else {
                    $row.hide();
                }
                visibleRowCount++;
            }
        });

        $('.details-row').hide();
        updatePaginationControls();
        showNoResultsMessage();
    }

    function updatePaginationControls() {
        const totalPages = Math.max(1, Math.ceil($filteredFiles.not('.details-row').length / rowsPerPage));

        $pageInfo.text(`Pagina ${currentPage} di ${totalPages}`);
        $prevPageBtn.prop('disabled', currentPage === 1);
        $nextPageBtn.prop('disabled', currentPage === totalPages);
    }

    function filterTable(query) {
        const lowerCaseQuery = query.toLowerCase();

        $filteredFiles = $tableBody.children('tr').filter(function() {
            const $row = $(this);
            if ($row.hasClass('details-row') || $row.hasClass('group-header')) return false;
            const text = $row.text().toLowerCase();
            return text.includes(lowerCaseQuery);
        });

        currentPage = 1;
        renderTable(currentPage);
    }

    function showNoResultsMessage() {
        $('#no-results-message').remove();
        if ($filteredFiles.length === 0) {
            if ($('#no-results-message').length === 0) {
                $tableBody.append(
                    '<tr id="no-results-message"><td colspan="7" class="text-center">Non ci sono risultati</td></tr>'
                );
            }
        } else {
            $('#no-results-message').remove();
        }
    }

    $searchInput.on('input', function() {
        const query = $searchInput.val().trim();
        filterTable(query);
    });

    $prevPageBtn.on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable(currentPage);
        }
    });

    $nextPageBtn.on('click', function() {
        const totalPages = Math.ceil($filteredFiles.length / rowsPerPage);
        //console.log('Total Pages:', totalPages);
        //console.log('Filtered files Len:', $filteredFiles.length);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable(currentPage);
        }
    });

    $tableBody.on('click', '.delete-btn', function() {
        const $row = $(this).closest('tr');
        postIdToDelete = $row.data('post-id'); // Memorizza l'ID del post

        $deleteModal.modal('show');
    });

    $confirmDeleteButton.on('click', function() {
        if (!postIdToDelete) return;

        $confirmDeleteButton.html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Elimina'
        );

        $.ajax({
            url: env_profilo_utente_documenti_acquistati.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_user_file',
                post_id: postIdToDelete,
                nonce: env_profilo_utente_documenti_acquistati.delete_nonce
            },
            success: function(response) {
                if (response.success) {
                    $(`[data-post-id="${postIdToDelete}"]`).remove();
                    $deleteModal.modal('hide');
                    postIdToDelete = null;
                } else {
                    showCustomAlert('Errore', 'Errore durante l\'eliminazione del file.', 'bg-danger btn-danger');
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

    const menuItems = document.querySelectorAll(".menu-item");
    const filterProBtn = document.getElementById("filter-pro");
    const filterAllBtn = document.getElementById("filter-all");
    const filterFreeBtn = document.getElementById("filter-blu");

    // Funzione per gestire lo stato 'active'
    function setActiveButton(button) {
        menuItems.forEach(item => item.classList.remove("active"));
        button.classList.add("active");
    }

    // Mostra tutti i file
    filterAllBtn.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveButton(this);
        $filteredFiles = $tableBody.children('tr').not('.details-row').not('.group-header');
        currentPage = 1;
        renderTable(currentPage);
    });

    // Mostra solo i file a pagamento
    filterProBtn.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveButton(this);
        $filteredFiles = $tableBody.children('tr').filter(function() {
            const $row = $(this);
            if ($row.hasClass('details-row') || $row.hasClass('group-header')) return false;
            const costoPro = parseFloat($row.attr("data-costo-pro")) || 0;
            return costoPro > 0;
        });
        currentPage = 1;
        renderTable(currentPage);
    });

    // Mostra solo i file gratuiti
    filterFreeBtn.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveButton(this);
        $filteredFiles = $tableBody.children('tr').filter(function() {
            const $row = $(this);
            if ($row.hasClass('details-row') || $row.hasClass('group-header')) return false;
            const costoPro = parseFloat($row.attr("data-costo-pro")) || 0;
            return costoPro === 0;
        });
        currentPage = 1;
        renderTable(currentPage);
    });

    //renderTable(currentPage);




        document.querySelectorAll('.group-table').forEach(function(table) {
            table.style.display = 'block';
        });
    
        document.getElementById('filter-dropdown').addEventListener('change', function() {
            var selectedGroup = this.value;
            document.querySelectorAll('.group-header, .group-header + tr').forEach(function(row) {
                if (selectedGroup === 'tutti' || row.getAttribute('data-group') === selectedGroup) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
    
            document.querySelectorAll('.group-header').forEach(function(header) {
                var group = header.getAttribute('data-group');
                var rows = document.querySelectorAll('tr[data-key-gruppo="' + group + '"]');
                var showGroup = false;
    
                rows.forEach(function(row) {
                    if (selectedGroup === 'tutti' || group === selectedGroup) {
                        row.style.display = '';
                        showGroup = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
    
                if (showGroup) {
                    header.style.display = '';
                } else {
                    header.style.display = 'none';
                }
            });
        });

    

        // Aggiungi un listener a tutti i pulsanti per aprire la modal
        document.querySelectorAll('.btn-mini-scrivi-recensione').forEach(button => {
            button.addEventListener('click', function () {
                const postId = this.getAttribute('data-post-id'); // Ottieni il post ID
                const postTitle = this.getAttribute('data-title'); // Ottieni il titolo del post
                openReviewModal(postId, postTitle);
            });
        });

    
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
                                <label for="star5" title="5 stars">&#9733;</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4" title="4 stars">&#9733;</label>                              
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3" title="3 stars">&#9733;</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2" title="2 stars">&#9733;</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1" title="1 star">&#9733;</label> 
                            </div>
                        </div>
                        <div class="mb-3">
                        
                        <label for="reviewTextarea" class="form-label">Scrivi la tua recensione</label>
                        <textarea class="form-control" id="reviewTextarea" rows="4" placeholder="Inserisci la tua recensione qui..."></textarea>
                        </div>
                        
    
                        <input type="hidden" id="postId" value="${postId}">
                    </form>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="submitReviewButton">
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
        document.getElementById('submitReviewButton').addEventListener('click', function () {
            submitReview();
        });
    }
    
    function submitReview() {
        const reviewText = document.getElementById('reviewTextarea').value;
        const postId = document.getElementById('postId').value;
    
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
                comment_post_ID: postId,
                comment: reviewText,
                rating: ratingValue,
                nonce: env_profilo_utente_documenti_acquistati.write_review_nonce
            },
            success: function(response) {
                
                if (response.success) {
                    showCustomAlert('Recensione inviata con successo!', 'Ti ringraziamo per la tua recensione', 'bg-success btn-success');
                    //alert('Recensione inviata con successo!');

                    // Aggiorna la pagina per visualizzare la recensione appena inviata
                    const reviewCell = $('#rating-cell-post-' + postId);
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
