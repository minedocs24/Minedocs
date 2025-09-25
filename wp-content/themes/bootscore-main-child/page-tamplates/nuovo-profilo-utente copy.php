<?php
/*
Template Name: Nuovo Profilo Utente
*/

get_header(); 

/*
// Ensure Bootstrap CSS is included
wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');

// Ensure jQuery is included
wp_enqueue_script('jquery');

// Ensure Bootstrap JS is included
wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', array('jquery'), null, true);
?>


<?php */
$class_padding_admin = current_user_can('administrator') ? 'pt-5' : '';
?>

<?php
$menu_data = array(
    array(
        'sezione' => 'Generale',
        'mostra_nome_sezione' => false,
        'voci' => array(
            'carica_documento' => array(
                'nome' => 'Movimenti',
                'tipo' => 'template_part',
                'contenuto' => 'template-parts/nuovo-profilo-utente/sezione-generale',
                'attivo' => true
            ),
            'amplia_la_tua_raccolta' => array(
                'nome' => 'Amplia la tua raccolta',
                'tipo' => 'template_part',
                'contenuto' => 'template-parts/profilo-utente/btn-amplia-raccolta',
                'attivo' => false
            ),
        )
    ),
    array(
         'sezione' => 'Esplora',
         'mostra_nome_sezione' => true,
         'voci' => array(
            'il_mio_profilo' => array(
                'nome' => 'Il mio profilo',
                'slug' => 'il-mio-profilo',
                'pagina' => 'il-mio-profilo',
                'icona' => 'fas fa-user',
                'tipo' => 'contenuto',
                'contenuto' => 'template-parts/profilo-utente/sezione-utente',
                'url' => '#section-profilo',
                'attivo' => true,
                'default' => true,
                
            ),
            'il_mio_studio' => array(
                'nome' => 'Il mio studio',
                'slug' => 'il-mio-studio',
                'pagina' => 'il-mio-profilo',
                'icona' => 'fas fa-book-open',
                'tipo' => 'contenuto',
                'contenuto' => 'template-parts/profilo-utente/sezione-utente',
                'url' => '#sezione-miei-documenti',
                'attivo' => true
            ),
            'documenti_caricati' => array(
                'nome' => 'Documenti caricati',
                'slug' => 'documenti-caricati',
                'pagina' => 'documenti-caricati',
                'icona' => 'fas fa-file-upload',
                'tipo' => 'contenuto',
                'contenuto' => 'template-parts/profilo-utente/sezione-documenti-caricati',
                'attivo' => true
            ),
            'i_miei_guadagni' => array(
                'nome' => 'I miei guadagni',
                'slug' => 'i-miei-guadagni',
                'pagina' => 'i-miei-guadagni',
                'icona' => 'fas fa-coins',
                'tipo' => 'contenuto',
                'contenuto' => 'template-parts/profilo-utente/sezione-guadagni',
                'attivo' => true
            ),
            'i_miei_movimenti' => array(
                'nome' => 'I miei movimenti',
                'slug' => 'i-miei-movimenti',
                'pagina' => 'i-miei-movimenti',
                'icona' => 'fas fa-exchange-alt',
                'tipo' => 'contenuto',
                'contenuto' => 'template-parts/profilo-utente/sezione-movimenti',
                'attivo' => true
            ),
            'ricarica_punti' => array(
                'nome' => 'Ricarica punti',
                'slug' => 'ricarica-punti',
                'icona' => 'fas fa-dollar-sign',
                'tipo' => 'link_esterno',
                'url' => "/wp1/compra-pacchetti-punti",
                'attivo' => true
            ),
            'impostazioni' => array(
                'nome' => 'Impostazioni',
                'slug' => 'impostazioni',
                'pagina' => 'impostazioni',
                'icona' => 'fas fa-cog',
                'tipo' => 'contenuto',
                'contenuto' => 'template-parts/profilo-utente/sezione-impostazioni',
                'attivo' => true
            ),
         )
        ),
    array(
        'sezione' => 'Raccolta',
        'mostra_nome_sezione' => true,
        'voci' => array(
            'il_mio_piano_di_studio' => array(
                'nome' => 'Il mio piano di studio',
                'slug' => 'il-mio-piano-di-studio',
                'icona' => 'fas fa-folder',
                'tipo' => 'link_esterno',
                'url' => '#',
                'attivo' => true
            ),
            'i_miei_documenti' => array(
                'nome' => 'I miei documenti',
                'slug' => 'i-miei-documenti',
                'icona' => 'fas fa-folder',
                'tipo' => 'link_esterno',
                'url' => '#',
                'attivo' => true
            ),
            'quiz_di_esercitazione' => array(
                'nome' => 'Quiz di esercitazione',
                'slug' => 'quiz-di-esercitazione',
                'icona' => 'fas fa-folder',
                'tipo' => 'link_esterno',
                'url' => '#',
                'attivo' => false
            ),
            'i_miei_libri' => array(
                'nome' => 'I miei libri',
                'slug' => 'i-miei-libri',
                'icona' => 'fas fa-folder',
                'tipo' => 'link_esterno',
                'url' => '#',
                'attivo' => false
            ),
        )
    )
)


?>
<div class="  <?php echo $class_padding_admin; ?>">
    <div class="row">
        <div class="col-md-4 col-lg-3">
            <button class="navbar-toggler d-md-none" type="button" data-toggle="collapse" data-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse d-md-block" id="navbarMenu">
                <?php get_template_part('template-parts/nuovo-profilo-utente/sidebar', null, ['menu_data' => $menu_data]); ?>
            </div>
        </div>
        <div class="col-md-8 col-lg-9">
            <div class="loading-section">

            </div>

            <div class="content">
                <?php foreach ($menu_data as $sezione) : ?>
                    <?php foreach ($sezione['voci'] as $voce_key => $voce) : ?>
                        <?php if (!empty($voce['default'])) : ?>
                            <div id="section-<?php echo esc_attr($voce_key); ?>" class="section-content">
                                <?php get_template_part($voce['contenuto']); ?>
                            </div>
                        <?php else : ?>
                            <?php if (isset($voce['attivo']) && $voce['attivo'] && isset($voce['tipo']) && $voce['tipo'] === 'contenuto') { ?>
                                <div id="section-<?php echo esc_attr($voce_key); ?>" class="section-content" style="display:block;"></div>
                            <?php } ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>



        </div>
    </div>
</div>


<?php get_footer(); ?>

<?php
add_action('rest_api_init', function () {
    register_rest_route('mytheme', '/load-section', array(
        'methods' => 'GET',
        'callback' => function ($data) {
            $section = sanitize_text_field($data['section']);
            global $menu_data;

            foreach ($menu_data as $sezione) {
                if (isset($sezione['voci'][$section])) {
                    ob_start();
                    get_template_part($sezione['voci'][$section]['contenuto']);
                    return ob_get_clean();
                }
            }

            return new WP_Error('section_not_found', 'Section not found', array('status' => 404));
        }
    ));
});
?>

<script>

/*

// Example usage:
load_template_part(
    'template-parts/profilo-utente/sezione-movimenti',
    '.content-section',
    function() { //console.log('Loading...'); },
    function(response) { //console.log('Success:', response); },
    function(xhr, status, error) { //console.log('Error:', status, error); }
);
*/

document.addEventListener('DOMContentLoaded', function () {
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function () {
            const sectionKey = this.dataset.section;
            const allSections = document.querySelectorAll('.section-content');
            allSections.forEach(section => section.style.display = 'none');

            const targetSection = document.querySelector(`#section-${sectionKey}`);
            if (targetSection.innerHTML.trim() === '') {
                // Carica il contenuto asincronicamente
                fetch(`/wp-json/mytheme/load-section?section=${sectionKey}`)
                    .then(response => response.text())
                    .then(html => {
                        targetSection.innerHTML = html;
                        targetSection.style.display = 'block';
                    });
            } else {
                targetSection.style.display = 'block';
            }

            // Aggiorna lo stato del menu
            menuItems.forEach(menu => menu.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Carica le sezioni asincrone dopo il caricamento della pagina
    const allInactiveSections = document.querySelectorAll('.section-content:empty');
    allInactiveSections.forEach(section => {
        const sectionKey = section.id.replace('section-', '');
        fetch(`/wp-json/mytheme/load-section?section=${sectionKey}`)
            .then(response => response.text())
            .then(html => {
                section.innerHTML = html;
            });
    });
});


function carica_contenuto_profilo(dati_voce) {

    const sezione_contenuto = document.querySelector('.content-section');
    if (sezione_contenuto.getAttribute('data-current-content') === dati_voce.contenuto) {
        return;
    }

    pulisci_contenuto();


        load_template_part(
            dati_voce.contenuto,
            '.content-section',
            function() { //console.log('Loading...'); 
                mostra_loading();
            },
            function(response) { 
                nascondi_loading();
                //console.log('Success:', response); 
                sezione_contenuto.setAttribute('data-current-content', dati_voce.contenuto);
            },
            function(xhr, status, error) { //console.log('Error:', status, error); }
        );

}

function pulisci_contenuto() {

    const sezione_contenuto = document.querySelector('.content-section');
    sezione_contenuto.style.transition = 'transform 0.5s ease-out, opacity 0.5s ease-out';
    sezione_contenuto.style.transform = 'scale(0)';
    sezione_contenuto.style.opacity = '0';
    setTimeout(function() {
        sezione_contenuto.innerHTML = '';
        sezione_contenuto.style.transform = 'scale(1)';
        sezione_contenuto.style.opacity = '1';
    }, 200);
}

/*function mostra_loading() {
    const loadingSection = document.querySelector('.loading-section');
    loadingSection.innerHTML = '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>';
    loadingSection.style.display = 'block';
}

function nascondi_loading() {
    const loadingSection = document.querySelector('.loading-section');
    loadingSection.style.display = 'none';
    loadingSection.innerHTML = '';
}*/
function mostra_loading() {
    const loadingSection = document.querySelector('.loading-section');
    loadingSection.innerHTML = `
        <div class="loading-wrapper text-center">
            <div class="loading-logo mb-3">
                <img src="https://via.placeholder.com/150" alt="Logo" class="img-fluid rounded-circle shadow-sm">
            </div>
            <h3 class="mb-3 text-primary">Minedocs</h3>
            <div class="stars">
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
            </div>
            <p class="mt-3 text-muted">Please wait while we load your content...</p>
        </div>`;
    loadingSection.style.display = 'flex';
    loadingSection.style.justifyContent = 'center';
    loadingSection.style.alignItems = 'center';
    loadingSection.style.height = '100vh';
    loadingSection.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
}

const style = document.createElement('style');
style.innerHTML = `
    .stars {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .star {
        width: 20px;
        height: 20px;
        margin: 0 5px;
        background-color: gold;
        clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
        animation: twinkle 1s infinite;
    }
    .star:nth-child(2) {
        animation-delay: 0.2s;
    }
    .star:nth-child(3) {
        animation-delay: 0.4s;
    }
    @keyframes twinkle {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.5); opacity: 0.5; }
    }
`;
document.head.appendChild(style);

function nascondi_loading() {
    const loadingSection = document.querySelector('.loading-section');
    loadingSection.style.display = 'none';
    loadingSection.innerHTML = '';
}

</script>



<style>
.navbar-toggler-icon {
    display: inline-block;
    width: 1.5em;
    height: 1.5em;
    vertical-align: middle;
    content: "";
    background: no-repeat center center;
    background-size: 100% 100%;
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%280, 0, 0, 0.5%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}
</style>
