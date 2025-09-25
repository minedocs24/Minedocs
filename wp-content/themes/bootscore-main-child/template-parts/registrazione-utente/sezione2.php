<?php
// Includi il file CSS della registrazione
wp_enqueue_style('registrazione-styles', get_stylesheet_directory_uri() . '/assets/css/registrazione.css');
?>

<div class="registration-section" id="section-2">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h2 class="display-6 text-center mb-4">Abbiamo quasi terminato!</h2>
            <p class="lead text-center text-muted mb-5">Manca poco! Qualche dettaglio in più...</p>

            <!-- Selezione 1: Anno di iscrizione -->
            <div class="mb-5">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label for="registration_year" class="form-label d-flex align-items-center gap-2">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/Icone_Minedocs_calendar.svg" alt="Icona Calendario" class="img-fluid" style="width: 35px; height: 35px;">
                            <span>Mi sono iscritt* nel:</span>
                        </label>
                    </div>
                    <div class="col-md-8">
                        <select name="registration_year" id="registration_year" class="form-select">
                            <?php
                            $currentYear = date("Y");
                            for ($year = $currentYear; $year >= $currentYear - 10; $year--) {
                                echo "<option value='$year'>$year</option>";
                            }
                            ?>
                        </select>
                        <small id="campo-anno-error" class="text-danger" style="display: none;">Seleziona l'anno di iscrizione.</small>
                    </div>
                </div>
            </div>

            <!-- Selezione 2: Lingue da studiare -->
            <div class="mb-4">
                <label class="form-label d-flex align-items-center gap-2">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/Icone_Minedocs_lingue.svg" alt="Icona Lingue" class="img-fluid" style="width: 35px; height: 35px;">
                    <span>Quali altre lingue ti piacerebbe studiare?</span>
                </label>
                <div class="language-options">
                    <?php
                    $languages = get_terms(array(
                        'taxonomy' => 'lingue',
                        'hide_empty' => false,
                    ));

                    // Sposta il termine "Altro" alla fine
                    usort($languages, function($a, $b) {
                        if ($a->name === 'Altro') return 1;
                        if ($b->name === 'Altro') return -1;
                        return 0;
                    });

                    if (!empty($languages) && !is_wp_error($languages)) {
                        foreach ($languages as $index => $language) {
                            $flag = get_term_meta($language->term_id, 'flag', true);
                            $hiddenClass = ($index >= 4) ? 'hidden-language' : '';
                            echo "<div class='language-option $hiddenClass' data-language='{$language->name}'>
                                    <input type='checkbox' name='languages[]' value='{$language->slug}' id='lang_{$language->slug}' class='d-none custom-checkbox'>
                                    <label for='lang_{$language->slug}' class='language-label'>
                                        <span class='flag'>$flag</span>
                                        <span class='language-name'>{$language->name}</span>
                                    </label>
                                  </div>";
                        }
                    }
                    ?>
                </div>
                <div class="text-center mt-3">
                    <a id="show-more-languages" class="btn btn-link text-decoration-none">Mostra altro</a>
                </div>
                <small id="campo-lingue-extra-error" class="text-danger" style="display: none;">Seleziona almeno una voce.</small>
            </div>

            <!-- Pulsanti di navigazione -->
            <div class="d-flex justify-content-center gap-3 mt-5">
                <a id="back2" class="btn btn-outline-secondary px-4 py-2">Indietro</a>
                <a id="next2" class="btn btn-primary px-4 py-2" type="button" onclick="saveProfilationData()">
                    <div id="icon-loading-next2" class="btn-loader mx-2" hidden style="display: inline-block;">
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                    Avanti
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const showMoreButton = document.getElementById('show-more-languages');
    const hiddenLanguages = document.querySelectorAll('.hidden-language');

    showMoreButton.addEventListener('click', () => {
        hiddenLanguages.forEach(language => language.classList.remove('hidden-language'));
        showMoreButton.style.display = 'none';
        });
    });

    document.querySelectorAll('.language-option').forEach(option => {
        option.addEventListener('click', () => {
            const checkbox = option.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            option.classList.toggle('selected', checkbox.checked);
        });
    });

    // Gestione della selezione attiva per il selettore dell'anno
    const wheel = document.querySelector('.year-selector');
    const wheelItems = document.querySelectorAll('.year-selector-item');

    function selectCenterItem() {
        const wheelHeight = wheel.clientHeight;
        const centerY = wheel.getBoundingClientRect().top + wheelHeight / 2;

        let closestItem = null;
        let closestDistance = Infinity;

        wheelItems.forEach((item) => {
            const itemRect = item.getBoundingClientRect();
            const itemCenterY = itemRect.top + itemRect.height / 2;
            const distance = Math.abs(centerY - itemCenterY);

            if (distance < closestDistance) {
                closestDistance = distance;
                closestItem = item;
            }
        });

        if (closestItem) {
            wheelItems.forEach((i) => i.classList.remove('active'));
            closestItem.classList.add('active');
            document.getElementById('registration_year').value = closestItem.dataset.year;
        }
    }

    // Centra l'anno corrente inizialmente
    const activeItem = document.querySelector('.year-selector-item.active');
    if (activeItem) {
        activeItem.scrollIntoView({
            block: 'center',
            behavior: 'smooth'
        });
    }

    // Aggiungi evento per aggiornare l'elemento attivo al termine dello scroll
    wheel.addEventListener('scroll', () => {
        clearTimeout(wheel.scrollTimeout);
        wheel.scrollTimeout = setTimeout(selectCenterItem, 100);
    });

    // Abilita lo scroll con la rotella del mouse
    wheel.addEventListener('wheel', (event) => {
        event.preventDefault();
        wheel.scrollTop += event.deltaY > 0 ? wheelItems[0].clientHeight : -wheelItems[0].clientHeight;
    });

    // Abilita il trascinamento con il mouse
    let isDragging = false;
    let startY;
    let scrollTop;

    wheel.addEventListener('mousedown', (event) => {
        isDragging = true;
        startY = event.pageY - wheel.offsetTop;
        scrollTop = wheel.scrollTop;
    });

    wheel.addEventListener('mouseleave', () => {
        isDragging = false;
    });

    wheel.addEventListener('mouseup', () => {
        isDragging = false;
    });

    wheel.addEventListener('mousemove', (event) => {
        if (!isDragging) return;
        event.preventDefault();
        const y = event.pageY - wheel.offsetTop;
        const walk = (y - startY) * 2; // Velocità di scorrimento
        wheel.scrollTop = scrollTop - walk;
    });

    // Abilita lo scroll con le frecce della tastiera
    document.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowUp') {
            wheel.scrollTop -= wheelItems[0].clientHeight;
            event.preventDefault();
        } else if (event.key === 'ArrowDown') {
            wheel.scrollTop += wheelItems[0].clientHeight;
            event.preventDefault();
        }
    });

    // Disabilita la selezione del testo durante il trascinamento
    wheel.addEventListener('mousedown', () => {
        document.body.style.userSelect = 'none';
    });

    wheel.addEventListener('mouseup', () => {
        document.body.style.userSelect = 'auto';
    });

    // Aggiungi evento per selezionare l'elemento cliccato e centrarlo
    wheelItems.forEach((item) => {
        item.addEventListener('click', () => {
            wheelItems.forEach((i) => i.classList.remove('active'));
            item.classList.add('active');
            item.scrollIntoView({
                block: 'center',
                behavior: 'smooth'
            });
        });
    });

    selectCenterItem();
</script>