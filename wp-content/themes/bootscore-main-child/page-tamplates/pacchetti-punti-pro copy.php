<?php

/**
 * Template Name: Pacchetti Punti Pro
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();

// ID dei prodotti WooCommerce
$product_ids = array(
    '150-punti-pro' => 165, // Sostituisci con l'ID del prodotto mensile
    '500-punti-pro' => 166, // Sostituisci con l'ID del prodotto trimestrale
    '1000-punti-pro' => 167 // Sostituisci con l'ID del prodotto annuale
);

$prices = array();
$products_names = array();
foreach ($product_ids as $key => $id) {
    $product = wc_get_product($id);
    $prices[$key] = array(
        'price' => $product->get_price_html(),
        'regular_price' => $product->get_regular_price(),
        'sale_price' => $product->get_sale_price()
    );
}

$products_names = array_map(function ($product_id) {
    return get_post($product_id)->post_title;
}, $product_ids);

?>

<div id="content" class="site-content">
    <div class="container py-5">
        <!-- Header Section -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Acquista Punti <span class="pro">Pro</span></h1>
            <p class="lead text-muted">Il tuo abbonamento è attivo ma hai bisogno di punti per accedere a tutti i contenuti del sito?</p>
        </div>

        <!-- Abbonamento attivo -->
        <?php if (!is_abbonamento_attivo(get_current_user_id())) { ?>
            <div class="alert alert-warning text-center" role="alert">
                <strong>Non hai un abbonamento attivo! Cosa aspetti?</strong>
                <div class="mt-3">
                    <a href="<?php echo (PIANI_PRO_PAGE); ?>" class="btn btn-success">
                        Vai a piani <span class="pro">Pro</span> e studia come un <i><span class="pro">Pro</span></i>
                    </a>
                </div>
            </div>
        <?php } ?>

        <!-- Slider Section -->
        <div class="text-center mb-4">
            <input type="range" min="150" max="1000" step="350" value="150" id="pointsSlider" class="form-range" oninput="updatePoints(this.value)">
            <p id="selectedPoints" class="display-3 mb-3">150 Punti <span class="pro">Pro</span></p>
        </div>

        <!-- Pricing Plans Section -->
        <div class="row g-4 justify-content-center">
            <div class="col-md-4" id="plan150">
                <div class="card h-100 border-0 shadow-sm position-relative">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="display-3 mb-3">150 Punti <span class="pro">Pro</span></h3>
                            <?php if (isset($prices['150-punti-pro']['sale_price']) && $prices['150-punti-pro']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['150-punti-pro']['regular_price']); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['150-punti-pro']['sale_price']); ?></span>
                                </div>
                                <?php $percentuale_sconto = round((($prices['150-punti-pro']['regular_price'] - $prices['150-punti-pro']['sale_price']) / $prices['150-punti-pro']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['150-punti-pro']['price']; ?></div>
                            <?php endif; ?>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni 150 punti Pro</li>
                        </ul>
                        <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['150-punti-pro'] . ']'); ?>" data-quantity="1"
                            class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                            data-product_id="<?php echo $product_ids['150-punti-pro']; ?>" aria-label="Aggiungi al carrello: &quot;150 Punti&quot;" rel="nofollow"
                            data-product_name="<?php echo $products_names['150-punti-pro']; ?>" data-price="<?php echo $prices['150-punti-pro']['regular_price']; ?>" product-title="<?php echo $products_names['150-punti-pro']; ?>">
                            Scegli 150 Punti
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4" id="plan500" style="display:none;">
                <div class="card h-100 border-0 shadow position-relative">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <span class="badge bg-warning text-dark mb-2">Più Popolare</span>
                            <h3 class="display-3 mb-3"><span class="pro">PRO</span> Trimestrale</h3>
                            <?php if (isset($prices['500-punti-pro']['sale_price']) && $prices['500-punti-pro']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['500-punti-pro']['regular_price']/3); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['500-punti-pro']['sale_price']/3); ?></span>
                                    <span class="pro-prezzo-scontato-mese text-muted">al mese</span>
                                </div>
                                <?php $percentuale_sconto = round((($prices['500-punti-pro']['regular_price'] - $prices['500-punti-pro']['sale_price']) / $prices['500-punti-pro']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['500-punti-pro']['price']; ?></div>
                            <?php endif; ?>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni 500 punti Pro</li>
                        </ul>
                        <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['500-punti-pro'] . ']'); ?>" data-quantity="1"
                            class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                            data-product_id="<?php echo $product_ids['500-punti-pro']; ?>" aria-label="Aggiungi al carrello: &quot;500 Punti&quot;" rel="nofollow"
                            data-product_name="<?php echo $products_names['500-punti-pro']; ?>" data-price="<?php echo $prices['500-punti-pro']['regular_price']; ?>" product-title="<?php echo $products_names['500-punti-pro']; ?>">
                            Scegli 500 Punti
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4" id="plan1000" style="display:none;">
                <div class="card h-100 border-0 shadow-sm position-relative">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="display-3 mb-3"><span class="pro">PRO</span> Annuale</h3>
                            <?php if (isset($prices['1000-punti-pro']['sale_price']) && $prices['1000-punti-pro']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['1000-punti-pro']['regular_price']/12); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['1000-punti-pro']['sale_price']/12); ?></span>
                                    <span class="pro-prezzo-scontato-mese text-muted">al mese</span>
                                </div>
                                <?php $percentuale_sconto = round((($prices['1000-punti-pro']['regular_price'] - $prices['1000-punti-pro']['sale_price']) / $prices['1000-punti-pro']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['1000-punti-pro']['price']; ?></div>
                                <p class="pro-prezzo-scontato-mese text-muted">al mese</p>
                            <?php endif; ?>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni 1000 punti Pro</li>
                        </ul>
                        <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['1000-punti-pro'] . ']'); ?>" data-quantity="1"
                            class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                            data-product_id="<?php echo $product_ids['1000-punti-pro']; ?>" aria-label="Aggiungi al carrello: &quot;1000 Punti&quot;" rel="nofollow"
                            data-product_name="<?php echo $products_names['1000-punti-pro']; ?>" data-price="<?php echo $prices['1000-punti-pro']['regular_price']; ?>" product-title="<?php echo $products_names['1000-punti-pro']; ?>">
                            Scegli 1000 Punti
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center mt-5 pt-5">
            <h2 class="h3 mb-4">Pronto a migliorare la tua esperienza?</h2>
            <p class="lead text-muted mb-4">Unisciti a migliaia di studenti che hanno già scelto il piano Pro</p>
            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['1000-punti-pro'] . ']'); ?>" class="btn btn-primary btn-lg">Inizia Ora</a>
        </div>

        <!-- Note Section -->
        <div class="text-muted mt-4">
            <p>* Esercitazioni illimitate e Accesso agli strumenti AI saranno disponibili a breve.</p>
        </div>
    </div>
</div>

<script>
    function updatePoints(value) {
        document.getElementById('selectedPoints').innerText = value + " Punti Pro";
        document.getElementById('plan150').style.display = value == 150 ? 'block' : 'none';
        document.getElementById('plan500').style.display = value == 500 ? 'block' : 'none';
        document.getElementById('plan1000').style.display = value == 1000 ? 'block' : 'none';
    }
</script>

<?php
get_footer();
