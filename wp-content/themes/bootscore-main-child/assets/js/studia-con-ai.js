jQuery(document).ready(function($) {
    // Previene inizializzazione multipla
    if (window.studiaConAiInitialized) {
        return;
    }
    window.studiaConAiInitialized = true;
    
    // Verifica che le variabili necessarie siano disponibili
    if (typeof env_studia_con_ai === 'undefined') {
        console.error('env_studia_con_ai non è definito!');
        return;
    }
    
    // Gestione del caricamento del documento
    const uploadSection = $('.studia-ai-upload-section');
    const fileInput = $('#studiaAiFileInput');
    const progressSection = $('#studiaAiProgressSection');
    const fileName = $('#studiaAiFileName');
    const progressBar = $('#studiaAiProgressBar .progress-bar');
    const analysisSection = $('.studia-ai-document-analysis');
    const configSection = $('.studia-ai-summary-config');
    const generateSection = $('.studia-ai-generate-summary');
    const quizConfigRow = $('.studia-ai-quiz-config-row');
    const quizPlayerRow = $('#quizPlayerRow');
    const newGenerationButton = $('#studia-ai-new-generation');
    const documentDetailsRow = $('#document-details-row');
    let currentQuiz = null; // { questions: [...], difficulty: string, idx: 0, score: 0, total: number, answered: boolean, correctAnswer: number }
    let lastBackendPoints = null; // punti calcolati dal backend per il documento (memorizzati)

    function computeQuizSupplement(numQuestions) {
        // Prime 5 incluse => 0 supplemento. Ogni 5 domande successive => +1 punto
        const n = parseInt(numQuestions, 10);
        if (!n || n <= 5) return 0;
        return Math.floor((n - 1) / 5);
    }

    // Centralizza l'aggiornamento del prezzo: se abbiamo il prezzo backend memorizzato
    // aggiorna client-side, altrimenti invoca la fetch
    function updatePriceForNumQuestions(numQuestions) {
        const supplement = computeQuizSupplement(numQuestions);
        if (lastBackendPoints !== null) {
            updateGenerateButtonLabel(lastBackendPoints + supplement);
        } else {
            fetchDynamicPriceAndUpdateButton();
        }
    }

    // Binding delegato per aggiornamento prezzo al variare del numero di domande
    // Uso delegated events sul document così funziona anche se gli input sono inseriti dinamicamente
    (function() {
        const selector = '#quiz-num-questions, #quiz-num-questions-php';
        // log per debug binding (silenzioso se jQuery non definito)
        try { console.debug('Binding quiz num questions listener'); } catch (e) {}
        // usa delegated events
        $(document).off('input change', selector).on('input change', selector, function() {
            const val = parseInt($(this).val(), 10);
            const numQuestions = (!isNaN(val) && val > 0) ? val : null;
            updatePriceForNumQuestions(numQuestions);
        });
    })();

    function hideQuizPlayer() {
        if (quizPlayerRow && quizPlayerRow.length) {
            quizPlayerRow.hide();
            // reset semplice degli elementi interni
            $('#quizStart').hide();
            $('#quizPlay').hide();
            $('#quizResult').hide();
            $('#quizQuestionText').text('');
            $('#quizOptions').empty();
            $('#quizProgressBar').css('width', '0%');
            $('#quizProgressText').text('Domanda 0 di 0');
            $('#quizDifficultyBadge').text('-').removeClass('bg-success bg-warning bg-danger');
        }
        currentQuiz = null;
    }

    function difficultyToBadge(difficulty) {
        const d = (difficulty || '').toString().toLowerCase();
        if (d === 'easy' || d === 'facile') return { text: 'Facile', cls: 'bg-success' };
        if (d === 'hard' || d === 'difficile') return { text: 'Difficile', cls: 'bg-danger' };
        return { text: 'Media', cls: 'bg-warning' };
    }

    function updateQuizProgress() {
        if (!currentQuiz) return;
        const current = currentQuiz.idx + 1;
        const total = currentQuiz.total;
        const percent = Math.max(0, Math.min(100, Math.round((current / total) * 100)));
        $('#quizProgressBar').css('width', percent + '%');
        $('#quizProgressText').text('Domanda ' + current + ' di ' + total);
    }

    function renderQuizQuestion() {
        if (!currentQuiz) return;
        const q = currentQuiz.questions[currentQuiz.idx];
        currentQuiz.answered = false;
        currentQuiz.correctAnswer = parseInt(q.correct_answer, 10);
        $('#quizQuestionText').text(q.question || '');
        $('#quizOptions').empty();
        $('#quizNextBtn').prop('disabled', true);

        // Costruisci le 4 opzioni in griglia
        Object.keys(q.options || {}).sort().forEach(function(key) {
            const text = q.options[key];
            const col = $('<div class="col-12 col-md-6"></div>');
            const btn = $('<button type="button" class="btn btn-outline-secondary w-100 text-start p-3"></button>');
            btn.attr('data-option-key', key);
            btn.append('<span class="fw-bold me-2">' + key + ')</span>' + text);

            btn.on('click', function() {
                if (!currentQuiz || currentQuiz.answered) return;
                const chosen = parseInt($(this).attr('data-option-key'), 10);
                const isCorrect = chosen === currentQuiz.correctAnswer;
                currentQuiz.answered = true;
                if (isCorrect) {
                    currentQuiz.score += 1;
                }

                // Disabilita tutte le opzioni e applica gli stili
                $('#quizOptions button').prop('disabled', true);

                // Evidenzia risposta selezionata
                if (isCorrect) {
                    $(this).removeClass('btn-outline-secondary').addClass('btn-success');
                    // mini animazione di "pulse"
                    $(this).css({ transform: 'scale(1.02)' });
                    setTimeout(() => { $(this).css({ transform: '' }); }, 200);
                } else {
                    $(this).removeClass('btn-outline-secondary').addClass('btn-danger');
                    // evidenzia anche la corretta
                    $('#quizOptions button[data-option-key="' + currentQuiz.correctAnswer + '"]').removeClass('btn-outline-secondary').addClass('btn-success');
                    // mini animazione shake via translate
                    $(this).css({ transform: 'translateX(-4px)' });
                    setTimeout(() => { $(this).css({ transform: 'translateX(4px)' }); }, 60);
                    setTimeout(() => { $(this).css({ transform: '' }); }, 120);
                }

                // Abilita "Prossima domanda"
                $('#quizNextBtn').prop('disabled', false);
            });

            col.append(btn);
            $('#quizOptions').append(col);
        });

        updateQuizProgress();
    }

    function showQuizStart() {
        $('#quizStart').show();
        $('#quizPlay').hide();
        $('#quizResult').hide();
        $('#quizNextBtn').prop('disabled', true);
    }

    function showQuizPlay() {
        $('#quizStart').hide();
        $('#quizPlay').show();
        $('#quizResult').hide();
    }

    function showQuizResult() {
        $('#quizStart').hide();
        $('#quizPlay').hide();
        $('#quizResult').show();
        if (currentQuiz) {
            $('#quizScore').text(currentQuiz.score + '/' + currentQuiz.total);
        }
    }

// Se la pagina è stata aperta con ?open_quiz_job=ID&action=quiz, avvia il quiz automaticamente
(function() {
    try {
        const params = new URLSearchParams(window.location.search);
        const jobId = params.get('open_quiz_job');
        const actionParam = params.get('action');
        if (jobId && actionParam === 'quiz') {
            // aspetta che la funzione sia definita
            setTimeout(() => {
                if (typeof downloadQuiz === 'function') {
                    downloadQuiz(jobId);
                }
            }, 300);
        }
    } catch (e) {
        console.error('Auto-open quiz failed', e);
    }
})();

    function initQuizPlayer(quizArray, difficulty) {
        if (!Array.isArray(quizArray) || quizArray.length === 0) {
            showCustomAlert('Errore', 'Nessuna domanda disponibile per il quiz.', 'bg-danger btn-danger');
            return;
        }
        const diff = difficultyToBadge(difficulty);
        $('#quizDifficultyBadge').text(diff.text).removeClass('bg-success bg-warning bg-danger').addClass(diff.cls);

        currentQuiz = {
            questions: quizArray,
            difficulty: diff.text,
            idx: 0,
            score: 0,
            total: quizArray.length,
            answered: false,
            correctAnswer: null
        };

        $('#quizProgressText').text('Domanda 0 di ' + currentQuiz.total);

        quizPlayerRow.show();
        showQuizStart();

        // Bind pulsanti (unico listener)
        $('#quizStartBtn').off('click').on('click', function() {
            showQuizPlay();
            renderQuizQuestion();
        });

        $('#quizNextBtn').off('click').on('click', function() {
            if (!currentQuiz) return;
            if (!currentQuiz.answered) return; // safety
            if (currentQuiz.idx < currentQuiz.total - 1) {
                currentQuiz.idx += 1;
                renderQuizQuestion();
            } else {
                showQuizResult();
            }
        });

        $('#quizRetryBtn').off('click').on('click', function() {
            if (!currentQuiz) return;
            currentQuiz.idx = 0;
            currentQuiz.score = 0;
            showQuizStart();
            updateQuizProgress();
        });
    }

    // Flag per prevenire chiamate multiple
    let isUploading = false;
    let isDocumentDetailsLoading = false;
    
    // Variabili per la paginazione
    let currentPage = 1;
    let itemsPerPage = 10;
    let allJobs = {};
    let totalJobs = 0;
    
    // Lista delle azioni disponibili
    const availableActions = ['riassunto', 'summary', 'mappa', 'mindmap', 'quiz'];
    
    // Gestione per documenti già presenti in piattaforma
    if (typeof env_studia_con_ai !== 'undefined' && env_studia_con_ai.has_document) {
        // Verifica se l'azione corrente è valida
        if (!availableActions.includes(env_studia_con_ai.action)) {
            // Reindirizza a riassunto mantenendo il documento
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('action', 'riassunto');
            window.location.href = newUrl.toString();
            return;
        }
        
        // Se c'è un documento selezionato, mostra le sezioni appropriate in base all'azione
        if (env_studia_con_ai.action === 'quiz') {
            // mostriamo solo la sezione quiz
            configSection.hide();
            quizConfigRow.show();
            // Reset e nascondi il player finché non pronto
            hideQuizPlayer();
        } else {
            // comportamento precedente per riassunti/altre azioni
            quizConfigRow.hide();
            if (!env_studia_con_ai.hide_params) {
                configSection.show();
            } else {
                configSection.hide();
            }
            hideQuizPlayer();
        }
        generateSection.show();
        
        uploadSection.hide();

        // Aggiorna il testo del pulsante di generazione in base all'azione
        updateActionLabels();
        
        // Carica i dettagli del documento
        loadDocumentDetails();
    } else {
        uploadSection.show();
        documentDetailsRow.hide();
    }
    
    // Gestione del cambio di azione AI
    $('.studia-ai-feature-item').off('click').on('click', function() {
        const action = $(this).data('action');
        const isComingSoon = $(this).hasClass('coming-soon');
        
        // Controlla se l'azione è disponibile
        if (!availableActions.includes(action)) {
            showCustomAlert("Funzionalità in sviluppo", "Questa funzionalità sarà disponibile presto!", 'bg-info btn-info');
            return;
        }
        
        if (isComingSoon) {
            showCustomAlert("Funzionalità in sviluppo", "Questa funzionalità sarà disponibile presto!", 'bg-info btn-info');
            return;
        }
        
        // Aggiorna la classe active
        $('.studia-ai-feature-item').removeClass('active');
        $(this).addClass('active');
        
        // Aggiorna l'azione corrente
        if (typeof env_studia_con_ai !== 'undefined') {
            env_studia_con_ai.action = action;
        }
        
        // Aggiorna i titoli e le etichette
        updateActionLabels();

        // Se compare il quiz player, nascondilo e ripristina la sezione di upload
        hideQuizPlayer();
        // mostra la sezione upload per iniziare nuova generazione
        // if (!(env_studia_con_ai && env_studia_con_ai.has_document && env_studia_con_ai.document_id)) {
        resetStudiaAiUpload();
        // }
        
        // Se c'è un documento selezionato, aggiorna l'URL senza ricaricare la pagina
        if (env_studia_con_ai && env_studia_con_ai.has_document && env_studia_con_ai.document_id) {
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('action', action);
            window.history.pushState({}, '', newUrl);
        }
    });

    // Funzione per aggiornare le etichette in base all'azione
    function updateActionLabels() {
        const actionLabels = {
            'riassunto': 'Genera Riassunto',
            'mappa': 'Genera Mappa Concettuale',
            'quiz': 'Genera Quiz',
            'evidenza': 'Evidenzia Documento',
            'interroga': 'Interroga Documento'
        };
        
        const currentAction = env_studia_con_ai ? env_studia_con_ai.action : 'riassunto';
        const actionLabel = actionLabels[currentAction] || 'Genera Riassunto';
        
        // Mostra stato in attesa prezzo e disabilita finché non disponibile
        $('#studia-ai-generate-summary')
            .prop('disabled', true)
            .html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Calcolo costo...`);
        // Aggiorna costo dinamico se possibile
        fetchDynamicPriceAndUpdateButton();
        
        // Aggiorna i titoli delle sezioni
        const sectionLabels = {
            'riassunto': 'il riassunto',
            'mappa': 'la mappa concettuale',
            'quiz': 'il quiz',
            'evidenza': 'l\'evidenziazione',
            'interroga': 'l\'interrogazione'
        };
        
        const sectionLabel = sectionLabels[currentAction] || 'il riassunto';

        // Mostra/nascondi sezioni di configurazione in base all'azione
        if (currentAction === 'quiz') {
            // mostra la configurazione quiz e nasconde la configurazione summary
            configSection.hide();
            quizConfigRow.show();
            hideQuizPlayer();
            $('.studia-ai-summary-config .card-title').html(`<i class="fas fa-cog text-primary me-2"></i> Configura ${sectionLabel} <span class="badge bg-success ms-2">Configurazione attiva</span>`);
        } else {
            // per ora i parametri dei riassunti restano nascosti a meno che hide_params sia false
            quizConfigRow.hide();
            if (!env_studia_con_ai.hide_params) {
                configSection.show();
                $('.studia-ai-summary-config .card-title').html(`<i class="fas fa-cog text-primary me-2"></i> Configura ${sectionLabel} <span class="badge bg-success ms-2">Configurazione attiva</span>`);
            } else {
                configSection.hide();
            }
            hideQuizPlayer();
        }

        $('.studia-ai-generate-summary .card-title').text(`Genera ${sectionLabel}`);
        $('#studia-ai-generate-summary-label').text(sectionLabel);
    }

    function updateGenerateButtonLabel(points) {
        const labels = {
            'riassunto': 'Genera Riassunto',
            'mappa': 'Genera Mappa Concettuale',
            'quiz': 'Genera Quiz',
            'evidenza': 'Evidenzia Documento',
            'interroga': 'Interroga Documento'
        };
        const action = env_studia_con_ai ? env_studia_con_ai.action : 'riassunto';
        const label = labels[action] || 'Genera Riassunto';
        if (typeof points === 'number' && points > 0) {
            const labelPunti = points === 1 ? 'punto' : 'punti';
            const suffix = ` (${points} ${labelPunti} pro)`;
            $('#studia-ai-generate-summary')
                .prop('disabled', false)
                .html(`<i class="fas fa-magic"></i> ${label}${suffix}`);
        } else {
            $('#studia-ai-generate-summary')
                .prop('disabled', true)
                .html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Calcolo costo...`);
        }
    }

    function fetchDynamicPriceAndUpdateButton() {
        const hasUpload = window.studiaAiDocumentData && window.studiaAiDocumentData.file_id;
        const hasDoc = env_studia_con_ai && env_studia_con_ai.has_document && env_studia_con_ai.document_id;
        if (!hasUpload && !hasDoc) {
            return;
        }

        const data = {
            action: 'get_dynamic_price',
            nonce: env_studia_con_ai.nonce_get_dynamic_price
        };
        if (hasUpload) {
            data.file_id = window.studiaAiDocumentData.file_id;
        } else if (hasDoc) {
            data.document_id = env_studia_con_ai.document_id;
        }

        $.ajax({
            url: env_studia_con_ai.ajax_url,
            type: 'POST',
            data,
            success: function(response) {
                if (response && response.success && response.data && typeof response.data.points !== 'undefined') {
                    // Punti calcolati dal backend
                    let backendPoints = parseInt(response.data.points, 10) || 0;
                    // memorizza per ricalcoli client-side
                    lastBackendPoints = backendPoints;

                    // Determina il numero di domande se presente (dal DOM o dal backend)
                    let numQuestions = null;
                    if (env_studia_con_ai && env_studia_con_ai.action === 'quiz') {
                        try {
                            const numEl = $('#quiz-num-questions, #quiz-num-questions-php');
                            if (numEl.length) {
                                const parsed = parseInt(numEl.val(), 10);
                                if (!isNaN(parsed)) numQuestions = parsed;
                            }
                            // fallback: se il backend ha fornito questions_count
                            if ((numQuestions === null || typeof numQuestions === 'undefined') && typeof response.data.questions_count !== 'undefined') {
                                const parsed2 = parseInt(response.data.questions_count, 10);
                                if (!isNaN(parsed2)) numQuestions = parsed2;
                            }
                            // Usa la funzione centralizzata per aggiornare il prezzo (somma backend + supplemento quiz se action=quiz)
                            updatePriceForNumQuestions(numQuestions);
                        } catch (e) {
                            console.error('Errore determinazione numQuestions', e);
                            updateGenerateButtonLabel(backendPoints);
                        }

                    } else {//Non è quiz
                        updateGenerateButtonLabel(backendPoints);
                    }

                    
                } else {
                    showCustomAlert('Calcolo prezzo non disponibile', 'Qualcosa è andato storto nel calcolo del prezzo. Riprova più tardi.', 'bg-warning btn-warning');
                    lastBackendPoints = null;
                    updateGenerateButtonLabel();
                }
            },
            error: function() {
                showCustomAlert('Calcolo prezzo non disponibile', 'Qualcosa è andato storto nel calcolo del prezzo. Riprova più tardi.', 'bg-warning btn-warning');
                updateGenerateButtonLabel();
            }
        });
    }

    // Aggiorna il numero di punti visualizzati nel sito chiamando l'endpoint centrale
    function updatePointsDisplay() {
        $.ajax({
            url: env_studia_con_ai.ajax_url,
            type: 'POST',
            data: { action: 'get_updated_points' },
            success: function(res) {
                if (res && res.success && res.data) {
                    Object.keys(res.data).forEach(function(key) {
                        var selector = '.show_count_' + key;
                        $(selector).each(function() {
                            $(this).text(res.data[key]);
                        });
                    });
                }
            }
        });
    }

    // Funzione per caricare i dettagli del documento
    function loadDocumentDetails() {
        // Previene chiamate multiple
        if (isDocumentDetailsLoading) {
            return;
        }
        
        isDocumentDetailsLoading = true;
        
        // Verifica che jQuery sia disponibile
        if (typeof $ === 'undefined') {
            console.error('jQuery non è disponibile');
            isDocumentDetailsLoading = false;
            return;
        }
        
        // Verifica che le variabili necessarie siano presenti
        if (!env_studia_con_ai || !env_studia_con_ai.ajax_url || !env_studia_con_ai.nonce) {
            console.error('Variabili JavaScript mancanti');
            isDocumentDetailsLoading = false;
            return;
        }
        
        if (!env_studia_con_ai.document_id) {
            isDocumentDetailsLoading = false;
            return;
        }
        
        $.ajax({
            url: env_studia_con_ai.ajax_url,
            type: 'POST',
            data: {
                action: 'get_document_details',
                nonce: env_studia_con_ai.nonce_document_details,
                document_id: env_studia_con_ai.document_id
            },
            beforeSend: function() {
                // Richiesta AJAX in corso
            },
            success: function(response) {
                if (response.success) {
                    displayDocumentDetails(response.data);
                    // Mostra le sezioni rispettando il flag di visibilità dei parametri
                    if (!env_studia_con_ai.hide_params) {
                        $('.studia-ai-summary-config').show();
                    }
                    $('.studia-ai-generate-summary').show();
                } else {
                    console.error('Errore nella risposta:', response.data);
                    $('#document-details').html(
                        '<div class="alert alert-danger">Errore nel caricamento del documento: ' + response.data + '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore AJAX:', error);
                $('#document-details').html(
                    '<div class="alert alert-danger">Errore nel caricamento del documento: ' + error + '</div>'
                );
            },
            complete: function() {
                isDocumentDetailsLoading = false;
            }
        });
    }

    // Funzione per visualizzare i dettagli del documento
    function displayDocumentDetails(document) {
        const detailsHtml = `
            <div class="row">
                <div class="col-md-8">
                    <h6 class="text-primary">${document.title}</h6>
                    <p class="text-muted mb-2">${document.content || 'Nessuna descrizione'}</p>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-file-pdf me-1"></i>
                                ${document.pages} pagine
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                ${document.upload_date}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-primary">${env_studia_con_ai.action}</span>
                </div>
            </div>
        `;
        
        $('#document-details').html(detailsHtml);
        // Aggiorna costo dinamico se documento piattaforma
        fetchDynamicPriceAndUpdateButton();
    }

    // Gestione del drag & drop
    uploadSection.off('dragover dragleave drop').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    }).on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        if (isUploading) return; // Previene chiamate multiple
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleStudiaAiFileInternal(files[0]);
        }
    });

    // Gestione del click per caricare
    uploadSection.off('click').on('click', function(e) {
        // Non attivare se si clicca sul pulsante
        if (!$(e.target).closest('button').length) {
        fileInput.click();
        }
    });

    fileInput.off('change').on('change', function(e) {
        if (isUploading) return; // Previene chiamate multiple
        
        const file = e.target.files[0];
        if (file) {
            handleStudiaAiFileInternal(file);
        }
    });

    function handleStudiaAiFileInternal(file) {
        if (!file) return;

        // Previene chiamate multiple
        if (isUploading) {
            console.log('Upload già in corso, ignoro la chiamata');
            return;
        }
        
        isUploading = true;

        // Verifica il tipo di file - consenti solo PDF
        const allowedTypes = ['application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            showCustomAlert("Impossibile caricare il file", 'Tipo di file non supportato. Utilizza esclusivamente PDF.', 'bg-danger btn-danger');
            isUploading = false;
            return;
        }

        // Verifica la dimensione del file (10MB)
        if (file.size > 10 * 1024 * 1024) {
            showCustomAlert("Impossibile caricare il file", 'Il file è troppo grande. Dimensione massima: 10MB.', 'bg-danger btn-danger');
            isUploading = false;
            return;
        }

        // Mostra la sezione di progresso
        uploadSection.hide();
        progressSection.show();
        
        // Aggiorna il nome del file
        fileName.find('.filename-text').text(file.name);
        fileName.removeClass('d-none');
        
        // Aggiungi le classi per l'animazione
        progressBar.addClass('progress-bar-striped', 'progress-bar-animated');

        // Prepara i dati per l'upload
        const formData = new FormData();
        formData.append('document', file);
        formData.append('action', 'analyze_document');
        formData.append('nonce', env_studia_con_ai.nonce_analyze_document || '');

        // Inizializza il progresso
        let progress = 0;
        let progressInterval = null;
        
        // Funzione per aggiornare il progresso
        function updateProgress(newProgress) {
            progress = newProgress;
            progressBar.css('width', progress + '%');
            progressBar.html('<strong>' + progress.toFixed(2) + '%</strong>');
        }
        
        // Simula il progresso iniziale
        progressInterval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress >= 30) {
                progress = 30; // Ferma al 30% per l'upload reale
                clearInterval(progressInterval);
            }
            updateProgress(progress);
        }, 300);

        // Chiamata AJAX con jQuery
        $.ajax({
            url: env_studia_con_ai.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5 minuti di timeout
            xhr: function() {
                // Personalizza l'oggetto XMLHttpRequest per il progresso
                const xhr = new window.XMLHttpRequest();
                
                // Gestione progresso upload
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const uploadPercent = (e.loaded / e.total) * 100;
                        // Combina il progresso simulato (30%) con quello reale (70%)
                        const totalProgress = 30 + (uploadPercent * 0.7);
                        updateProgress(totalProgress);
                        
                        // Quando l'upload è completato, mostra il messaggio di analisi
                        if (uploadPercent >= 100) {
                            clearInterval(progressInterval);
                            updateProgress(100);
                            fileName.html('<i class="fas fa-cog fa-spin me-2"></i> <span class="filename-text">Analisi del documento in corso...</span>');
                            showAnalysisInProgressMessage();
                        }
                    }
                });
                
                return xhr;
            },
            beforeSend: function() {
                // Azioni prima dell'invio
                console.log('Inizio upload del file:', file.name);
            },
            success: function(response) {
                clearInterval(progressInterval);
                progressBar.removeClass('progress-bar-striped', 'progress-bar-animated');
                
                if (response.success) {
                    showStudiaAiAnalysisSection(response.data);
                } else {
                    // Gestione caso: nessun testo estraibile dal file PDF
                    if (response.data && response.data.code === 'no_text_extracted') {
                        showCustomAlert("Attenzione", response.data.message, 'bg-warning btn-warning');
                        // Nascondi la sezione di analisi documento e messaggi di analisi in corso
                        analysisSection.hide();
                        $('.alert-info').remove();
                        // Nascondi la sezione di configurazione e generazione
                        configSection.hide();
                        generateSection.hide();
                        // Blocca anche eventuali dati globali
                        window.studiaAiDocumentData = null;
                        // Mostra nuovamente la sezione di upload per permettere un nuovo caricamento
                        uploadSection.show();
                        progressSection.hide();
                        fileName.addClass('d-none');
                        progressBar.css('width', '0%').html('');
                        isUploading = false;
                        return;
                    }
                    showCustomAlert("Errore nell'analisi", response.data.message || 'Errore durante l\'analisi del documento', 'bg-danger btn-danger');
                    resetStudiaAiUpload();
                }
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                progressBar.removeClass('progress-bar-striped', 'progress-bar-animated');
                
                const errorMessage = handleAjaxError(xhr, status, error);
                showCustomAlert("Errore", errorMessage, 'bg-danger btn-danger');
                
                resetStudiaAiUpload();
            },
            complete: function() {
                // Azioni al completamento (successo o errore)
                console.log('Upload completato');
                isUploading = false; // Reset del flag
            }
        });
    }



    function showAnalysisInProgressMessage() {
        // Crea un messaggio informativo per l'utente
        const messageHtml = `
            <div class="alert alert-info mt-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <strong>Analisi del documento in corso...</strong><br>
                        <small>Il sistema sta analizzando il tuo documento. A breve potrai procedere con la generazione ${env_studia_con_ai.action && env_studia_con_ai.action.toLowerCase() === 'mappa' ? 'della' : 'del'} ${env_studia_con_ai.action}</small>
                    </div>
                </div>
            </div>
        `;
        
        if (env_studia_con_ai.action === 'quiz') {
            quizConfigRow.show();
        } else {
            quizConfigRow.hide();
        }
        // Inserisci il messaggio prima della sezione di configurazione
        $('.studia-ai-summary-config').before(messageHtml);
        
        // Mostra la sezione di configurazione solo se non nascosta
        if (!env_studia_con_ai.hide_params) {
            $('.studia-ai-summary-config').show();
        } else {
            $('.studia-ai-summary-config').hide();
        }
    }

    function showStudiaAiAnalysisSection(data) {
        // Nascondi la sezione di progresso
        progressSection.hide();
        
        // Rimuovi il messaggio di analisi in corso
        $('.alert-info').remove();
        
        // Mostra la sezione di analisi
        analysisSection.show();
        
        // Popola i dettagli del documento
        $('.studia-ai-document-title').text(data.title);
        $('.studia-ai-document-pages').text(data.pages + ' pagine');
        $('.studia-ai-document-words').text(data.words + ' parole');
        
        // Mostra la sezione di configurazione solo se non nascosta
        if (!env_studia_con_ai.hide_params) {
        configSection.show();
        } else {
            configSection.hide();
        }

        // Mostra la sezione di generazione
        generateSection.show();
        
        // Salva i dati del documento per la generazione del riassunto
        window.studiaAiDocumentData = data;

        // Aggiorna costo dinamico
        fetchDynamicPriceAndUpdateButton();
    }

    // Bind per il pulsante Nuova generazione
    $('#studia-ai-new-generation').off('click').on('click', function() {
        $(this).hide();
        resetStudiaAiUpload();
    });

    // Rimuoviamo la gestione del submit del form
    // La sezione di configurazione rimane sempre visibile
    // e le opzioni vengono utilizzate automaticamente durante la generazione

    // Gestione della generazione del riassunto (NUOVO SISTEMA ASINCRONO)
    // Rimuove eventuali event listener esistenti per evitare chiamate duplicate
    $('#studia-ai-generate-summary').off('click').on('click', function() {
        const button = $(this);
        
        // Previene chiamate multiple se il pulsante è già disabilitato
        if (button.prop('disabled')) {
            return;
        }
        
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i> Avvio generazione...');

        // Prepara i dati per la generazione
        const formData = new FormData();

        // Determina l'azione AJAX e il nonce in base all'azione selezionata (es: "mappa"/"mindmap")
        const selectedAction = env_studia_con_ai && env_studia_con_ai.action ? env_studia_con_ai.action : 'riassunto';
        let ajaxAction = 'generate_summary';
        // fallback nonce per la generazione di summary
        let ajaxNonce = env_studia_con_ai.nonce_generate_summary;

        if (selectedAction === 'mappa' || selectedAction === 'mindmap') {
            ajaxAction = 'generate_map';
            // supporta diversi nomi di nonce eventualmente presenti
            ajaxNonce = env_studia_con_ai.nonce_generate_mappe || env_studia_con_ai.nonce_generate_mindmap || env_studia_con_ai.nonce_generate_summary;
        } else if (selectedAction === 'quiz') {
            // Quando l'azione selezionata è 'quiz' bisogna chiamare l'endpoint dedicato
            ajaxAction = 'generate_quiz';
            // usa il nonce specifico per i quiz se presente, altrimenti fallback a quello generico
            ajaxNonce = env_studia_con_ai.nonce_generate_quiz || env_studia_con_ai.nonce_generate_summary;
        }

        formData.append('action', ajaxAction);
        // Se i parametri sono nascosti, non inviare il form di configurazione
        if (!env_studia_con_ai.hide_params) {
            formData.append('config', $('#studia-ai-summary-form').serialize());
        } else {
            formData.append('config', '');
        }
        formData.append('nonce', ajaxNonce);
        
        // Aggiungi i dati del documento se disponibili
        if (window.studiaAiDocumentData) {
            formData.append('file_id', window.studiaAiDocumentData.file_id);
        }
        
        // Se c'è un documento selezionato dalla piattaforma, aggiungi i suoi dati
        if (env_studia_con_ai.has_document && env_studia_con_ai.document_id) {
            formData.append('document_id', env_studia_con_ai.document_id);
            formData.append('request_type', env_studia_con_ai.action);
        }

        // Se l'azione è quiz, allega i parametri specifici (numero domande e difficoltà)
        if (selectedAction === 'quiz') {
            // supporta sia gli id generati via JS sia quelli renderizzati in PHP
            const numEl = $('#quiz-num-questions, #quiz-num-questions-php');
            const diffEl = $('#quiz-difficulty, #quiz-difficulty-php');

            // Valore di default
            let numQuestions = 100;
            if (numEl.length) {
                const parsed = parseInt(numEl.val(), 10);
                if (!isNaN(parsed)) {
                    if (parsed < 1 || parsed > 20) {
                        showCustomAlert("Errore", "Il numero di domande deve essere compreso tra 1 e 20.", 'bg-danger btn-danger');
                        button.prop('disabled', false);
                        fetchDynamicPriceAndUpdateButton();
                        return;
                    }
                    numQuestions = parsed;
                }
            }

            // Difficoltà: ci aspettiamo i valori 'facile', 'media', 'difficile' (default 'media')
            let difficulty = 'medium';
            if (diffEl.length) {
                const val = diffEl.val();
                if (val) {
                    difficulty = val;
                    if (difficulty !== 'easy' && difficulty !== 'medium' && difficulty !== 'hard') {
                        showCustomAlert("Errore", "La difficoltà deve essere facile, media o difficile.", 'bg-danger btn-danger');
                        button.prop('disabled', false);
                        fetchDynamicPriceAndUpdateButton();
                        return;
                    }
                }
            }

            formData.append('num_questions', numQuestions);
            formData.append('difficulty', difficulty);
        }

        // Chiamata AJAX con jQuery
        $.ajax({
            url: env_studia_con_ai.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            // timeout: 30000, // 30 secondi di timeout
            beforeSend: function() {

            },
            success: function(response) {
                if (response.success) {
                    // Mostra messaggio di successo
                    showCustomAlert("Generazione avviata!", response.data.message, 'bg-success btn-success');
                    // Se si tratta di quiz, mostra direttamente il player con i dati ricevuti
                    if (selectedAction === 'quiz' && response.data && response.data.quiz_data && Array.isArray(response.data.quiz_data.quiz)) {
                        console.log('Entro nel if');
                        try {
                            const quizArray = response.data.quiz_data.quiz;
                            const difficulty = response.data.difficulty || 'medium';
                            // Nascondi sezioni non necessarie e mostra player
                            $('.studia-ai-generate-summary').hide();
                            quizConfigRow.hide();
                            // Nascondi la sezione con i dettagli del documento caricato
                            $('.studia-ai-document-analysis').hide();
                            loadSummaryJobs();
                            updatePointsDisplay();
                            initQuizPlayer(quizArray, difficulty);
                            newGenerationButton.show();
                            return; // evita resto del flusso generazioni
                        } catch (e) {
                            console.error('Errore inizializzazione quiz:', e);
                        }
                    }

                    // Resetta il pulsante e ricalcola prezzo per mantenere lo stato coerente
                    button.prop('disabled', false);
                    fetchDynamicPriceAndUpdateButton();
                    
                    // Aggiorna la lista dei job
                    loadSummaryJobs();
                    
                    // Aggiorna i punti visualizzati nel sito
                    updatePointsDisplay();
                    
                    // Mostra la sezione delle generazioni
                    $('.studia-ai-generate-summary').hide();
                    $('.card:has(#jobs-container)').show();
                    // Mostra il pulsante per avviare una nuova generazione
                    newGenerationButton.show();
                    
                    // Se è un duplicato, scorri automaticamente alla sezione generazioni
                    if (isDuplicate) {
                        setTimeout(() => {
                            $('html, body').animate({
                                scrollTop: $('.card:has(#jobs-container)').offset().top - 100
                            }, 800);
                        }, 1000);
                    }
                } else {
                    if (response.data.message === 'Not enough points to remove') {
                        showCustomAlert("Errore", "Non hai abbastanza punti per effettuare la generazione.", 'bg-danger btn-danger');
                    } else {
                        showCustomAlert("Errore", response.data.message || 'Errore durante l\'avvio della generazione', 'bg-danger btn-danger');
                    }
                    button.prop('disabled', false);
                    fetchDynamicPriceAndUpdateButton();
                }
            },
            error: function(xhr, status, error) {
                const errorMessage = handleAjaxError(xhr, status, error);
                showCustomAlert("Errore", errorMessage, 'bg-danger btn-danger');
                
                button.prop('disabled', false);
                updateGenerateButtonLabel();
            },
            complete: function() {
                console.log('Avvio generazione completato');
                updatePointsDisplay();
            }
        });
    });

    // Gestione della coda delle generazioni
    function loadSummaryJobs(page = 1) {
        const container = $('#jobs-container');
        currentPage = page;

        $.ajax({
            url: env_studia_con_ai.ajax_url,
            type: 'POST',
            data: {
                action: 'get_summary_jobs',
                nonce: env_studia_con_ai.nonce_summary_jobs,
                page: page,
                per_page: itemsPerPage
            },
            success: function(response) {
                if (response.success) {
                    allJobs = response.data.jobs || {};
                    totalJobs = response.data.total || 0;
                    displaySummaryJobs(allJobs);
                    displayPagination(response.data.total || 0, page);
                } else {
                    container.html('<div class="alert alert-danger">Errore nel caricamento delle generazioni</div>');
                    $('#jobs-pagination').hide();
                }
            },
            error: function() {
                container.html('<div class="alert alert-danger">Errore di connessione</div>');
                $('#jobs-pagination').hide();
            }
        });
    }

    function displaySummaryJobs(jobs) {
        const container = $('#jobs-container');
        
        if (Object.keys(jobs).length === 0) {
            container.html(`
                <div class="text-center text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h5>Nessuna generazione trovata</h5>
                    <p>I tuoi riassunti appariranno qui una volta avviati.</p>
                </div>
            `);
            $('#jobs-pagination').hide();
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr><th>Documento</th><th>Tipo</th><th>Stato</th><th>Data</th><th>Azioni</th></tr></thead><tbody>';
        
        // Converti l'oggetto jobs in array e ordina per data (più recente prima)
        const jobsArray = Object.keys(jobs).map(jobId => ({
            jobId: jobId,
            ...jobs[jobId]
        })).sort((a, b) => new Date(b.request_date) - new Date(a.request_date));
        
        jobsArray.forEach(job => {
            const config = job.config ? JSON.parse(job.config) : {};
            const status = getStatusBadge(job.status);
            const date = new Date(job.request_date).toLocaleString('it-IT');
            const requestType = getRequestTypeLabel(job.request_type);
            
            // Ottieni il nome del file o il titolo del documento
            const fileName = job.file_name || 'Documento sconosciuto';
            const isPlatformDocument = job.is_platform_document || false;
            
            // Determina il sottotitolo in base al tipo di documento
            let subtitle = '';
            if (!env_studia_con_ai.hide_params && config && (config.mode || config.language)) {
                if (isPlatformDocument) {
                    subtitle = `${config.mode || ''} ${config.mode && config.language ? '•' : ''} ${config.language || ''}`.trim();
                } else {
                    const parts = [config.mode, config.language, config.detail_level].filter(Boolean);
                    subtitle = parts.join(' • ');
                }
            }
            
            html += `
                <tr>
                    <td>
                        <strong>${fileName}</strong><br>
                        <small class="text-muted">${subtitle}</small>
                    </td>
                    <td>${requestType}</td>
                    <td>${status}</td>
                    <td>${date}</td>
                    <td>
                        ${getActionButtons(job)}
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        container.html(html);
    }
    
    function displayPagination(totalItems, currentPage) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const paginationContainer = $('#pagination-container');
        const paginationInfo = $('#pagination-info');
        const paginationDiv = $('#jobs-pagination');
        
        if (totalPages <= 1) {
            paginationDiv.hide();
            return;
        }
        
        paginationDiv.show();
        
        // Calcola gli elementi da mostrare
        const startItem = (currentPage - 1) * itemsPerPage + 1;
        const endItem = Math.min(currentPage * itemsPerPage, totalItems);
        
        // Aggiorna le informazioni
        paginationInfo.text(`Mostrando ${startItem}-${endItem} di ${totalItems} generazioni`);
        
        // Genera i pulsanti di paginazione
        let paginationHtml = '';
        
        // Pulsante "Precedente"
        if (currentPage > 1) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1})" aria-label="Precedente">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>`;
        } else {
            paginationHtml += `<li class="page-item disabled">
                <span class="page-link" aria-label="Precedente">
                    <span aria-hidden="true">&laquo;</span>
                </span>
            </li>`;
        }
        
        // Calcola il range di pagine da mostrare
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        // Aggiusta il range se necessario
        if (endPage - startPage < 4) {
            if (startPage === 1) {
                endPage = Math.min(totalPages, startPage + 4);
            } else {
                startPage = Math.max(1, endPage - 4);
            }
        }
        
        // Pulsanti delle pagine
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHtml += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>`;
            }
        }
        
        // Pulsante "Successiva"
        if (currentPage < totalPages) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1})" aria-label="Successiva">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>`;
        } else {
            paginationHtml += `<li class="page-item disabled">
                <span class="page-link" aria-label="Successiva">
                    <span aria-hidden="true">&raquo;</span>
                </span>
            </li>`;
        }
        
        paginationContainer.html(paginationHtml);
    }
    
    // Funzione globale per cambiare pagina
    window.changePage = function(page) {
        loadSummaryJobs(page);
    };

    function getRequestTypeLabel(requestType) {
        const types = {
            'summary': 'Riassunto',
            'riassunto': 'Riassunto',
            'quiz': 'Quiz',
            'mappa': 'Mappa Concettuale',
            'mindmap': 'Mappa Concettuale',
            'evidenza': 'Evidenziazione',
            'highlight': 'Evidenziazione',
            'interroga': 'Interrogazione'
        };
        return types[requestType] || requestType;
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">In elaborazione</span>',
            'processing': '<span class="badge bg-info">In elaborazione</span>',
            'completed': '<span class="badge bg-success">Completato</span>',
            'error': '<span class="badge bg-danger">Errore</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">Sconosciuto</span>';
    }

    function getActionButtons(job) {
        let buttons = `<button class="btn btn-sm btn-outline-primary" onclick="showJobDetails(${job.job_id})">
            <i class="fas fa-eye"></i> Dettagli
        </button>`;

        if (job.status === 'completed') {
            if (job.request_type === 'quiz') {
                buttons += ` <button class="btn btn-sm btn-success" onclick="downloadQuiz(${job.job_id})">
                    <i class="fas fa-play"></i> Avvia Quiz
                </button>`;
            } else {
                buttons += ` <button class="btn btn-sm btn-success" onclick="downloadSummary(${job.job_id})">
                    <i class="fas fa-download"></i> Scarica
                </button>`;
            }
        }
                
        return buttons;
    }

    // Carica i job all'avvio
    loadSummaryJobs(1);

    // Aggiorna la lista quando si clicca su "Aggiorna"
    $('#refresh-jobs').off('click').on('click', function() {
        const button = $(this);
        
        // Previene chiamate multiple se il pulsante è già disabilitato
        if (button.prop('disabled')) {
            return;
        }
        
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Resetta alla prima pagina
        currentPage = 1;
        loadSummaryJobs(1);
        
        setTimeout(() => {
            button.prop('disabled', false);
            button.html('<i class="fas fa-sync-alt"></i> Aggiorna');
        }, 1000);
    });

    // Funzione globale per mostrare i dettagli del job
    // Evita di ridefinire le funzioni se sono già state definite
    if (!window.showJobDetails) {
        window.showJobDetails = function(jobId) {
        $('#jobDetailsModal').modal('show');
        $('#jobDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Caricamento...</div>');

        $.ajax({
            url: env_studia_con_ai.ajax_url,
            type: 'POST',
            data: {
                action: 'get_job_details',
                job_id: jobId,
                nonce: env_studia_con_ai.nonce_get_job_details
            },
            success: function(response) {
                if (response.success) {
                    displayJobDetails(response.data);
                } else {
                    $('#jobDetailsContent').html(`
                        <div class="alert alert-danger">
                            <h6>Errore</h6>
                            <p>${response.data.message}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#jobDetailsContent').html(`
                    <div class="alert alert-danger">
                        <h6>Errore di connessione</h6>
                        <p>Impossibile caricare i dettagli del job.</p>
                    </div>
                `);
            }
        });
    };
    } // Chiusura del blocco if (!window.showJobDetails)

    // Funzione globale per il download sicuro del riassunto
    if (!window.downloadSummary) {
        window.downloadSummary = function(jobId) {
            // Disabilita il pulsante durante la richiesta
            const button = $(`button[onclick="downloadSummary(${jobId})"]`);
            const originalText = button.html();
            
            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i> Generazione URL...');
            
            $.ajax({
                url: env_studia_con_ai.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_summary_download_url',
                    job_id: jobId,
                    nonce: env_studia_con_ai.nonce_summary_download
                },
                success: function(response) {
                    if (response.success) {
                        // Avvia il download con l'URL sicuro
                        window.location.href = response.data.download_url;
                        
                        // Mostra messaggio di successo
                        showCustomAlert(
                            "Download avviato!",
                            "Il download del riassunto è stato avviato con successo.",
                            "bg-success btn-success"
                        );
                    } else {
                        showCustomAlert(
                            "Errore nel download",
                            response.data.message || "Impossibile generare l'URL di download.",
                            "bg-warning btn-warning"
                        );
                    }
                },
                error: function(xhr, status, error) {
                    showCustomAlert(
                        "Errore di connessione",
                        "Si è verificato un errore durante la richiesta di download.",
                        "bg-danger btn-danger"
                    );
                },
                complete: function() {
                    // Ripristina il pulsante
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        };
    } // Chiusura del blocco if (!window.downloadSummary)

    // Funzione globale per avviare un quiz dal job (recupera il JSON dal backend e lo mostra)
    if (!window.downloadQuiz) {
        window.downloadQuiz = function(jobId) {
            // Se siamo sulla pagina profilo-utente-generazioni-ai, reindirizza a studia-con-ai aprendo il quiz
            try {
                const path = window.location.pathname || '';
                if (path.indexOf('profilo-utente-generazioni-ai') !== -1 || path.indexOf('profilo-utente') !== -1) {
                    const base = (env_studia_con_ai && env_studia_con_ai.home_url) ? env_studia_con_ai.home_url : window.location.origin;
                    window.location.href = base.replace(/\/$/, '') + '/studia-con-ai/?open_quiz_job=' + encodeURIComponent(jobId) + '&action=quiz';
                    return;
                }
            } catch (e) {
                console.error('Errore redirect quiz from profile:', e);
            }

            const button = $(`button[onclick="downloadQuiz(${jobId})"]`);
            const originalText = button.html();

            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i> Preparazione quiz...');

            $.ajax({
                url: env_studia_con_ai.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_quiz_download_url',
                    job_id: jobId,
                    nonce: env_studia_con_ai.nonce_summary_download
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Se il backend ha restituito direttamente il JSON del quiz
                        if (response.data.quiz_json) {
                            const json = response.data.quiz_json;
                            const quizArray = json.quiz_data;
                            const difficulty = response.data.difficulty || (json.difficulty) || 'medium';
                            $('.studia-ai-document-analysis').hide();
                            uploadSection.hide();
                            quizConfigRow.hide();
                            // Se c'è un documento caricato, nascondilo
                            if (env_studia_con_ai.has_document) {
                                documentDetailsRow.hide();
                                generateSection.hide();
                                // Chiudi il modal con i dettagli della generazione
                                $('#jobDetailsModal').modal('hide');
                            }
                            initQuizPlayer(quizArray, difficulty);
                            newGenerationButton.show();
                            // Mostra la feature dei quiz selezionata e rimuove la classe active da tutte le altre
                            $('.studia-ai-feature-item').removeClass('active');
                            $('.studia-ai-feature-item[data-action="quiz"]').addClass('active');
                            return;
                        } else {
                            showCustomAlert('Errore', 'Impossibile ottenere il quiz.', 'bg-warning btn-warning');
                        }
                    } else {
                        showCustomAlert('Errore nel recupero quiz', response.data && response.data.message ? response.data.message : 'Impossibile ottenere il quiz.', 'bg-warning btn-warning');
                    }
                },
                error: function() {
                    showCustomAlert('Errore di connessione', 'Si è verificato un errore durante la richiesta.', 'bg-danger btn-danger');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        };
    }

    // Funzione globale per il retry dei job (placeholder per ora)
    if (!window.retryJob) {
        window.retryJob = function(jobId) {
            showCustomAlert("Info", "Funzionalità di retry in sviluppo", 'bg-info btn-info');
        };
    } // Chiusura del blocco if (!window.retryJob)

    function displayJobDetails(job) {
        const config = job.config ? JSON.parse(job.config) : {};
        const date = new Date(job.request_date).toLocaleString('it-IT');
        const requestType = getRequestTypeLabel(job.request_type);
        
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informazioni Generali</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Tipo:</strong></td><td>${requestType}</td></tr>
                        <tr><td><strong>Stato:</strong></td><td>${getStatusBadge(job.status)}</td></tr>
                        <tr><td><strong>Data richiesta:</strong></td><td>${date}</td></tr>
                        <tr><td><strong>Costo (Punti Pro):</strong></td><td>${job.points_cost ? job.points_cost + (job.points_cost === 1 ? ' punto' : ' punti') : '-'}</td></tr>
                    </table>
                </div>`;
        // Se è un quiz, mostra i dettagli nella colonna accanto (se presente), altrimenti sotto
        if (job.request_type === 'quiz') {
            const numQ = config.question_number || config.num_questions || '-';
            const diffRaw = (config.difficulty || '-').toString().toLowerCase();
            const diffIt = diffRaw === 'easy' || diffRaw === 'facile' ? 'Facile' : (diffRaw === 'medium' || diffRaw === 'media' ? 'Media' : (diffRaw === 'hard' || diffRaw === 'difficile' ? 'Difficile' : (config.difficulty || '-')));
            html += `
                <div class="col-md-6">
                    <h6>Dettagli Quiz</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Numero domande:</strong></td><td>${numQ}</td></tr>
                        <tr><td><strong>Difficoltà:</strong></td><td>${diffIt}</td></tr>
                    </table>
                </div>
            `;
        }
        html += `
                ${!env_studia_con_ai.hide_params ? `
                <div class="col-md-6">
                    <h6>Parametri Configurazione</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Modalità:</strong></td><td>${config.mode ? (config.mode.charAt(0).toUpperCase() + config.mode.slice(1)) : '-'}</td></tr>
                        <tr><td><strong>Lingua:</strong></td><td>${config.language ? (config.language.charAt(0).toUpperCase() + config.language.slice(1)) : '-'}</td></tr>
                        <tr><td><strong>Dettaglio:</strong></td><td>${config.detail_level ? (config.detail_level.charAt(0).toUpperCase() + config.detail_level.slice(1)) : '-'}</td></tr>
                        <tr><td><strong>Tempo Lettura:</strong></td><td>${config.reading_time ? (config.reading_time.charAt(0).toUpperCase() + config.reading_time.slice(1)) : '-'}</td></tr>
                    </table>
                </div>` : ''}
            </div>
        `;
        
        if (job.error_message) {
            html += `
                <div class="alert alert-danger mt-3">
                    <h6>Errore</h6>
                    <p>${job.error_message}</p>
                </div>
            `;
        }
        
        if (job.status === 'completed') {
            const puntiText = job.points_cost ? (job.points_cost === 1 ? '1 punto' : job.points_cost + ' punti') : '-';
            html += `
                <div class="alert alert-success mt-3">
                    <h6>Risultato disponibile</h6>
                    <p><strong>Costo utilizzato:</strong> ${puntiText} Pro</p>
                    <button class="btn btn-success" onclick="${job.request_type === 'quiz' ? `downloadQuiz(${job.job_id})` : `downloadSummary(${job.job_id})`}">
                        <i class="fas fa-${job.request_type === 'quiz' ? 'play' : 'download'}"></i> ${job.request_type === 'quiz' ? 'Avvia quiz' : 'Scarica risultato'}
                    </button>
                </div>`;
        }
        
        $('#jobDetailsContent').html(html);
    }

    // Funzione globale per riprovare un job (placeholder per ora)
    if (!window.retryJob) {
        window.retryJob = function(jobId) {
        showCustomAlert("Info", "Funzionalità di retry in sviluppo", 'bg-info btn-info');
    };
    } // Chiusura del blocco if (!window.retryJob)

    function showError(message) {
        showCustomAlert("Errore", message, 'bg-danger btn-danger');
        resetStudiaAiUpload();
    }

    // Funzione di utilità per gestire errori AJAX
    function handleAjaxError(xhr, status, error) {
        let errorMessage = 'Si è verificato un errore imprevisto.';
        
        if (status === 'timeout') {
            errorMessage = 'La richiesta è scaduta. Riprova.';
        } else if (xhr.status === 413) {
            errorMessage = 'File troppo grande. Il file non deve superare i 10MB.';
        } else if (xhr.status === 0) {
            errorMessage = 'Errore di rete. Verifica la tua connessione.';
        } else if (xhr.status === 500) {
            errorMessage = 'Errore del server. Riprova più tardi.';
        } else if (xhr.status === 403) {
            errorMessage = 'Accesso negato. Verifica di essere autenticato.';
        } else if (xhr.status === 404) {
            errorMessage = 'Risorsa non trovata.';
        }
        
        return errorMessage;
    }

    function resetStudiaAiUpload() {
        // Se c'è un documento selezionato, aggiorna l'URL senza ricaricare la pagina
        if (env_studia_con_ai && env_studia_con_ai.has_document && env_studia_con_ai.document_id) {
            uploadSection.hide();
            documentDetailsRow.show();
            generateSection.show();
        } else {
            uploadSection.show();
            documentDetailsRow.hide();
            generateSection.hide();
        }        
        progressSection.hide();
        analysisSection.hide();
        // Se è quiz e c'è un documento selezionato, mostra la sezione di configurazione
        if (env_studia_con_ai && env_studia_con_ai.action === 'quiz' && env_studia_con_ai.has_document) {
            quizConfigRow.show();
        } else {
            quizConfigRow.hide();
        }
        configSection.hide();
        
        newGenerationButton.hide();
        hideQuizPlayer();
              
        fileInput.val('');
        progressBar.css('width', '0%');
        progressBar.html('');
        progressBar.removeClass('progress-bar-striped', 'progress-bar-animated');
        
        // Nascondi gli elementi di progresso
        fileName.addClass('d-none');
        progressSection.hide();
        
        // Rimuovi messaggi di analisi
        $('.alert-info').remove();
        
        // Reset dei dati del documento
        window.studiaAiDocumentData = null;
        
        // Reset del flag di upload
        isUploading = false;
    }

    // Gestione toggle opzioni avanzate - UN SOLO LISTENER
    $(document).off('click', '#toggle-advanced, .studia-ai-toggle-advanced, [data-toggle="advanced-options"]').on('click', '#toggle-advanced, .studia-ai-toggle-advanced, [data-toggle="advanced-options"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const advancedOptions = $('#advanced-options');
        const button = $(this);
        const icon = button.find('i');
        
        if (advancedOptions.length > 0) {
            if (advancedOptions.is(':visible')) {
                advancedOptions.slideUp(300);
                icon.removeClass('fa-chevron-up').addClass('fa-cog');
                button.removeClass('btn-secondary').addClass('btn-outline-secondary');
            } else {
                advancedOptions.slideDown(300);
                icon.removeClass('fa-cog').addClass('fa-chevron-up');
                button.removeClass('btn-outline-secondary').addClass('btn-secondary');
            }
        }
    });
});



 