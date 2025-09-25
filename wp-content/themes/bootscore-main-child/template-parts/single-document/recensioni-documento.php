<?php
// Include il file con la funzione `renderRecensione`
require_once 'recensione.php';

// Ottieni le recensioni dall'array passato
$recensioni = $args['recensioni'];
?>

<div class="row striscia-recensioni">
    <div class="col-12">
        <div class="carousel__wrapper">
            <?php
            $counter = 0; // Contatore per gestire la visibilità
            foreach ($recensioni as $recensione) {
                // Trasforma l'oggetto WP_Comment in dati compatibili con `renderRecensione`
                $nome = get_user_by('email', $recensione->comment_author_email)->nickname;  //$recensione->comment_author_email;
                $avatar = get_avatar_url($recensione->comment_author_email); // Ottiene l'URL dell'avatar
                $contenuto = $recensione->comment_content;

                // Recupera il numero di stelle usando `get_comment_meta`
                $stelle = get_comment_meta($recensione->comment_ID, 'rating', true);
                $stelle = is_numeric($stelle) ? (int)$stelle : 5;

                // Aggiungi una classe `hidden-review` alle recensioni successive alla prima
                $class = $counter > 0 ? 'hidden-review' : '';
                ?>
                <div class="carousel__slide <?= $class ?>">
                    <?php
                    // Usa la funzione `renderRecensione` per visualizzare i dati
                    renderRecensione($nome, $avatar, $stelle, $contenuto);
                    ?>
                </div>
                <?php
                $counter++;
            }
            ?>
        </div>

        <?php if (count($recensioni) > 1): ?>
            <div class="read-more-wrapper">
                <span class="read-more-text">Leggi di più</span>
            </div>
        <?php endif; ?>
    </div>
</div>


<style>
.hidden-review {
    max-height: 0; /* Altezza iniziale */
    opacity: 0; /* Trasparente inizialmente */
    overflow: hidden; /* Nasconde il contenuto */
    transition: max-height 0.5s ease, opacity 0.5s ease; /* Animazione fluida */
}

.hidden-review.show {
    max-height: 500px; /* Altezza massima per permettere l'espansione */
    opacity: 1; /* Pieno colore */
}

.read-more-wrapper {
    text-align: center;
    margin-top: 16px;
}

.read-more-text {
    color: #007bff;
    cursor: pointer;
    font-size: 1em;
    text-decoration: underline;
}

.read-more-text:hover {
    color: #0056b3;
    text-decoration: none;
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const readMoreText = document.querySelector('.read-more-text');
    const hiddenReviews = document.querySelectorAll('.hidden-review');

    if (readMoreText) {
        readMoreText.addEventListener('click', function () {
            hiddenReviews.forEach(review => {
                // Aggiunge la classe "show" per attivare la transizione
                review.classList.add('show');
            });

            // Nasconde la scritta "Leggi di più"
            readMoreText.style.display = 'none';
        });
    }
});
</script>

