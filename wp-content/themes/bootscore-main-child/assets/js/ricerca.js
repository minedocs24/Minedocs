jQuery(document).ready(function ($) {
    let itemsPerPage = 5;
    let currentPage = 1;
    let totalPages = 0;

    function updateFilters() {
        let filters = [];
        let institute = $('#search-institute').val();
        let subject = $('#search-subject').val();
        let type = $('#search-type').val();
        let institute_type = $('#search-education-type').val();
        let study_course = $('#search-course').val();
        let academic_year = $('#search-academic-year').val();
        let hide_purchased = $('#hide-purchased-documents').is(':checked');

        $('#active-filters').empty();

        if (institute) {
            filters.push({ label: $('#search-institute option:selected').text(), value: institute, key: 'institute' });
        }
        if (subject) {
            filters.push({ label: $('#search-subject option:selected').text(), value: subject, key: 'subject' });
        }
        if (type) {
            filters.push({ label: $('#search-type option:selected').text(), value: type, key: 'type' });
        }
        if (institute_type) {
            filters.push({ label: $('#search-education-type option:selected').text(), value: institute_type, key: 'institute_type' });
        }
        if (study_course) {
            filters.push({ label: $('#search-course option:selected').text(), value: study_course, key: 'study_course' });
        }
        if (academic_year) {
            filters.push({ label: $('#search-academic-year option:selected').text(), value: academic_year, key: 'academic_year' });
        }
        if (hide_purchased) {
            filters.push({ label: 'Nascondi documenti già acquistati', value: hide_purchased, key: 'hide_purchased' });
        }

        filters.forEach(filter => {
            $('#active-filters').append(
                `<span class="filter-badge" data-key="${filter.key}" data-value="${filter.value}">
                    ${filter.label} <span class="remove-filter">&times;</span>
                </span>`
            );
        });

        $('#filtersModal').modal('hide');

        performSearch();
    }

    $('#apply-filters').on('click', function () {
        updateFilters();
    });

    /*$(document).on('click', '.remove-filter', function () {
        let filterKey = $(this).parent().data('key');
        $(`#search-${filterKey}`).val('');
        $(this).parent().remove();
        performSearch();
    });*/


    $(document).on('click', '.remove-filter', function () {
        let filterKey = $(this).parent().data('key');
        if (filterKey === 'hide_purchased') {
            $('#hide-purchased-documents').prop('checked', false);
        } else if (filterKey === 'academic_year') {
            $('#search-academic-year').val(''); // Resetta il valore del filtro sull'anno accademico
        } else {
            $(`#search-${filterKey}`).val('');
        }
        $(this).parent().remove();
        performSearch(); // Aggiorna i risultati della ricerca
    });

    function renderPage(response, page) {
        let resultsHtml = '';
        let start = (page - 1) * itemsPerPage;
        let end = start + itemsPerPage;
        let paginatedItems = response.data.slice(start, end);

        paginatedItems.forEach(item => {
            resultsHtml += item.html;
        });

        $('#search-results').html(resultsHtml);
        
        $('#page-info').text(`Pagina ${page} di ${totalPages}`);
    }

    function handlePagination(response) {
        // Rimuove tutti i listener precedenti
        $(document).off('click', '#prev-page');
        $(document).off('click', '#next-page');
        $(document).off('click', '.page-link');

        $(document).on('click', '#prev-page', function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderPage(response, currentPage);
            }
        });

        $(document).on('click', '#next-page', function (e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                renderPage(response, currentPage);
            }
        });

        $(document).on('click', '.page-link', function (e) {
            e.preventDefault();
            currentPage = parseInt($(this).text());
            renderPage(response, currentPage);
        });
    }

    function performSearch() {
        let searchQuery = $('.search-bar-input').val();
        let institute_type = $('#search-education-type').val();
        let institute = $('#search-institute').val();
        let study_course = $('#search-course').val();
        let subject = $('#search-subject').val();
        let type = $('#search-type').val();
        let orderby = $('#orderby').val();
        let academic_year = $('#search-academic-year').val();
        let hide_purchased = $('#hide-purchased-documents').is(':checked');

        /*if (searchQuery.length < 3) {
            $('#search-results').html('<div class="alert alert-warning">Per favore, inserisci almeno 3 caratteri per la ricerca.</div>');
            return;
        }*/

        if (!type) {
            type = 'documento';
        }

        $.ajax({
            url: env_ricerca.ajax_url,
            type: 'POST',
            data: {
                action: 'search_documents',
                search: searchQuery,
                institute: institute,
                subject: subject,
                type: type,
                institute_type: institute_type,
                study_course: study_course,
                orderby: orderby,
                academic_year: academic_year,
                hide_purchased: hide_purchased,
                nonce: env_ricerca.nonce
            },
            beforeSend: function () {
                $('#pagination').prop('hidden', true);
                $('#num-results').text('Sto cercando i documenti...');
                $('#search-results').html('<div class="loading-animation"><img src="' + env_ricerca.logo + '" alt="Caricamento..." class="loading-logo"  /></div>');
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
            },
            success: function (response) {
                if (response.success === false) {
                    $('#search-results').html('<div class="alert alert-danger">' + response.data + '</div>');
                    $('#num-results').text("Nessun risultato trovato");
                    return;
                } else {
                    //console.log(response);

                    totalPages = Math.ceil(response.data.length / itemsPerPage);
                    currentPage = 1;
                    if (response.data.length > 0) {
                        if (response.data.length >= 25) {
                            const phrases = [
                                "Ecco i primi 25! Vuoi vedere di più?",
                                "25 risultati al volo. Ce n’è altri, se ti va di cercare meglio!",
                                "Solo l’inizio: questi sono i primi 25.",
                                "Ne abbiamo trovati tanti! Qui solo i primi 25.",
                                "Mostriamo i primi 25. Vuoi scavare più a fondo?",
                                "Un assaggio: 25 risultati. Vuoi affinare la ricerca?",
                                "Solo i primi 25. Il resto ti aspetta!",
                                "25 per cominciare. Ma c'è altro, se vuoi!"
                            ];
                            const randomPhrase = phrases[Math.floor(Math.random() * phrases.length)];
                            $('#num-results').text(randomPhrase);
                        } else {
                            $('#num-results').text("Sono stati trovati " + response.data.length + " risultati");
                        }
                    } else {
                        $('#num-results').text("Nessun risultato trovato");
                    }

                    if (totalPages > 1) {
                        $('#pagination').prop('hidden', false);
                    } 

                    renderPage(response, currentPage);
                    handlePagination(response);
                }
            },
            error: function (error) {
                //console.log(error);
                $('#search-results').html('<div class="alert alert-danger">' + error.data + '</div>');
            }
        });
    }

    // Rimuovo la ricerca automatica durante la digitazione
    // $('.search-bar-input').on('input', function () {
    //     performSearch();
    // });

    // Aggiungo la ricerca con il pulsante
    $('#search-button').on('click', function () {
        performSearch();
    });

    // Aggiungo la ricerca con il tasto INVIO
    $('.search-bar-input').on('keypress', function (e) {
        if (e.which === 13) { // Codice del tasto INVIO
            e.preventDefault();
            performSearch();
        }
    });

    $('#orderby').on('change', function () {
        performSearch();
    });

    // Render initial pagination controls
    $('#pagination').html(`
        <div class="pagination-controls">
            <img id="prev-page" class="table-button-prev" src="${env_ricerca.left_arrow}" alt="Precedente" width="16" height="16" />
            <span id="page-info">Pagina 1 di 1</span>
            <img id="next-page" class="table-button-next" src="${env_ricerca.left_arrow}" alt="Successiva" width="16" height="16" />
        </div>
    `);


    if ($('.search-bar-input').val().length == 0) {
        $('.search-bar-input').val('*');
        performSearch();
    } else {
        performSearch();
    }
});
