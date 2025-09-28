<?php
/**
 * Template part to display the information banner on the home page
 *
 * @package Bootscore
 * @version 6.0.0
 */

$traguardi = array(
    array(
        "numero" => "10000",
        "testo" => "Utenti attivi",
        "icon" => "users",
        "color" => "success"
    ),
    array(
        "numero" => "1500",
        "testo" => "Documenti",
        "icon" => "file-alt",
        "color" => "primary"
    ),
    array(
        "numero" => "1000",
        "testo" => "Recensioni",
        "icon" => "star",
        "color" => "warning"
    )
);

$servizi = array(
    array(
        "titolo" => "📚 Documenti di ogni tipo",
        "testo" => "Riassunti, panieri, mappe concettuali...",
        "icon" => "book"
    ),
    array(
        "titolo" => "🤖 Studia con l'AI",
        "testo" => "Fatti supportare nello studio dall'AI",
        "icon" => "robot"
    ),
    array(
        "titolo" => "👩🏼‍🏫 Accedi ai corsi",
        "testo" => "Impara con i corsi super aggiornati",
        "icon" => "graduation-cap"
    )
);

$elementi = array(
    array(
        "title" => "I nostri traguardi",
        "text" => return_template_part('template-parts/home/box-traguardi', null, array('traguardi' => $traguardi)),
        "classes" => "col-lg-3 col-md-5 mb-4 mb-md-0",
        "style" => array(
            "background-color" => "var(--white)",
            "box-shadow" => "var(--box-shadow)",
            "border-radius" => "var(--border-radius-lg)"
        )
    ),
    array(
        "title" => "I nostri servizi",
        "text" => return_template_part('template-parts/home/box-servizi', null, array('servizi' => $servizi)),
        "classes" => "col-lg-6 col-md-7 mb-4 mb-md-0",
        "style" => array(
            "background-color" => "var(--white)",
            "box-shadow" => "var(--box-shadow)",
            "border-radius" => "var(--border-radius-lg)"
        )
    ),
    array(
        "title" => "",
        "text" => "",
        "classes" => "col-lg-3 d-none d-lg-block",
        "style" => array(
            "background-image" => "url('" . get_stylesheet_directory_uri() . "/assets/img/home/immagine_traguardi_001.jpg')" /* "/assets/img/home/studenti-diplomati.webp')" */,
            "background-size" => "cover",
            "background-position" => "center",
            "box-shadow" => "var(--box-shadow)",
            "border-radius" => "var(--border-radius-lg)",
            "min-height" => "500px"
        )
    )
);
?>

<section class="info-banner">
    <div class="container">
        <div class="row">
            <?php foreach($elementi as $elemento): ?>
                <div class="<?php echo esc_attr($elemento['classes']); ?>">
                    <?php get_template_part('template-parts/home/box-informazioni', null, array('elemento' => $elemento)); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
