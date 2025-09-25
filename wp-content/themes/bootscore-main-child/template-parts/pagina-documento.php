<?php
/*
Template Name: Pagina documento con PDF viewer
*/


global $product;
global $post;
global $sistemiPunti;

$term_istituto = wp_get_object_terms($post->ID, 'nome_istituto')[0] ?? (object) ['name' => 'N/A'];
$term_tipo_istituto = wp_get_object_terms($post->ID, 'tipo_istituto')[0] ?? (object) ['name' => 'N/A'];
$term_anno = wp_get_object_terms($post->ID, 'anno_accademico')[0] ?? (object) ['name' => 'N/A'];
$term_corso = wp_get_object_terms($post->ID, 'nome_corso')[0] ?? (object) ['name' => 'N/A'];
$term_corso_laurea = wp_get_object_terms($post->ID, 'nome_corso_di_laurea')[0] ?? (object) ['name' => 'N/A'];
$numero_download = get_product_purchase_count($post->ID) ?? 0;
$post_status = get_post_status($post->ID);
$post_product_status = get_stato_prodotto($post->ID);
$post_approval_status = get_stato_approvazione_prodotto($post->ID);
$gia_acquistato = user_has_purchased_product(get_current_user_id(  ), $post->ID);
$stato_approvazione = get_post_meta($post->ID, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true);

$modalita = wp_get_object_terms($post->ID, 'modalita_pubblicazione')[0];
$tipo_punti = "";
$icona_punti = "";
if ($modalita->slug == 'vendi') {
    $costo_punti = get_post_meta($post->ID,'_costo_in_punti_pro', true);
    $tipo_punti = "Punti Pro";
    $icona_punti = basename($sistemiPunti['pro']->get_icon());
} else if ($modalita->slug == 'condividi') {
    $costo_punti = get_post_meta($post->ID,'_costo_in_punti_blu', true);
    $tipo_punti = "Punti Blu";
    $icona_punti = basename($sistemiPunti['blu']->get_icon());
}
$n_pagine = get_post_meta($post->ID,'_num_pagine', true);
$data_caricamento = get_the_date('d/m/Y', $post->ID);
$tempo_passato = human_time_diff( get_the_time('U', $post->ID), current_time('timestamp') ) . ' fa';

//$media_recensioni = $product->get_average_rating();
$info_recensioni = get_product_review_info($post->ID);
$media_recensioni = $info_recensioni['average_rating'];
$n_recensioni = $info_recensioni['total_reviews'];
if ($media_recensioni == 0 or $media_recensioni == null) {
    $media_recensioni = "Nessuna recensione";
} else {
    $media_recensioni = number_format($media_recensioni, 1, '.', '');
}



// $features = array (
//     array('icon' => 'istituto.svg', 'text' => $term_istituto->name , 'link' => get_term_link( $term_istituto , 'nome_istituto' ) ),
//     array('icon' => 'calendario.svg', 'text' => $term_anno->name , 'link' => get_term_link( $term_anno , 'anno_accademico' )),
//     array('icon' => 'libro.svg', 'text' => $term_corso->name , 'link' => get_term_link( $term_corso , 'anno_accademico' )),
//     array('icon' => 'libro.svg', 'text' => $term_corso_laurea->name , 'link' => get_term_link( $term_corso_laurea , 'anno_accademico' )),
//     array('icon' => 'penna.svg', 'text' => $n_pagine.' pagine'),
//     array('icon' => $icona_punti, 'text' => $costo_punti.' '.$tipo_punti),
//     array('icon' => 'stella.svg', 'text' => $media_recensioni),
//     array('icon' => 'download.svg', 'text' => $numero_download.' Download'),
//     array('icon' => 'time-plus.svg', 'text' => $data_caricamento . ' ('.$tempo_passato.')' )
// );
$features = array (
    array('icon' => 'university', 'text' => $term_istituto->name, 'link' => get_term_link($term_istituto, 'nome_istituto'), 'color' => 'primary'),
    array('icon' => 'calendar-alt', 'text' => $term_anno->name, 'link' => get_term_link($term_anno, 'anno_accademico'), 'color' => 'info'),
    array('icon' => 'book', 'text' => $term_corso->name, 'link' => get_term_link($term_corso, 'nome_corso'), 'color' => 'success'),
    array('icon' => 'graduation-cap', 'text' => $term_corso_laurea->name, 'link' => get_term_link($term_corso_laurea, 'nome_corso_di_laurea'), 'color' => 'primary'),
    array('icon' => 'file-alt', 'text' => $n_pagine.' pagine', 'color' => 'secondary'),
    array('icon' => 'coins', 'text' => $costo_punti.' '.$tipo_punti, 'color' => 'warning'),
    array('icon' => 'star', 'text' => $media_recensioni, 'color' => 'warning'),
    array('icon' => 'download', 'text' => $numero_download.' Download', 'color' => 'primary'),
    array('icon' => 'clock', 'text' => $data_caricamento . ' ('.$tempo_passato.')', 'color' => 'info')
);
?>

<div id="content" class="container-fluid px-4" style="margin-top: -25px;">

    <?php
    $message = "";

    $combinations = [
        'draft|eliminato_utente|non_impostato' => "Questo documento è stato eliminato dall'utente e non ha uno stato impostato.",
        'draft|eliminato_utente|in_approvazione' => "Questo documento è stato eliminato dall'utente e si trova in attesa di approvazione.",
        'draft|eliminato_utente|approvato' => "Questo documento è stato eliminato dall'utente ma era stato approvato dagli amministratori.",
        'draft|eliminato_utente|non_approvato' => "Questo documento è stato eliminato dall'utente e non è stato approvato dagli amministratori.",
        'draft|nascosto_aggiornamento|non_impostato' => "È stata pubblicata una nuova versione di questo documento, ma non ha uno stato impostato.",
        'draft|nascosto_aggiornamento|in_approvazione' => "È stata pubblicata una nuova versione di questo documento, che si trova in attesa di approvazione.",
        'draft|nascosto_aggiornamento|approvato' => "È stata pubblicata una nuova versione di questo documento, che è stata approvata.",
        'draft|nascosto_aggiornamento|non_approvato' => "È stata pubblicata una nuova versione di questo documento, che non è stata approvata.",
        'draft|pubblicato|non_impostato' => "Questo documento non è pubblicato e lo stato di approvazione non è definito. Contatta un admin.",
        'draft|pubblicato|in_approvazione' => "Questo documento non è stato pubblicato e si trova in attesa di approvazione.",
        'draft|pubblicato|approvato' => "Questo documento non è in stato pubblicato ma è stato approvato dagli amministratori. Contatta un admin per pubblicarlo.",
        'draft|pubblicato|non_approvato' => "Questo documento non è in stato pubblicato ma non è stato approvato dagli amministratori. Contatta un admin per pubblicarlo.",
        'draft|eliminato_admin|non_impostato' => "Questo documento è stato eliminato dagli amministratori e non ha uno stato impostato.",
        'draft|eliminato_admin|in_approvazione' => "Questo documento è stato eliminato dagli amministratori e si trova in attesa di approvazione.",
        'draft|eliminato_admin|approvato' => "Questo documento è stato eliminato dagli amministratori ma era stato approvato.",
        'draft|eliminato_admin|non_approvato' => "Questo documento è stato eliminato dagli amministratori e non è stato approvato.",
        'draft|eliminato_cancellazione_utente|non_impostato' => "Questo documento è stato eliminato dall'utente per cancellazione e non ha uno stato impostato.",
        'draft|eliminato_cancellazione_utente|in_approvazione' => "Questo documento è stato eliminato dall'utente per cancellazione e si trova in attesa di approvazione.",
        'draft|eliminato_cancellazione_utente|approvato' => "Questo documento è stato eliminato dall'utente per cancellazione ma era stato approvato.",
        'draft|eliminato_cancellazione_utente|non_approvato' => "Questo documento è stato eliminato dall'utente per cancellazione e non è stato approvato.",
        'publish|eliminato_utente|non_impostato' => "", // "Questo documento è stato eliminato dall'utente e non ha uno stato impostato.",
        'publish|eliminato_utente|in_approvazione' => "", //"Questo documento è stato eliminato dall'utente e si trova in attesa di approvazione.",
        'publish|eliminato_utente|approvato' => "", //"Questo documento è stato eliminato dall'utente ma era stato approvato dagli amministratori.",
        'publish|eliminato_utente|non_approvato' => "", //"Questo documento è stato eliminato dall'utente e non è stato approvato dagli amministratori.",
        'publish|nascosto_aggiornamento|non_impostato' => "È stata pubblicata una nuova versione di questo documento.",
        'publish|nascosto_aggiornamento|in_approvazione' => "È stata pubblicata una nuova versione di questo documento.",
        'publish|nascosto_aggiornamento|approvato' => "È stata pubblicata una nuova versione di questo documento.",
        'publish|nascosto_aggiornamento|non_approvato' => "È stata pubblicata una nuova versione di questo documento.",
        'publish|pubblicato|non_impostato' => "", //"Questo documento è stato pubblicato ma non ha uno stato impostato.",
        'publish|pubblicato|in_approvazione' => "", //"Questo documento è stato pubblicato e si trova in attesa di approvazione.",
        'publish|pubblicato|approvato' => "", //"Questo documento è stato pubblicato ed è stato approvato dagli amministratori.",
        'publish|pubblicato|non_approvato' => "", //"Questo documento è stato pubblicato ma non è stato approvato dagli amministratori.",
        'publish|eliminato_admin|non_impostato' => "", //"Questo documento è stato eliminato dagli amministratori e non ha uno stato impostato.",
        'publish|eliminato_admin|in_approvazione' => "", //"Questo documento è stato eliminato dagli amministratori e si trova in attesa di approvazione.",
        'publish|eliminato_admin|approvato' => "Questo documento è stato eliminato dagli amministratori ma era stato approvato.",
        'publish|eliminato_admin|non_approvato' => "", //"Questo documento è stato eliminato dagli amministratori e non è stato approvato.",
        'publish|eliminato_cancellazione_utente|non_impostato' => "", //"Questo documento è stato eliminato dall'utente per cancellazione e non ha uno stato impostato.",
        'publish|eliminato_cancellazione_utente|in_approvazione' => "", //"Questo documento è stato eliminato dall'utente per cancellazione e si trova in attesa di approvazione.",
        'publish|eliminato_cancellazione_utente|approvato' => "", //"Questo documento è stato eliminato dall'utente per cancellazione ma era stato approvato.",
        'publish|eliminato_cancellazione_utente|non_approvato' =>"" // "Questo documento è stato eliminato dall'utente per cancellazione e non è stato approvato."
        // Add more combinations as needed
    ];

    $message = "";

    foreach ($combinations as $combination => $msg) {
        $combination = explode('|', $combination);
        if ($post_status === $combination[0] && $post_product_status === $combination[1] && $post_approval_status === $combination[2]) {
            $message = $msg;
            break;
        }
    }

    if (!empty($message)) {
        ?>
    <div class="alert alert-warning" role="alert">
        <?php echo $message; ?>
    </div>
    <?php
    }
?>

    <div class="row">
        <div id="caratteristiche" class="col-md-4 position-relative">

            <?php get_template_part("template-parts/single-document/actions", null, 
                    array('costo' => array(
                                    'icon' => $icona_punti, 
                                    'text' => $costo_punti.' '.$tipo_punti
                                    ),
                        'gia_acquistato' => $gia_acquistato, 
                    'stato_approvazione' => $stato_approvazione,),            
                    ); ?>

            <div class="mb-3 mt-5">
                <h1 class="h5 custom-bold"><?php echo $product->get_name(); ?></h1>
            </div>

            <hr class="border-dark">

            <?php if ($modalita->slug == 'vendi' || $modalita->slug == 'condividi'): ?>
            <input id="tipo_documento" type="hidden" name="tipo_punti"
                value="<?php echo $modalita->slug == 'vendi' ? 'pro' : 'blu'; ?>">
            <?php endif; ?>

            <div class="mt-3 mb-5">
                <p class="product-description"><?php echo $product->get_description(); ?></p>
            </div>

            <?php get_template_part("template-parts/single-document/features", null, array('features'=>$features)); ?>



            <?php get_template_part("template-parts/single-document/author", null, array('user_id'=>$post->post_author)); ?>

            <?php get_template_part("template-parts/single-document/reviews", null, array('product'=>$product)); ?>
            <!-- <?php //wc_get_template_part("single-product-reviews"); ?> -->

        </div>
        <div id="anteprima" class="col-md-8" style="position: fixed; right: 0;">

            <?php get_template_part("template-parts/single-document/pdf-viewer2", null, array('anteprima_att_id'=>get_post_meta($post->ID, '_file_anteprima', true))); ?>
            <div style="position: absolute; top: 75px; left: 0px;" class="d-none d-md-block">
                <button id="btn-expand" class="btn-expand btn" style="z-index: 0;">
                    <img class="info-burger"
                        src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/burger.png"
                        alt="Precedente" width="16" height="16" />
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {

    $('#btn-expand').click(function() {
        $('#anteprima').css('transition', 'width 0.5s ease');
        $('#caratteristiche').css('transition', 'width 0.5s ease');

        $('#anteprima').toggleClass('col-md-8 col-md-12');
    });

    function adjustPdfViewerPosition() {
        if ($(window).width() > 992) {
            $('#anteprima').css('position', 'fixed');
        } else {
            $('#anteprima').css('position', 'static');
        }
    }

    adjustPdfViewerPosition();

    $(window).resize(function() {
        adjustPdfViewerPosition();
    });
});
</script>

<style>
.info-burger {
    margin-right: 15px;
    /* Aggiungi margine a destra */
    cursor: pointer;
}

#content {
    background-color: #F7F7F7;
}


.product-description {
    font-size: 17px;
}


/* Blocco selezione testo e download */
.pdf-viewer {
    pointer-events: none;
}

.pdf-container {
    /*position: relative;
    /* Necessario per posizionare il popup all'interno di questa sezione */
    overflow-x: auto;
    /* Aggiunge una scroll bar orizzontale se necessaria */

    direction: rtl;
    /* Inverte la direzione del contenuto */
}



object {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    pointer-events: none;
}

.popup-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1002; /* Per sovrapporre il popup al PDF */
    padding: 20px;
    text-align: center;
    font-family: Arial, sans-serif;
    pointer-events: none;
    /* Rende il popup trasparente agli eventi di input e quindi non bloccare le interazioni con il pdf*/
}

.popup-content h2 {
    font-size: 24px;
    margin-bottom: 10px;
}

.popup-content p {
    font-size: 16px;
    margin-bottom: 15px;
}

.popup-button {
    background-color: #007BFF;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    pointer-events: auto;
    /* Il bottone può ricevere input */
    text-decoration: none;
}

.popup-button:hover {
    background-color: #0056b3;
}

.pro-text {
    color: #FF6F00;
}

.rocket-icon {
    width: 50px;
    margin-bottom: 10px;
    transform: rotate(35deg);
    transition: transform 0.5s ease;
}


.btn-custom {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: none;
    width: auto;
    max-width: 100%;
    background-color: rgb(0, 168, 255) !important;
    font-weight: 700;
}

.btn-custom:hover {
    background-color: white !important;
    color: black !important;
    border: 1px solid black;
}


.btn-expand {
    position: absolute;
    background-color: rgb(200, 200, 200);
    transform: translateY(-50%);
    z-index: 9999;
    border: 0px solid;
    border-radius: 10px;
    padding: 10px;
    font-size: 20px;
    cursor: pointer;
    color: black !important;
}

.btn-expand:hover {
    background-color: rgb(223, 223, 223);
}

.custom-bold {
    font-weight: bold;
}
</style>

<style>
/* Stili esistenti */
.review-item {
    padding: 15px;
    border-radius: 10px;
    background: #f9f9f9;
    margin-bottom: 10px;
}

.review-header img {
    width: 50px;
    height: 50px;
    object-fit: cover;
}

.review-rating {
    color: #f39c12;
    font-size: 18px;
}

.review-text {
    margin-top: 10px;
    font-size: 16px;
}



/* Contenitore del sistema di rating */
.rating-box {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    align-items: center;
    font-size: 2rem;
    /* Dimensione delle stelle */
    gap: 0.5rem;
    /* Spazio tra le stelle */
}

/* Nascondi gli input radio */
.rating-box input[type="radio"] {
    display: none;
}

/* Stile base delle stelle */
.rating-box label {
    color: #ccc;
    /* Colore stelle inattive */
    cursor: pointer;
    transition: color 0.3s;
}

/* Cambia il colore delle stelle al passaggio del mouse (solo se non readonly) */
.rating-box:not(.readonly) label:hover,
.rating-box:not(.readonly) label:hover~label {
    color: #ffcc00;
    /* Colore stelle al passaggio del mouse */
}

/* Cambia il colore delle stelle selezionate */
.rating-box input[type="radio"]:checked~label {
    color: #ffcc00;
    /* Colore stelle selezionate */
}

/* Gestione della classe readonly per evitare modifiche */
.rating-box.readonly label {
    cursor: default;
    pointer-events: none;
    /* Disabilita interazioni */
}

/* Assicura che le stelle selezionate siano sempre visibili anche in readonly */
.rating-box.readonly input[type="radio"]:checked~label {
    color: #ffcc00;
    /* Colore stelle selezionate */
}









/* Stile per la recensione dell'utente attuale */
.current-user-review {

    position: relative;
}

.current-user-label {
    position: absolute;
    top: -15px;
    right: 15px;
    padding: 4px 10px;
    border-radius: 5px;
    font-size: 14px;
}

.current-user-review-bad {
    border: 3px solid #dc3545;
    /* Colore del bordo */
}

.current-user-label-bad {
    background: #dc3545;
    color: #fff;
}

.current-user-label-neutral {
    background: #ffc107;
    color: #000;
}

.current-user-review-neutral {
    border: 3px solid #ffc107;
    /* Colore del bordo */
}

.current-user-review-good {
    border: 3px solid #28a745;
    /* Colore del bordo */
}

.current-user-label-good {
    background: #28a745;
    color: #fff;
}

/* Media queries per schermi piccoli */
@media (max-width: 1024px) {
    .review-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .rating-box {
        font-size: 1.5rem;
        /* Riduci la dimensione delle stelle */
        gap: 0.3rem;
        /* Riduci lo spazio tra le stelle */
        margin-top: 10px;
        /* Aggiungi spazio sopra le stelle */
    }
}

@media (max-width: 768px) {
    .rating-box {
        font-size: 1.2rem;
        /* Riduci ulteriormente la dimensione delle stelle */
        gap: 0.2rem;
        /* Riduci ulteriormente lo spazio tra le stelle */
    }
}

@media (max-width: 480px) {
    .rating-box {
        font-size: 1.2rem;
        /* Riduci ulteriormente la dimensione delle stelle */
        gap: 0.2rem;
        /* Riduci ulteriormente lo spazio tra le stelle */
    }
}
</style>