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
            <h1 class="display-4 fw-bold mb-3">Ricarica Punti <span class="pro">Pro</span></h1>
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

        <!-- Pricing Plans Section -->
        <div class="row g-4 justify-content-center">
            <!-- Piano 150 Punti -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm position-relative">

                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            
                            <h3 class="display-3 mb-3">Inizia come un <span class="pro">Pro</span></h3>
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
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni 150 punti Pro <i class="fas fa-info-circle" tooltip="Consumabili entro 365 giorni con abbonamento attivo"></i></li>
                        </ul>
                        <?php if (!is_abbonamento_attivo(get_current_user_id())) { ?>
                            <a class="btn-error-abbonamento-attivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-link-points-page="<?php echo get_permalink(get_page_by_path('compra-pacchetti-punti')->ID); ?>"
                                aria-label="Aggiungi al carrello: &quot;150 Punti&quot;" rel="nofollow">
                                Ricarica 150 Punti
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['150-punti-pro'] . ']'); ?>" data-quantity="1"
                            class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                            data-product_id="<?php echo $product_ids['mensile']; ?>" aria-label="Aggiungi al carrello: &quot;Mensile&quot;" rel="nofollow"
                            data-product_name="<?php echo $products_names['150-punti-pro']; ?>" data-price="<?php echo $prices['150-punti-pro']['regular_price']; ?>" product-title="<?php echo $products_names['150-punti-pro']; ?>">
                            Ricarica 150 Punti
                        </a>
                        <?php } ?>
                      
                    </div>
                </div>
            </div>

            <!-- Piano 500 Punti -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow position-relative">

                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <span class="badge bg-warning text-dark mb-2">Più Popolare</span>
                            <h3 class="display-3 mb-3">Cresci come un <span class="pro">Pro</span></h3>
                            <?php if (isset($prices['500-punti-pro']['sale_price']) && $prices['500-punti-pro']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['500-punti-pro']['regular_price']); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['500-punti-pro']['sale_price']); ?></span>

                                </div>
                                <?php $percentuale_sconto = round((($prices['500-punti-pro']['regular_price'] - $prices['500-punti-pro']['sale_price']) / $prices['500-punti-pro']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['500-punti-pro']['price']; ?></div>
                            <?php endif; ?>
                            
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni 500 punti Pro <i class="fas fa-info-circle" tooltip="Consumabili entro 365 giorni con abbonamento attivo"></i></li>
                        </ul>
                        <?php if (!is_abbonamento_attivo(get_current_user_id())) { ?>
                            <a class="btn-error-abbonamento-attivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-link-points-page="<?php echo get_permalink(get_page_by_path('compra-pacchetti-punti')->ID); ?>"
                                aria-label="Aggiungi al carrello: &quot;500 Punti&quot;" rel="nofollow">
                                Ricarica 500 Punti
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['500-punti-pro'] . ']'); ?>" data-quantity="1"
                                class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-product_id="<?php echo $product_ids['500-punti-pro']; ?>" aria-label="Aggiungi al carrello: &quot;<?php echo $products_names['500-punti-pro']; ?>&quot;" rel="nofollow"
                                data-product_name="<?php echo $products_names['500-punti-pro']; ?>" data-price="<?php echo $prices['500-punti-pro']['regular_price']; ?>" product-title="<?php echo $products_names['500-punti-pro']; ?>">
                                Ricarica 500 Punti
                            </a>
                            <?php } ?>
                        </div>
                </div>
            </div>

            <!-- Piano 1000 Punti -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm position-relative">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                                <h3 class="display-3 mb-3">Eccelli come un <span class="pro">Pro</span></h3>
                            <?php if (isset($prices['1000-punti-pro']['sale_price']) && $prices['1000-punti-pro']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['1000-punti-pro']['regular_price']); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['1000-punti-pro']['sale_price']); ?></span>
                                    
                                </div>
                                <?php $percentuale_sconto = round((($prices['1000-punti-pro']['regular_price'] - $prices['1000-punti-pro']['sale_price']) / $prices['1000-punti-pro']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['1000-punti-pro']['price']; ?></div>
                                
                            <?php endif; ?>
                            
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni 1000 punti Pro <i class="fas fa-info-circle" tooltip="Consumabili entro 365 giorni con abbonamento attivo"></i></li>
                        </ul>
                        <?php if (!is_abbonamento_attivo(get_current_user_id())) { ?>
                            <a class="btn-error-abbonamento-attivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-link-points-page="<?php echo get_permalink(get_page_by_path('compra-pacchetti-punti')->ID); ?>"
                                aria-label="Aggiungi al carrello: &quot;1000 Punti&quot;" rel="nofollow">
                                Ricarica 1000 Punti
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['1000-punti-pro'] . ']'); ?>" data-quantity="1"
                                class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-product_id="<?php echo $product_ids['1000-punti-pro']; ?>" aria-label="Aggiungi al carrello: &quot;1000 Punti&quot;" rel="nofollow"
                                data-product_name="<?php echo $products_names['1000-punti-pro']; ?>" data-price="<?php echo $prices['1000-punti-pro']['regular_price']; ?>" product-title="<?php echo $products_names['1000-punti-pro']; ?>">
                                Ricarica 1000 Punti
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
get_footer();

