<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.3.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
  return;
}

?>
<div class="container mt-4">
  <div class="card shadow border-0 my-4" style="border-radius: 15px;">
    <div class="card-body">
      <h1 class="h5 custom-bold">Recensioni</h1>
      <p class="card-text">Leggi le recensioni degli altri utenti o scrivi la tua:</p>

      <!-- Elenco recensioni -->
      <div class="reviews-section mb-4">
        <?php if ( have_comments() ) : ?>
          <ol class="commentlist">
            <?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
          </ol>

          <?php
          if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
            echo '<nav class="woocommerce-pagination">';
            paginate_comments_links(
              apply_filters(
                'woocommerce_comment_pagination_args',
                array(
                  'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
                  'next_text' => is_rtl() ? '&larr;' : '&rarr;',
                  'type'      => 'list',
                )
              )
            );
            echo '</nav>';
          endif;
          ?>
        <?php else : ?>
          <p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
        <?php endif; ?>
      </div>

      <div class="write-review">
        <h1 class="h5 custom-bold">Scrivi una recensione</h1>
        <form id="reviewForm">
          <!-- Stelle per la valutazione -->
          <div class="mb-3">
            <label for="rating" class="form-label">Valutazione</label>
            <div id="rating" class="d-flex align-items-center">
              <!-- Ogni stella ha un data-value -->
              <i class="bi bi-star text-secondary fs-4 star" data-value="1" style="cursor: pointer;"></i>
              <i class="bi bi-star text-secondary fs-4 star" data-value="2" style="cursor: pointer;"></i>
              <i class="bi bi-star text-secondary fs-4 star" data-value="3" style="cursor: pointer;"></i>
              <i class="bi bi-star text-secondary fs-4 star" data-value="4" style="cursor: pointer;"></i>
              <i class="bi bi-star text-secondary fs-4 star" data-value="5" style="cursor: pointer;"></i>
            </div>
            <input type="hidden" id="ratingValue" name="ratingValue" value="0">
          </div>
          <!-- Testo della recensione -->
          <div class="mb-3">
            <label for="reviewText" class="form-label">La tua recensione</label>
            <textarea id="reviewText" class="form-control" rows="3" placeholder="Scrivi qui la tua recensione..." required></textarea>
          </div>
          <!-- Pulsante di invio -->
          <div class="d-flex justify-content-center">
            <button type="submit" class="modern-btn">Invia recensione</button>
          </div>
        </form>
      </div>

<script>
  // Intercetta il click sulle stelle
  document.querySelectorAll('.star').forEach(function (star) {
    star.addEventListener('click', function (e) {
      e.preventDefault(); // Previene l'azione di default (scrolling)
      e.stopPropagation(); // Blocca la propagazione dell'evento
      const rating = this.getAttribute('data-value'); // Valore della stella cliccata
      
      // Aggiorna il colore delle stelle
      document.querySelectorAll('.star').forEach(function (s) {
        if (s.getAttribute('data-value') <= rating) {
          s.classList.remove('text-secondary');
          s.classList.add('text-warning'); // Stella selezionata
        } else {
          s.classList.remove('text-warning');
          s.classList.add('text-secondary'); // Stella non selezionata
        }
      });

      // Aggiorna il campo nascosto con il valore selezionato
      document.getElementById('ratingValue').value = rating;
    });
  });

  // Validazione del form
  document.getElementById('reviewForm').addEventListener('submit', function (e) {
    const rating = document.getElementById('ratingValue').value;
    if (rating === '0') {
      e.preventDefault(); // Blocca l'invio se non è stata selezionata una valutazione
      alert('Per favore, seleziona una valutazione prima di inviare la recensione.');
    }
  });
</script>
</div>
</div>
</div>
<?php
