<div class="container py-5">
    <!-- Titolo principale -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <h1 class="display-3 fw-bold mb-3">Supporto e Contatti</h1>
            <p class="lead text-muted">Siamo qui per aiutarti. Trova le risposte che cerchi o contattaci per assistenza personalizzata.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Sezione FAQ -->
            <div class="card shadow-sm border-0 mb-4 faq-section">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="fas fa-question-circle text-primary fs-4"></i>
                        </div>
                        <h2 class="h4 mb-0">Hai bisogno di aiuto?</h2>
                    </div>
                    <p class="text-muted mb-4">Consulta le nostre <a href="<?php echo get_permalink(get_page_by_path('faq')); ?>" class="text-primary fw-medium">FAQ</a> per trovare rapidamente risposte alle domande più comuni.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-book text-primary me-3"></i>
                                <div>
                                    <h3 class="h6 mb-1">Come funziona?</h3>
                                    <p class="small text-muted mb-0">Scopri come utilizzare al meglio i nostri servizi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-file-alt text-primary me-3"></i>
                                <div>
                                    <h3 class="h6 mb-1">Documenti</h3>
                                    <p class="small text-muted mb-0">Informazioni sui documenti e il loro utilizzo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="<?php echo get_permalink(get_page_by_path('faq')); ?>" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-search me-2"></i>Consulta le FAQ
                        </a>
                        <button class="btn btn-outline-primary btn-lg px-4" onclick="showContactForm()">
                            <i class="fas fa-envelope me-2"></i>Contattaci
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form di Contatto (inizialmente nascosto) -->
            <div id="contact-form-container" class="card shadow-sm border-0" style="display: none;">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h2 class="display-5 fw-bold mb-3">Contattaci!</h2>
                        <p class="lead text-muted">Hai domande? Scrivici, ti risponderemo al più presto.</p>
                    </div>

                    <form id="contact-form" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label class="form-label fw-medium" for="contact_name">Nome</label>
                            <input class="form-control form-control-lg" type="text" id="contact_name" name="contact_name" required>
                            <div class="invalid-feedback" id="error_name">Il campo Nome è obbligatorio.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium" for="contact_email">Email</label>
                            <input class="form-control form-control-lg" type="email" id="contact_email" name="contact_email" required>
                            <div class="invalid-feedback" id="error_email">Il campo Email è obbligatorio.</div>
                            <div class="invalid-feedback" id="error_email_format">Fornisci un indirizzo email valido.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium" for="contact_message">Messaggio</label>
                            <textarea class="form-control form-control-lg" id="contact_message" name="contact_message" rows="5" required></textarea>
                            <div class="invalid-feedback" id="error_message">Il campo Messaggio è obbligatorio.</div>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary btn-lg px-5" type="button" onclick="sendContactEmail()">
                                <div id="icon-loading-send" class="btn-loader mx-2" hidden>
                                    <span class="spinner-border spinner-border-sm"></span>
                                </div>
                                Invia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

