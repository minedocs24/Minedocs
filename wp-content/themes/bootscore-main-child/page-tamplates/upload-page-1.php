<?php

/**
 * Template Name: Upload Page 1
 *
 * 
 *
 * @package Bootscore
 * @version 6.0.0
 */
// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    wp_redirect( LOGIN_PAGE );
    return;
}
// Exit if accessed directly
defined('ABSPATH') || exit;
get_header();
?>


<?php if(!is_user_logged_in()){ ?>
    <div id="content" class="container pt-5">
        <h2>Effettua il login</h2>
        <p>Per poter caricare i tuoi documenti devi effettuare il login</p>
        <a href="<?php echo LOGIN_PAGE; ?>" class="btn btn-primary">Login</a>
    </div>

<?php } else { ?>
    
    <?php
    // Recupera l'ID dell'utente loggato
    $current_user_id = get_current_user_id();

    // Recupera il parametro `post_id` dall'URL
    $post_hid = isset($_GET['post_id']) ? sanitize_text_field($_GET['post_id']) : null;

    error_log('Post HID: ' . $post_hid); // Log del post HID


    $post_id = intval(get_product_id_by_hash($post_hid));
    error_log('Post ID: ' . $post_id); // Log del post ID

    //$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;

    // Verifica se il documento appartiene all'utente loggato
    if ($post_id) {
        $document_author_id = get_post_field('post_author', $post_id);
        if ($document_author_id != $current_user_id) {
            echo '<div id="content" class="container pt-5">';
            echo '<h2>Accesso negato</h2>';
            echo '<p>Non hai i permessi per modificare questo documento.</p>';
            echo '</div>';
            get_footer();
            exit;
        }
    } 
    ?>

    <div id="content" class="container pt-5">
        <div class="upload-page-container">
            <div class="upload-page-title">
                <h2>Carica i tuoi documenti e <span class="text-primary">guadagna</span></h2>
                <p class="text-small">in soli 3 passaggi</p>
            </div>

            <div class="upload-tabs">
                <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="pill1-tab" href="#pill1" role="tab"
                            aria-controls="pill1" aria-selected="true">Carica</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pill2-tab" href="#pill2" role="tab" aria-controls="pill2"
                            aria-selected="false">Descrivi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pill3-tab" href="#pill3" role="tab" aria-controls="pill3"
                            aria-selected="false">Fatto</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pill1" role="tabpanel" aria-labelledby="pill1-tab">
                    <?php if ($post_id): ?>
                        <div id="buttonSection" class="button-section">
                            <div class="my-3">
                                <div class="d-flex flex-column flex-md-row justify-content-around my-3">
                                    <div class="modify-card flex-grow-1 mx-2 mx-md-5 mb-3 mb-md-0" data-plan="trimestrale" id="plan-trimestrale">
                                        <p class="h5"><strong><span class="text-pantone-arancione">📄 Carica</span> una nuova versione</strong></p>
                                        <p class="text-small"><small>Caricando una nuova versione, gli utenti vedranno il documento aggiornato.</small></p>
                                        <button class="button-custom button-custom-blue" onclick="show_section1(); setUploadChoice('uploadNewVersion'); check_fields()">Carica nuovo file</button>
                                    </div>

                                    <div class="modify-card flex-grow-1 mx-2 mx-md-5 selected" data-plan="annuale" id="plan-annuale">
                                        <p class="h5"><strong><span class="text-primary">📝 Modifica i dati</span> del documento</strong></p>
                                        <p class="text-small"><small>Modifica i dati del documento esistente senza caricare una versione aggiornata.</small></p>
                                        <button class="button-custom button-custom-blue" onclick="go_to_section2(); setUploadChoice('modify'); check_fields()">Modifica i dati</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Il template è nascosto inizialmente -->
                        <div id="uploadTemplate" class="upload-template" style="display: none;">
                            <?php get_template_part( 'template-parts/upload/upload-pill1' ); ?>
                        </div>
                    <?php else: ?>
                        <?php get_template_part( 'template-parts/upload/upload-pill1' ); ?>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="pill2" role="tabpanel" aria-labelledby="pill2-tab">
                    <?php get_template_part( 'template-parts/upload/upload-pill2' ); ?>
                </div>
                <div class="tab-pane fade" id="pill3" role="tabpanel" aria-labelledby="pill3-tab">
                    <?php get_template_part( 'template-parts/upload/upload-pill3' ); ?>
                </div>
            </div>
        </div>
    </div>
<?php
}
get_footer();