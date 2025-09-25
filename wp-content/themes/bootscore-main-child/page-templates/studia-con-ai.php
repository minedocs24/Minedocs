<?php
/**
 * Template Name: Studia con AI
 */

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    wp_redirect( LOGIN_PAGE );
    return;
}

// Funzione per ottenere l'etichetta dell'azione
function getActionLabel($action) {
    $labels = array(
        'riassunto' => 'il riassunto',
        'summary' => 'il riassunto',
        'mappa' => 'la mappa concettuale',
        'mindmap' => 'la mappa concettuale',
        'quiz' => 'il quiz',
        'evidenza' => 'l\'evidenziazione',
        'highlight' => 'l\'evidenziazione',
        'interroga' => 'l\'interrogazione'
    );
    return isset($labels[$action]) ? $labels[$action] : 'il riassunto';
}

// Recupera i parametri dalla URL
$document_id = isset($_GET['document_id']) ? sanitize_text_field($_GET['document_id']) : '';
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'riassunto';

// Lista delle azioni disponibili (solo riassunto per ora)
$available_actions = array('riassunto', 'summary');

// Se l'azione non è disponibile, reindirizza a riassunto
if (!in_array($action, $available_actions)) {
    $action = 'riassunto';
    // Se c'è un documento, mantieni il documento ma cambia l'azione
    if (!empty($document_id)) {
        $redirect_url = home_url('/studia-con-ai/') . '?document_id=' . urlencode($document_id) . '&action=riassunto';
        wp_redirect($redirect_url);
        exit;
    }
}

// Se non c'è un documento selezionato, mostra la pagina normale
$has_document = !empty($document_id);

get_header();

// Includi il popup personalizzato per gli alert
get_template_part('template-parts/commons/custom-popup');

// Definisci le variabili JavaScript dopo get_header()
wp_enqueue_script('studia-con-ai-script', get_stylesheet_directory_uri() . '/assets/js/studia-con-ai.js', array('jquery'), null, true);
wp_localize_script('studia-con-ai-script', 'env_studia_con_ai', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('studia_con_ai_nonce'),
    'nonce_generate_summary' => wp_create_nonce('nonce_generate_summary'),
    'nonce_get_dynamic_price' => wp_create_nonce('nonce_get_dynamic_price'),
    'nonce_summary_jobs' => wp_create_nonce('nonce_summary_jobs'),
    'nonce_document_details' => wp_create_nonce('nonce_document_details'),
    'nonce_analyze_document' => wp_create_nonce('nonce_analyze_document'),
    'nonce_get_job_details' => wp_create_nonce('nonce_get_job_details'),
    'nonce_delete_job' => wp_create_nonce('nonce_delete_job'),
    'nonce_summary_download' => wp_create_nonce('nonce_summary_download'),
    'home_url' => home_url(),
    'document_id' => $document_id,
    'action' => $action,
    'has_document' => $has_document,
    'hide_params' => true
));

?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Studia con AI</h1>
            <p class="lead mb-5">Utilizza l'intelligenza artificiale per migliorare il tuo studio</p>
        </div>
    </div>

    <!-- Sezione delle funzionalità AI (sempre visibile) -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="studia-ai-features-grid">
                <!-- Crea un riassunto -->
                <div class="studia-ai-feature-item <?php echo ($action === 'riassunto' || $action === 'summary') ? 'active' : ''; ?>" data-action="riassunto">
                    <div class="studia-ai-feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="studia-ai-feature-text">Crea un riassunto</div>
                </div>

                <!-- Crea una mappa concettuale -->
                <div class="studia-ai-feature-item <?php echo ($action === 'mappa' || $action === 'mindmap') ? 'active' : ''; ?> coming-soon" data-action="mappa">
                    <div class="studia-ai-feature-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="studia-ai-feature-text">Crea una mappa concettuale <span class="badge bg-secondary">Coming Soon</span></div>
                </div>

                <!-- Evidenzia nel documento -->
                <div class="studia-ai-feature-item <?php echo ($action === 'evidenza' || $action === 'highlight') ? 'active' : ''; ?> coming-soon" data-action="evidenza">
                    <div class="studia-ai-feature-icon">
                        <i class="fas fa-highlighter"></i>
                    </div>
                    <div class="studia-ai-feature-text">Evidenzia nel documento <span class="badge bg-secondary">Coming Soon</span></div>
                </div>

                <div class="studia-ai-feature-item <?php echo ($action === 'quiz') ? 'active' : ''; ?> coming-soon" data-action="quiz">
                    <div class="studia-ai-feature-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="studia-ai-feature-text">Crea un quiz <span class="badge bg-secondary">Coming Soon</span></div>
                </div>

                <div class="studia-ai-feature-item <?php echo ($action === 'interroga') ? 'active' : ''; ?> coming-soon" data-action="interroga">
                    <div class="studia-ai-feature-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="studia-ai-feature-text">Interroga il documento <span class="badge bg-secondary">Coming Soon</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione documento (condizionale) -->
    <?php if ($has_document): ?>
    <!-- Sezione documento selezionato -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Documento selezionato
                        </h5>
                        <a href="<?php echo home_url('/studia-con-ai/') . '?action=' . urlencode($action); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Scegli altro documento
                        </a>
                    </div>
                </div>
                <div class="card-body" id="document-details">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Caricamento...</span>
                        </div>
                        <p class="mt-2">Caricamento dettagli documento...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Sezione di caricamento del documento (solo se non c'è un documento selezionato) -->
    <div class="row">
        <div class="col-12">
            <div id="studiaAiUploadSection" class="studia-ai-upload-section">
                <img fetchpriority="high" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/upload_logo.svg" alt="Upload logo">
                <p class="text-large">Trascina qui il tuo documento</p>
                <p class="text-small">Oppure</p>
                <button class="button-custom button-custom-blue" onclick="document.getElementById('studiaAiFileInput').click()">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/add.svg" class="add-icon">
                    Scegli tra i file
                </button>
                <input type="file" id="studiaAiFileInput" class="d-none" 
                       accept=".pdf">
                <p class="text-limit-section">Limite massimo 10 MB per file. Formati accettati: PDF</p>
            </div>
        </div>
    </div>

    <!-- Sezione progresso caricamento -->
    <div class="row">
        <div class="col-12">
            <div class="progress-section my-4" id="studiaAiProgressSection" style="display: none;">
                <p id="studiaAiFileName" class="file-name">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/tick.png" alt="Success" class="me-2"> 
                    <span class="filename-text">Filename</span>
                </p>
                <div class="progress" id="studiaAiProgressBar">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                         style="width: 0%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione di analisi del documento (nascosta inizialmente) -->
    <div class="row studia-ai-document-analysis" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Documento caricato</h5>
                    <div class="studia-ai-document-details">
                        <p><strong>Titolo:</strong> <span class="studia-ai-document-title"></span></p>
                        <p><strong>Pagine:</strong> <span class="studia-ai-document-pages"></span></p>
                        <p><strong>Parole:</strong> <span class="studia-ai-document-words"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sezione di configurazione del riassunto (nascosta inizialmente) -->
    <div class="row studia-ai-summary-config mt-4" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-cog text-primary me-2"></i>
                        Configura <?php echo getActionLabel($action); ?>
                        <span class="badge bg-success ms-2">Configurazione attiva</span>
                    </h5>
                    <form id="studia-ai-summary-form">
                        <!-- Configurazioni principali -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="summary-mode" class="form-label">Modalità</label>
                                <select class="form-select" id="summary-mode" name="mode" required>
                                    <option value="discorsivo">Discorsivo</option>
                                    <option value="elenco">Elenco puntato</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="detail-level" class="form-label">Livello di dettaglio</label>
                                <select class="form-select" id="detail-level" name="detail_level" required>
                                    <option value="alto">Alto</option>
                                    <option value="medio">Medio</option>
                                    <option value="basso">Basso</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="summary-language" class="form-label">Lingua</label>
                                <select class="form-select" id="summary-language" name="language" required>
                                    <option value="italiano">Italiano</option>
                                    <option value="inglese">Inglese</option>
                                    <option value="francese">Francese</option>
                                    <option value="tedesco">Tedesco</option>
                                    <option value="spagnolo">Spagnolo</option>
                                    <option value="portoghese">Portoghese</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reading-time" class="form-label">Tempo di lettura</label>
                                <select class="form-select" id="reading-time" name="reading_time" required>
                                    <option value="breve">Breve (2-3 minuti)</option>
                                    <option value="medio">Medio (5-7 minuti)</option>
                                    <option value="lungo">Lungo (10-15 minuti)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sezione avanzate -->
                        <div class="studia-ai-advanced-section mt-4">
                            <div class="d-flex align-items-center mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm me-2 studia-ai-toggle-advanced" id="toggle-advanced" data-toggle="advanced-options">
                                    <i class="fas fa-cog"></i> Opzioni avanzate
                                </button>
                                <span class="text-muted small">Personalizza ulteriormente il riassunto</span>
                            </div>
                            
                            <div id="advanced-options" class="studia-ai-advanced-options" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="max-words" class="form-label">Numero massimo di parole</label>
                                        <input type="number" class="form-control" id="max-words" name="max_words" min="100" max="5000" value="">
                                        <input type="hidden" name="max_words" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="min-words" class="form-label">Numero minimo di parole</label>
                                        <input type="number" class="form-control" id="min-words" name="min_words" min="50" max="1000" value="">
                                        <input type="hidden" name="min_words" value="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="include-quotes" class="form-label">Includi citazioni testuali</label>
                                        <select class="form-select" id="include-quotes" name="include_quotes">
                                            <option value="">Seleziona...</option>
                                            <option value="no">No</option>
                                            <option value="si">Sì</option>
                                        </select>
                                        <input type="hidden" name="include_quotes" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tone" class="form-label">Tono</label>
                                        <select class="form-select" id="tone" name="tone">
                                            <option value="">Seleziona...</option>
                                            <option value="neutro">Neutro</option>
                                            <option value="informale">Informale</option>
                                            <option value="professionale">Professionale</option>
                                            <option value="tecnico">Tecnico</option>
                                        </select>
                                        <input type="hidden" name="tone" value="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="comprehension-level" class="form-label">Livello di comprensione</label>
                                        <select class="form-select" id="comprehension-level" name="comprehension_level">
                                            <option value="">Seleziona...</option>
                                            <option value="liceale">Liceale</option>
                                            <option value="universitario">Universitario</option>
                                            <option value="esperto">Esperto</option>
                                            <option value="bambino">Bambino</option>
                                        </select>
                                        <input type="hidden" name="comprehension_level" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="summary-objective" class="form-label">Obiettivo riassunto</label>
                                        <select class="form-select" id="summary-objective" name="summary_objective">
                                            <option value="">Seleziona...</option>
                                            <option value="studiare">Studiare</option>
                                            <option value="presentare">Presentare</option>
                                            <option value="condividere">Condividere online</option>
                                            <option value="ripetere">Ripetere</option>
                                        </select>
                                        <input type="hidden" name="summary_objective" value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rimuoviamo il tasto di conferma per mantenere la sezione sempre visibile -->
                        <div class="text-end mt-4">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Le opzioni selezionate verranno utilizzate automaticamente per la generazione del riassunto
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione di generazione del riassunto (nascosta inizialmente) -->
    <div class="row studia-ai-generate-summary mt-4" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">Genera <?php echo getActionLabel($action); ?></h5>
                    <div class="alert alert-info mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Generazione asincrona:</strong> Il riassunto verrà generato in background utilizzando le opzioni configurate.
                        <br><small>Puoi continuare a navigare sul sito mentre il riassunto viene elaborato. Controlla lo stato nella sezione "Le mie generazioni".</small>
                    </div>
                    <div class="alert alert-warning mb-4" role="alert">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Tempi di elaborazione:</strong> La generazione potrebbe richiedere alcuni minuti a seconda della complessità del documento.
                        <br><small>Riceverai una notifica quando il riassunto sarà pronto per il download.</small>
                    </div>
                    <button id="studia-ai-generate-summary" class="btn btn-primary btn-lg" disabled>
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Calcolo costo...
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione "Le mie generazioni" -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-alt me-2"></i>
                        Le mie generazioni
                    </h5>
                    <button id="refresh-jobs" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sync-alt"></i> Aggiorna
                    </button>
                </div>
                <div class="card-body">
                    <div id="jobs-container">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Caricamento generazioni in corso...</p>
                        </div>
                    </div>
                    
                    <!-- Paginazione -->
                    <div id="jobs-pagination" class="mt-4" style="display: none;">
                        <nav aria-label="Paginazione generazioni">
                            <ul class="pagination justify-content-center" id="pagination-container">
                                <!-- Contenuto dinamico -->
                            </ul>
                        </nav>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                <span id="pagination-info">Mostrando 0-0 di 0 generazioni</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal per i dettagli del job -->
    <div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobDetailsModalLabel">Dettagli Generazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="jobDetailsContent">
                    <!-- Contenuto dinamico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
?> 