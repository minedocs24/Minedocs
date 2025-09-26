<?php

$s = isset($_GET['search']) ? $_GET['search'] : '';

?>

<div class="search-form-wrapper">
    <div role="search" id="search-form" class="search-form">
        <div class="row justify-content-center">
            <div class="col-md-8 col-12">
                <div class="search-bar position-relative mb-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0 search-bar-input" 
                               placeholder="Cerca documenti, corsi o libri"
                               aria-label="Ricerca" 
                               name="search" 
                               value="<?php echo esc_attr($s); ?>">
                        <button type="submit" 
                            class="btn btn-primary search-button" 
                            id="search-button">
                            Cerca
                        </button>
                    </div>
                </div>

                <div class="search-controls">
                    <!-- Sezione ordinamento -->
                    <div class="d-flex align-items-center gap-2">
                        <label for="orderby" class="form-label">Ordina per:</label>
                        <select id="orderby" class="form-select form-select-sm" name="orderby">
                            <option value="date_desc">Data di pubblicazione - Dal più recente al meno recente</option>
                            <option value="date_asc">Data di pubblicazione - Dal meno recente al più recente</option>
                            <option value="title_asc">Nome documento - dalla A alla Z</option>
                            <option value="title_desc">Nome documento - dalla Z alla A</option>
                            <option value="reviews_desc">Recensioni - Dal voto più alto al voto più basso</option>
                            <option value="reviews_asc">Recensioni - Dal voto meno alto al voto più alto</option>
                            <option value="downloads_desc">Download - Dal più scaricato al meno scaricato</option>
                            <option value="downloads_asc">Download - Dal meno scaricato al più scaricato</option>
                        </select>
                    </div>

                    <!-- Sezione filtri -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label">Filtra per:</label>
                        <button id="toggle-filters" 
                                class="btn btn-primary btn-sm" 
                                data-bs-toggle="modal"
                                data-bs-target="#filtersModal">
                            <?php get_template_part('template-parts/commons/icon', null, array('icon_name'=>'filter', 'size' => 16)); ?>
                            <span>Filtri</span>
                        </button>
                        <div id="active-filters" class="d-flex flex-wrap gap-2">
                            <!-- Qui verranno mostrati i filtri selezionati -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setQueryVars(type, tipo_prodotto) {
    document.getElementById('post_type').value = type;
    document.getElementById('tipo_prodotto').value = tipo_prodotto;
}
</script>
