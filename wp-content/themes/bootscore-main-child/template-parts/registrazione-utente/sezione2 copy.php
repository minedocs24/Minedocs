<div class="section" id="section-2">

    <!-- Testo di progresso -->
    <p class="progress-text my-3">Abbiamo quasi terminato, manca poco!<br>Qualche dettaglio in più...</p>

    <!-- Selezione 1: Anno di iscrizione -->
    <div class="box-campi">
        <div class="selection-group mb-4 d-flex justify-content-center align-items-center">

            <div class="label-container">
                <label for="registration_year" class="form-label">
                    <?php get_template_part('template-parts/commons/icon', null, array('icon_name' => 'calendar', 'size' => 30)); ?>
                    <i class="bi bi-bank"></i> Mi sono iscritto nell'anno...
                </label>
            </div>
            <div class="custom-wheel-selector">
                <div class="custom-wheel">
                    <?php
                    $currentYear = date("Y");
                    for ($year = $currentYear; $year >= $currentYear - 10; $year--) {
                        $activeClass = ($year == $currentYear) ? 'active' : '';
                        echo "<div class='custom-wheel-item $activeClass' data-year='$year'>$year</div>";                    
                    }
                    echo "<input type='hidden' name='registration_year' id='registration_year' value='$currentYear'>";
                    ?>
                </div>
            </div>
            <small id="campo-anno-error" class="text-danger" style="display: none;">Seleziona l'anno di iscrizione.</small>
        </div>

        <!-- Selezione 2: Lingue da studiare -->
        <div class="selection-group mb-4">
            <label for="language-study" class="form-label">
                🇬🇧
                Ti piacerebbe studiare altre lingue?
            </label>
            <div class="language-options d-flex flex-wrap">
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
                    foreach ($languages as $language) {
                        $flag = get_term_meta($language->term_id, 'flag', true); // Assuming 'flag' is the meta key for the flag
                        echo "<div class='language-option' data-language='{$language->name}'>
                                <input type='checkbox' name='languages[]' value='{$language->slug}' id='lang_{$language->slug}' class='d-none'>
                                <label for='lang_{$language->slug}' class='language-label'>
                                    <span class='flag'>$flag</span>
                                    <span class='language-name'>{$language->name}</span>
                                </label>
                              </div>";
                    }
                }
                ?>
            </div>
            <small id="campo-lingue-extra-error" class="text-danger" style="display: none;">Seleziona almeno una voce.</small>
        </div>

        

        <script>
        document.querySelectorAll('.language-option').forEach(option => {
            option.addEventListener('click', () => {
                const checkbox = option.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
                option.classList.toggle('selected', checkbox.checked);
            });
        });
        </script>


    </div>
    <button id="back2" class="btn btn-primary btn-next mt-3">Indietro</button>
    <button id="next2" class="btn btn-primary btn-next mt-3" type="button" onclick="saveProfilationData()">
        <div id="icon-loading-next2" class="btn-loader mx-2" hidden style="display: inline-block;">
            <span class="spinner-border spinner-border-sm"></span>
        </div>
        Avanti
    </button>
</div>

<style>

.label-container {
    flex: 1;
    text-align: left;
    display: flex;
    align-items: center;
}

.custom-wheel-selector {
    font-family: Arial, sans-serif;
    display: flex;
    flex: 2;
    /*flex-direction: column;*/
    align-items: center;
    justify-content: flex-start;
}

.custom-label {
    display: flex;
    align-items: center;
    font-size: 16px;
    margin-bottom: 10px;
}

.custom-label::before {
    content: "📅";
    margin-right: 8px;
}

.custom-wheel {
    height: 120px;
    /* Dimensione della ruota */
    overflow: hidden;
    /* Nasconde le parti fuori dalla ruota */
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0px;
    border-radius: 10px;
    position: relative;
    padding: 50px 0;
    /* Spazio per permettere agli elementi estremi di centrare */
    white-space: nowrap;
}

.custom-wheel-item {
    font-size: 18px;
    padding: 0px;
    padding-left: 20px;
    padding-right: 20px;
    cursor: pointer;
    text-align: center;
    transition: font-size 0.3s, color 0.3s;
    color: gray;
    border-top: 1px solid lightgray;
    border-bottom: 1px solid lightgray;
}

.custom-wheel-item.active {
    font-size: 22px;
    font-weight: bold;
    color: black;
}

.custom-wheel-item:not(.active):hover {
    color: darkgray;
}

/* Sfuma i bordi superiori e inferiori */
.custom-wheel::before,
.custom-wheel::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 40px;
    /*background: linear-gradient(to bottom, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));*/
    pointer-events: none;
}

.custom-wheel::before {
    top: 0;
}

.custom-wheel::after {
    bottom: 0;
}
</style>

<script>
// Gestione della selezione attiva
const wheel = document.querySelector('.custom-wheel');
const wheelItems = document.querySelectorAll('.custom-wheel-item');

// Funzione per selezionare l'elemento al centro della ruota
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
document.addEventListener('DOMContentLoaded', () => {
    const activeItem = document.querySelector('.custom-wheel-item.active');
    if (activeItem) {
        activeItem.scrollIntoView({
            block: 'center',
            behavior: 'smooth'
        });
    }
});

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
        const itemHeight = wheelItems[0].clientHeight; // Altezza del singolo item
        wheel.scrollTop -= itemHeight; // Altezza di scorrimento per ogni pressione della freccia
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





































































<!-- V2 -->

<div class="section" id="section-2">

    <!-- Testo di progresso -->
    <p class="progress-text my-3">Abbiamo quasi terminato, manca poco!<br>Qualche dettaglio in più...</p>

    <!-- Selezione 1: Anno di iscrizione -->
    <div class="box-campi">
        <div class="selection-group mb-4 d-flex justify-content-center align-items-center">

            <div class="label-container">
                <label for="registration_year" class="form-label">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/calendar.png" alt="Icona Istituto" style="width: 20px; height: 20px;">
                    <i class="bi bi-mortarboard"></i> <span style="height: 24px;">Mi sono iscritt* nel:</span>
                </label>
            </div>
            <div class="custom-wheel-selector">
                <div class="custom-wheel">
                    <?php
                    $currentYear = date("Y");
                    for ($year = $currentYear; $year >= $currentYear - 10; $year--) {
                        $activeClass = ($year == $currentYear) ? 'active' : '';
                        echo "<div class='custom-wheel-item $activeClass' data-year='$year'>$year</div>";
                    }
                    echo "<input type='hidden' name='registration_year' id='registration_year' value='$currentYear'>";
                    ?>
                </div>
            </div>
            <small id="campo-anno-error" class="text-danger" style="display: none;">Seleziona l'anno di iscrizione.</small>
        </div>

        <!-- Selezione 2: Lingue da studiare -->
        <div class="selection-group mb-4">
            <label for="language-study" class="form-label">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/registrazione/england-flag.png" alt="Icona Istituto" style="width: 20px; height: 20px;">
                <i class="bi bi-mortarboard"></i> <span style="height: 24px;">Quali altre lingue ti piacerebbe studiare?</span>
            </label>
            <div class="language-options d-flex flex-wrap">
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
                    $flag = get_term_meta($language->term_id, 'flag', true); // Assuming 'flag' is the meta key for the flag
                    $hiddenClass = ($index >= 4) ? 'hidden-language' : ''; // Nascondi le lingue dopo la terza
                    echo "<div class='language-option $hiddenClass' data-language='{$language->name}'>
                            <input type='checkbox' name='languages[]' value='{$language->slug}' id='lang_{$language->slug}' class='d-none'>
                            <label for='lang_{$language->slug}' class='language-label'>
                                <span class='flag'>$flag</span>
                                <span class='language-name'>{$language->name}</span>
                            </label>
                          </div>";
                }
            }
            ?>
            <div class="w-100 d-flex justify-content-center">
                <a id="show-more-languages" class="btn btn-link">Mostra altro</a>
            </div>
        </div>
        <small id="campo-lingue-extra-error" class="text-danger" style="display: none;">Seleziona almeno una voce.</small>
    </div>

    

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const showMoreButton = document.getElementById('show-more-languages');
                const hiddenLanguages = document.querySelectorAll('.hidden-language');

                showMoreButton.addEventListener('click', () => {
                    hiddenLanguages.forEach(language => language.classList.remove('hidden-language'));
                    showMoreButton.style.display = 'none'; // Nascondi il pulsante dopo aver mostrato tutte le lingue
                });
            });
        </script>

        <script>
        document.querySelectorAll('.language-option').forEach(option => {
            option.addEventListener('click', () => {
                const checkbox = option.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
                option.classList.toggle('selected', checkbox.checked);
            });
        });
        </script>


    </div>
    <button id="back2" class="btn btn-primary btn-next mt-3">Indietro</button>
    <button id="next2" class="btn btn-primary btn-next mt-3" type="button" onclick="saveProfilationData()">
        <div id="icon-loading-next2" class="btn-loader mx-2" hidden style="display: inline-block;">
            <span class="spinner-border spinner-border-sm"></span>
        </div>
        Avanti
    </button>
</div>

<style>

.label-container {
    flex: 1;
    text-align: left;
    display: flex;
    align-items: center;
}

.custom-wheel-selector {
    font-family: Arial, sans-serif;
    display: flex;
    flex: 1;
    /*flex-direction: column;*/
    align-items: center;
    justify-content: flex-start;
}

.custom-label {
    display: flex;
    align-items: center;
    font-size: 16px;
    margin-bottom: 10px;
}

.custom-label::before {
    content: "📅";
    margin-right: 8px;
}

.custom-wheel {
    height: 120px;
    /* Dimensione della ruota */
    overflow: hidden;
    /* Nasconde le parti fuori dalla ruota */
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0px;
    border-radius: 10px;
    position: relative;
    padding: 50px 0;
    /* Spazio per permettere agli elementi estremi di centrare */
    white-space: nowrap;
}

.custom-wheel-item {
    font-size: 18px;
    padding: 0px;
    padding-left: 20px;
    padding-right: 20px;
    cursor: pointer;
    text-align: center;
    transition: font-size 0.3s, color 0.3s;
    color: gray;
    border-top: 1px solid lightgray;
    border-bottom: 1px solid lightgray;
}

.custom-wheel-item.active {
    font-size: 22px;
    font-weight: bold;
    color: black;
}

.custom-wheel-item:not(.active):hover {
    color: darkgray;
}

/* Sfuma i bordi superiori e inferiori */
.custom-wheel::before,
.custom-wheel::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 40px;
    /*background: linear-gradient(to bottom, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));*/
    pointer-events: none;
}

.custom-wheel::before {
    top: 0;
}

.custom-wheel::after {
    bottom: 0;
}
</style>

<script>
// Gestione della selezione attiva
const wheel = document.querySelector('.custom-wheel');
const wheelItems = document.querySelectorAll('.custom-wheel-item');

// Funzione per selezionare l'elemento al centro della ruota
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
document.addEventListener('DOMContentLoaded', () => {
    const activeItem = document.querySelector('.custom-wheel-item.active');
    if (activeItem) {
        activeItem.scrollIntoView({
            block: 'center',
            behavior: 'smooth'
        });
    }
});

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
        const itemHeight = wheelItems[0].clientHeight; // Altezza del singolo item
        wheel.scrollTop -= itemHeight; // Altezza di scorrimento per ogni pressione della freccia
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