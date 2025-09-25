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
<div class="mt-4">
    <div class="card shadow border-0 my-4 rounded-lg no-hover" style="border-radius: 15px;">
        <div class="card-body">
            <h3 class="h4 custom-bold">Recensioni</h3>

            <?php
            $current_user = wp_get_current_user();
            $has_reviewed = false;
            $comments = get_comments(array('post_id' => $product->get_id()));

            foreach ($comments as $comment) {
                if ($comment->user_id == $current_user->ID) {
                    $has_reviewed = true;
                    break;
                }
            }

            $can_review = is_user_logged_in() && !$has_reviewed && wc_customer_bought_product('', $current_user->ID, $product->get_id()) && get_post($product->get_id())->post_author != $current_user->ID;

            $reason = '';

            if (!$can_review) {
                if ($has_reviewed) {
                    $reason = 'Hai già scritto una recensione per questo documento.';
                } elseif (get_post($product->get_id())->post_author == $current_user->ID) {
                    $reason = 'Non puoi scrivere una recensione per un tuo documento.';
                } elseif (!wc_customer_bought_product('', $current_user->ID, $product->get_id())) {
                    $reason = 'Devi acquistare questo documento per poter scrivere una recensione.';
                } else {
                    $reason = 'Non puoi scrivere una recensione per questo documento.';
                }
            }

            if ($has_reviewed) {
                get_template_part('template-parts/single-document/singola-recensione', null, array('review' => $comment));
            } else {
                if ($can_review) {
                    ?>
                    <div id="avviso-scrivi-recensione" class="alert alert-info">
                        <p class="text-muted">Hai scaricato questo documento ma non ci hai ancora detto cosa ne pensi. Aiuta gli altri utenti, scrivi la tua recensione!
                        <?php if (defined('PUNTI_BLU_PRIMA_RECENSIONE') && PUNTI_BLU_PRIMA_RECENSIONE > 0) { ?>
                            <br>Scrivendo la tua recensione guadagnerai <strong><?php echo PUNTI_BLU_PRIMA_RECENSIONE; ?> <?php echo get_sistema_punti('blu')->print_icon() ?> </strong>!
                        <?php } ?>
                        </p>
                    </div>
                    <?php
                    get_template_part('template-parts/single-document/scrivi-recensione', null);
                    ?>
                    <div id="miaRecensione" class="mt-4"></div>
                    <?php
                } else {
                    if (is_user_logged_in()) {
                        ?>
                        <div class="alert alert-warning mt-4" role="alert">
                            <?php echo $reason; ?>
                        </div>
                        <?php
                    }
                }
            }
            ?>

            <hr class="my-4">

            <?php
            $last_reviews = get_comments(array(
                'post_id' => $product->get_id(),
                'status' => 'approve',
                'type'   => 'review',
                'number' => 2,
                'author__not_in' => array($current_user->ID),
                'orderby' => 'comment_date',
                'order' => 'DESC',
            ));
            ?>

            <?php if (!empty($last_reviews)) : ?>
                <h2 class="h5 custom-bold">Cosa ne pensano gli altri utenti</h2>
                <?php foreach ($last_reviews as $review) : ?>
                    <?php get_template_part('template-parts/single-document/singola-recensione', null, array('review' => $review)); ?>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="alert alert-info mt-4" role="alert">
                    Al momento non ci sono recensioni da parte di altri utenti per questo documento.
                </div>
            <?php endif; ?>

            <?php get_template_part('template-parts/single-document/modal-tutte-recensioni', null); ?>
        </div>
    </div>
</div>

