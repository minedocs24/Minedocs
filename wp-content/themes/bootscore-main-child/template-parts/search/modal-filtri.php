<?php
$tipo_istituto = get_lista_tipo_istituto();
$istituti = get_lista_istituto();
$corsi = get_lista_materia();
$corsi_di_studio = get_lista_corso_di_studi();
$anni_accademici = get_lista_anni_accademici(true);
$tipi_documento = get_lista_tipo_documento();

?>
<!-- Modal per i filtri -->
<div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title" id="filtersModalLabel">
                    <i class="fas fa-filter text-primary me-2"></i>
                    Filtra i risultati
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filters-container">
                    <div class="mb-4">
                        <label for="search-institute" class="form-label d-flex align-items-center gap-2">
                            <i class="fas fa-university text-muted"></i>
                            Istituto
                        </label>
                        <select class="form-select text-truncate" id="search-institute">
                            <option value="">Seleziona Istituto</option>
                            <?php foreach ($istituti as $istituto): ?>
                            <option value="<?php echo esc_attr($istituto['id']); ?>">
                                <?php echo esc_html($istituto['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="search-course" class="form-label d-flex align-items-center gap-2">
                            <i class="fas fa-graduation-cap text-muted"></i>
                            Corso di Studi
                        </label>
                        <select class="form-select text-truncate" id="search-course">
                            <option value="">Seleziona Corso di Studi</option>
                            <?php foreach ($corsi_di_studio as $corso): ?>
                            <option value="<?php echo esc_attr($corso['id']); ?>">
                                <?php echo esc_html($corso['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="search-subject" class="form-label d-flex align-items-center gap-2">
                            <i class="fas fa-book text-muted"></i>
                            Materia
                        </label>
                        <select class="form-select text-truncate" id="search-subject">
                            <option value="">Seleziona Materia</option>
                            <?php foreach ($corsi as $corso): ?>
                            <option value="<?php echo esc_attr($corso['id']); ?>">
                                <?php echo esc_html($corso['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="search-type" class="form-label d-flex align-items-center gap-2">
                            <i class="fas fa-file-alt text-muted"></i>
                            Tipo di Documento
                        </label>
                        <select class="form-select text-truncate" id="search-type">
                            <option value="">Seleziona Tipo di Documento</option>
                            <?php foreach ($tipi_documento as $tipo): ?>
                            <option value="<?php echo esc_attr($tipo['id']); ?>">
                                <?php echo esc_html($tipo['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="search-academic-year" class="form-label d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            Anno Accademico
                        </label>
                        <select class="form-select text-truncate" id="search-academic-year">
                            <option value="">Seleziona Anno Accademico</option>
                            <?php foreach ($anni_accademici as $anno): ?>
                            <option value="<?php echo esc_attr($anno['id']); ?>">
                                <?php echo esc_html($anno['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="hide-purchased-documents">
                        <label class="form-check-label d-flex align-items-center gap-2" for="hide-purchased-documents">
                            <i class="fas fa-eye-slash text-muted"></i>
                            Nascondi documenti già acquistati
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" id="apply-filters">
                    <i class="fas fa-check me-2"></i>
                    Applica Filtri
                </button>
            </div>
        </div>
    </div>
</div>

