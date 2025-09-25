function showDeleteModal(postId) {
    const deleteModal = document.getElementById('deleteModal');
    const confirmDeleteButton = document.getElementById('confirmDelete');
    
    if (deleteModal && confirmDeleteButton) {
        deleteModal.classList.add('show');
        deleteModal.style.display = 'block';
        
        confirmDeleteButton.onclick = function() {
            deletePost(postId, deleteModal, confirmDeleteButton);
        };
    }
}

function deletePost(postId, deleteModal, confirmDeleteButton) {
    const formData = new FormData();
    formData.append('action', 'delete_post');
    formData.append('post_id', postId);
    formData.append('nonce', deletePostNonce);

    fetch(ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert('Errore durante l\'eliminazione del documento');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante l\'eliminazione del documento');
    })
    .finally(() => {
        deleteModal.classList.remove('show');
        deleteModal.style.display = 'none';
        confirmDeleteButton.onclick = null;
    });
}

// Gestione del visualizzatore PDF
document.addEventListener('DOMContentLoaded', function() {
    const pdfViewer = document.querySelector('.pdf-viewer');
    const pdfContainer = document.querySelector('.pdf-container');
    console.log(pdfViewer, pdfContainer);
    if (pdfViewer && pdfContainer) {
        // Disabilita il download del PDF
        pdfViewer.style.pointerEvents = 'none';
        
        // Gestione dello zoom
        const zoomIn = document.getElementById('zoomIn');
        const zoomOut = document.getElementById('zoomOut');
        
        if (zoomIn && zoomOut) {
            let currentZoom = 1;
            
            zoomIn.addEventListener('click', function() {
                currentZoom += 0.1;
                pdfContainer.style.transform = `scale(${currentZoom})`;
            });
            
            zoomOut.addEventListener('click', function() {
                if (currentZoom > 0.5) {
                    currentZoom -= 0.1;
                    pdfContainer.style.transform = `scale(${currentZoom})`;
                }
            });
        }
    }
});

document.addEventListener("DOMContentLoaded", function () {
    var showReviewsButton = document.getElementById('show-reviews');
    var reviewsModal = new bootstrap.Modal(document.getElementById('reviewsModal'));

    showReviewsButton.addEventListener('click', function() {
        reviewsModal.show();
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    
    document.getElementById('showReviewFormButton').addEventListener('click', function() {
        var reviewCard = document.getElementById('scrivi-recensione');
        reviewCard.style.display = 'block';
        reviewCard.style.opacity = 0;
        reviewCard.style.transition = 'opacity 0.5s';
        setTimeout(function() {
            reviewCard.style.opacity = 1;
        }, 10);
        document.getElementById('showReviewFormButton').style.display = 'none';
    });

    // jQuery(document).ready(function ($) {
    //     // Filtra recensioni per rating
    //     $(".filter-rating").click(function () {
    //         var selectedRating = $(this).data("rating");
    
    //         $(".review-card").each(function () {
    //             var reviewRating = $(this).data("rating");
    //             if (reviewRating == selectedRating) {
    //                 $(this).fadeIn();
    //             } else {
    //                 $(this).fadeOut();
    //             }
    //         });
    //     });
    
    //     // Ordina le recensioni
    //     $("#sort-reviews").change(function () {
    //         var sortType = $(this).val();
    //         var reviewsContainer = $("#reviews-container");
    
    //         var reviews = $(".review-card").toArray();
    //         reviews.sort(function (a, b) {
    //             var dateA = new Date($(a).data("date"));
    //             var dateB = new Date($(b).data("date"));
    //             return sortType === "newest" ? dateB - dateA : dateA - dateB;
    //         });
    
    //         // Aggiorna l'ordine nel DOM
    //         reviewsContainer.html(reviews);
    //     });
    // });
    

});



jQuery(document).ready(function ($) {

    const $editModal = $('#editFileModal');
    const $notEditableModal = $('#notEditableModal');
    const $notEditableModalBody = $notEditableModal.find('.modal-body');
    const $confirmEditButton = $('#confirmEditButton');

    // Gestione del click sul pulsante "Modifica"
    $('#editButton').on('click', function (e) {
        e.preventDefault();
        const postId = $(this).data('id');
        const status = $(this).data('status');
        const editUrl = `${env_singolo_documento.edit_page_url}/?post_id=${postId}`;

        if (status === 'in_approvazione') {
            // Mostra il modal informativo per documenti in revisione
            $notEditableModalBody.text('Questo documento sarà modificabile solo dopo l\'approvazione da parte degli amministratori.');
            $notEditableModal.modal('show');
        } else if (status === 'rifiutato') {
            // Mostra il modal informativo per documenti rifiutati
            $notEditableModalBody.text('Questo documento è stato rifiutato e non può essere modificato. Contatta il supporto per ulteriori informazioni.');
            $notEditableModal.modal('show');
        } else {
            // Imposta il link di conferma nel modal
            $confirmEditButton.attr('href', editUrl);

            // Mostra il modal di conferma modifica
            $editModal.modal('show');
        }
    });

    $('#submitReviewButton').click(function () {

        const iconLoadingSendReview = $('#icon-loading-send-review');
        iconLoadingSendReview.prop('hidden', false);


        var rating = $('input[name=rating]:checked').val();

        if (!rating) {
            showCustomAlert('Errore', 'Seleziona un voto.', 'bg-danger btn-danger');
            iconLoadingSendReview.prop('hidden', true);
            return;
        }

        rating = parseInt(rating);

        var review = $('#reviewTextarea').val();

        if (!review) {
            showCustomAlert('Errore', 'Inserisci un commento.', 'bg-danger btn-danger');
            iconLoadingSendReview.prop('hidden', true);
            return;
        }


        var postId = $('#postId').val();
        var data = {
            action: 'submit_review',
            rating: rating,
            comment: review,
            comment_post_ID: postId,
            nonce: env_singolo_documento.nonce_submit_review
        };
        $.post({
            url: env_singolo_documento.ajax_url,
            data: data,
            success: function (response) {
                if (response.success) {

                    var review = response.data.review_html;
                    $('#miaRecensione').hide().append(review).fadeIn('slow');
                    $('#avviso-scrivi-recensione').hide();
                    $('#scrivi-recensione').slideUp();
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });

                    
                } else {
                    console.error(response.data);
                    showCustomAlert('Errore', 'Errore durante l\'invio della recensione.', 'bg-danger btn-danger');
                }
            },
            error: function () {
                showCustomAlert('Errore', 'Errore durante l\'invio della recensione.', 'bg-danger btn-danger');
            }
        });
        $('#icon-loading-send-review').show();
    });




    $('.review-like, .review-dislike').click(function () {
        const isLike = $(this).attr('id').startsWith('review-like');
        const reviewId = $(this).data('id-review');
        const mode = isLike ? 'like' : 'dislike';
        const $button = $(this);
        const $counter = $button.find('span');

        var loading_spinner = $('.icon-loading-like-' + reviewId);
        loading_spinner.prop('hidden', false);

        var vecchio_numero_like = parseInt($('.numero-like-' + reviewId).text());
        var vecchio_numero_dislike = parseInt($('.numero-dislike-' + reviewId).text());

        $('.numero-like-' + reviewId).text("");
        $('.numero-dislike-' + reviewId).text("");


        $.ajax({
            url: env_singolo_documento.ajax_url,
            type: 'POST',
            data: {
                action: 'handle_like_dislike',
                action_type: mode,
                comment_id: reviewId,
                nonce: env_singolo_documento.nonce_like_review
            },
            success: function (response) {
            if (response.success) {
                    $('.numero-like-' + reviewId).text(response.data.likes);
                    $('.numero-dislike-' + reviewId).text(response.data.dislikes);
                if (response.data.current_user === 'like') {
                    $('.review-like-' + reviewId + '').addClass('btn-success').removeClass('btn-muted');
                    $('.review-dislike-' + reviewId + '').removeClass('btn-danger').addClass('btn-muted');
                } else if (response.data.current_user === 'dislike') {
                    $('.review-dislike-' + reviewId + '').addClass('btn-danger').removeClass('btn-muted');
                    $('.review-like-' + reviewId + '').removeClass('btn-success').addClass('btn-muted');
                } else {
                    $('.review-like-' + reviewId + '').removeClass('btn-success').addClass('btn-muted');
                    $('.review-dislike-' + reviewId + '').removeClass('btn-danger').addClass('btn-muted');
                }
            } else {
                $('.numero-like-' + reviewId).text(vecchio_numero_like);
                $('.numero-dislike-' + reviewId).text(vecchio_numero_dislike);
                console.error(response.data);
                showCustomAlert('Errore', 'Errore durante l\'aggiornamento del voto.', 'bg-danger btn-danger');
            }
            },
            error: function () {
                $('.numero-like-' + reviewId).text(vecchio_numero_like);
                $('.numero-dislike-' + reviewId).text(vecchio_numero_dislike);
            showCustomAlert('Errore', 'Errore durante l\'aggiornamento del voto.', 'bg-danger btn-danger');
            },
            complete: function () {
                loading_spinner.prop('hidden', true);
            }
        });
    });



$('.review-report').click(function () {
    const reviewId = $(this).data('id-review');

    const modalHtml = `
        <div class="modal fade" id="reportReviewModal" tabindex="-1" aria-labelledby="reportReviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="reportReviewModalLabel">Segnala Recensione</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reportReasonTextarea" class="form-label">Motivo della segnalazione</label>
                            <textarea id="reportReasonTextarea" class="form-control" placeholder="Indica il motivo della segnalazione" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" class="btn btn-danger" id="confirmReportButton">
                        <div id="icon-loading-invia-segnalazione" class="btn-loader mx-2" hidden style="display: inline-block;">
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                            Segnala
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    jQuery('body').append(modalHtml);
    const $reportModal = jQuery('#reportReviewModal');
    const $confirmReportButton = jQuery('#confirmReportButton');

    $reportModal.modal('show');
    $button = $('.review-report-' + reviewId);

    $confirmReportButton.on('click', function () {
        const reason = jQuery('#reportReasonTextarea').val();

        if (!reason) {
            showCustomAlert('Errore', 'Inserisci un motivo per la segnalazione.', 'bg-danger btn-danger');
            return;
        }

        jQuery('#icon-loading-invia-segnalazione').prop('hidden', false);
        jQuery.ajax({
            url: env_singolo_documento.ajax_url,
            type: 'POST',
            data: {
                action: 'segnala_recensione',
                review_id: reviewId,
                reason: reason,
                nonce: env_singolo_documento.nonce_report_review
            },
            success: function (response) {
                if (response.success) {
                    showCustomAlert('Successo', 'La recensione è stata segnalata con successo. I moderatori analizzeranno questa recensione e, se necessario, prenderanno provvedimenti. Ti ringraziamo per la tua collaborazione!', 'bg-success btn-success');
                    $reportModal.modal('hide');
                    $button.prop('disabled', true);
                    $button.html('Segnalata');
                    $button.data('id-user', null);
                    $button.data('id-review', null);

                } else {
                    console.error(response.data);
                    showCustomAlert('Errore', 'Errore durante la segnalazione della recensione.', 'bg-danger btn-danger');
                }
            },
            error: function () {
                showCustomAlert('Errore', 'Errore durante la segnalazione della recensione.', 'bg-danger btn-danger');
            },
            complete: function () {
                jQuery('#icon-loading-invia-segnalazione').prop('hidden', true);
            }
        });
    });

    $reportModal.on('hidden.bs.modal', function () {
        $reportModal.remove();
    });
});


});