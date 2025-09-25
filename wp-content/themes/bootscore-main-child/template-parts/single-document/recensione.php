<?php
/**
 * Renderizza una singola recensione.
 *
 * @param string $nome Nome dell'autore della recensione.
 * @param string $avatar URL dell'immagine dell'avatar.
 * @param int $stelle Numero di stelle (da 1 a 5).
 * @param string $recensione Testo della recensione.
 */

function renderRecensione($nome, $avatar, $stelle, $recensione) {
    ?>
    <div class="review">
        <div class="header_review">
            <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar di <?= htmlspecialchars($nome) ?>" class="avatar">
            <h3 class="review_author"><?= htmlspecialchars($nome) ?></h3>
        </div>
        <div class="content_review">
            <p class="stars_review"><?= str_repeat('⭐', $stelle) . str_repeat('☆', 5 - $stelle) ?></p>
            <p class="review_text"><?= htmlspecialchars($recensione) ?></p>
            <hr class="divider">
        </div>
    </div>
    <?php
}
?>


<style>
.review {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 10px;
    margin-bottom: 10px;
    max-width: 400px;
}

.header_review {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    width: 100%;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.review_author {
    margin: 0;
    font-size: 1em;
    color: #333;
    flex-grow: 1;
    text-align: left;
}

.stars_review {
    margin: 4px 0;
    color: #f4c150; /* Colore stelle piene */
    font-size: 1em;
}

.content_review {
    width: 100%;
}

.review_text {
    margin: 8px 0;
    color: #555;
}

.divider {
    border: none;
    border-top: 1px solid rgba(128, 128, 128, 0.5);
    margin: 8px 0;
    width: 100%;
}
</style>
