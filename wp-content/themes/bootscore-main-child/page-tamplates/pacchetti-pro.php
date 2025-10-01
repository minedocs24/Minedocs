<?php

/**
 * Template Name: Pacchetti Pro
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();

// ID dei prodotti WooCommerce
$product_ids = array(
    'mensile' => 117, // Sostituisci con l'ID del prodotto mensile
    'trimestrale' => 136, // Sostituisci con l'ID del prodotto trimestrale
    'annuale' => 137 // Sostituisci con l'ID del prodotto annuale
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
            <h1 class="display-4 fw-bold mb-3">Passa a <span class="pro">Pro</span></h1>
            <p class="lead text-muted">Sblocca tutte le funzionalità premium e ottieni vantaggi esclusivi</p>
        </div>

        <!-- Abbonamento attivo -->
        <?php if (is_abbonamento_attivo(get_current_user_id())) { ?>
            <div class="alert alert-primary text-center" role="alert">
                <strong>Il tuo abbonamento è già attivo!</strong>
                <div class="mt-3">
                    <a href="<?php echo (PROFILO_UTENTE_IMPOSTAZIONI. '#abbonamento'); ?>" class="btn btn-success m-1">Gestisci il tuo abbonamento</a>
                    <a href="<?php echo (PACCHETTI_PUNTI_PAGE); ?>" class="btn btn-success m-1">Ho bisogno di punti</a>
                </div>
            </div>
        <?php } ?>

        <!-- Pricing Plans Section -->
        <div class="row g-4 justify-content-center">
            <!-- Piano Mensile -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm position-relative">

                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            
                            <h3 class="display-3 mb-3"><span class="pro">PRO</span> Mensile</h3>
                            <?php if (isset($prices['mensile']['sale_price']) && $prices['mensile']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['mensile']['regular_price']/12); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['mensile']['sale_price']/12); ?></span>
                                    <span class="pro-prezzo-scontato-mese text-muted">al mese</span>
                                </div>
                                <?php $percentuale_sconto = round((($prices['mensile']['regular_price'] - $prices['mensile']['sale_price']) / $prices['mensile']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['mensile']['price']; ?></div>
                                <span class="pro-prezzo-scontato-mese text-muted">al mese</span>
                            <?php endif; ?>
                            
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni <?php echo AMOUNT_PUNTI_PRO_ABBPRO030; ?> punti Pro  <i class="fas fa-info-circle" tooltip="Senza rinnovo automatico, i punti scadono con il piano. Con rinnovo automatico attivo, li conservi."></i></li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Accesso ai documenti Pro</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Esercitazioni illimitate*</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Accesso agli strumenti AI*</li>
                        </ul>
                        <?php if (is_abbonamento_attivo(get_current_user_id())) { ?>
                            <a class="btn-error-abbonamento-attivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-link-points-page="<?php echo get_permalink(get_page_by_path('compra-pacchetti-punti')->ID); ?>"
                                aria-label="Aggiungi al carrello: &quot;Mensile&quot;" rel="nofollow">
                                Scegli Mensile
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['mensile'] . ']'); ?>" data-quantity="1"
                            class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                            data-product_id="<?php echo $product_ids['mensile']; ?>" aria-label="Aggiungi al carrello: &quot;Mensile&quot;" rel="nofollow"
                            data-product_name="<?php echo $products_names['mensile']; ?>" data-price="<?php echo $prices['mensile']['regular_price']; ?>" product-title="<?php echo $products_names['mensile']; ?>">
                            Scegli Mensile
                        </a>
                        <?php } ?>
                        <p class="text-muted mt-2">in un'unica soluzione di <?php echo isset($prices['mensile']['sale_price']) && $prices['mensile']['sale_price'] ? wc_price($prices['mensile']['sale_price']/12) : $prices['mensile']['price']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Piano Trimestrale -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow position-relative">

                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <span class="badge bg-warning text-dark mb-2">Più Popolare</span>
                            <h3 class="display-3 mb-3"><span class="pro">PRO</span> Trimestrale</h3>
                            <?php if (isset($prices['trimestrale']['sale_price']) && $prices['trimestrale']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['trimestrale']['regular_price']/3); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['trimestrale']['sale_price']/3); ?></span>
                                    <span class="pro-prezzo-scontato-mese text-muted">al mese</span>
                                </div>
                                <?php $percentuale_sconto = round((($prices['trimestrale']['regular_price'] - $prices['trimestrale']['sale_price']) / $prices['trimestrale']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['trimestrale']['price']; ?></div>
                            <?php endif; ?>
                            
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni <?php echo AMOUNT_PUNTI_PRO_ABBPRO090; ?> punti Pro <i class="fas fa-info-circle" tooltip="Senza rinnovo automatico, i punti scadono con il piano. Con rinnovo automatico attivo, li conservi."></i></li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Accesso ai documenti Pro</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Esercitazioni illimitate*</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Accesso agli strumenti AI*</li>
                        </ul>
                        <?php if (is_abbonamento_attivo(get_current_user_id())) { ?>
                            <a class="btn-error-abbonamento-attivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-link-points-page="<?php echo get_permalink(get_page_by_path('compra-pacchetti-punti')->ID); ?>"
                                aria-label="Aggiungi al carrello: &quot;Trimestrale&quot;" rel="nofollow">
                                Scegli Trimestrale
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['trimestrale'] . ']'); ?>" data-quantity="1"
                                class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-product_id="<?php echo $product_ids['trimestrale']; ?>" aria-label="Aggiungi al carrello: &quot;<?php echo $products_names['trimestrale']; ?>&quot;" rel="nofollow"
                                data-product_name="<?php echo $products_names['trimestrale']; ?>" data-price="<?php echo $prices['trimestrale']['regular_price']; ?>" product-title="<?php echo $products_names['trimestrale']; ?>">
                                Scegli Trimestrale
                            </a>
                        <?php } ?>
                        <p class="text-muted mt-2">in un'unica soluzione di <?php echo isset($prices['trimestrale']['sale_price']) && $prices['trimestrale']['sale_price'] ? wc_price($prices['trimestrale']['sale_price']) : $prices['trimestrale']['price']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Piano Annuale -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm position-relative">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="display-3 mb-3"><span class="pro">PRO</span> Annuale</h3>
                            <?php if (isset($prices['annuale']['sale_price']) && $prices['annuale']['sale_price']): ?>
                                <div class="d-flex justify-content-center">
                                    <span class="pro-prezzo-originale text-decoration-line-through"><?php echo wc_price($prices['annuale']['regular_price']/12); ?></span>
                                    <span class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo wc_price($prices['annuale']['sale_price']/12); ?></span>
                                    <span class="pro-prezzo-scontato-mese text-muted">al mese</span>
                                </div>
                                <?php $percentuale_sconto = round((($prices['annuale']['regular_price'] - $prices['annuale']['sale_price']) / $prices['annuale']['regular_price']) * 100); ?>
                                <span class="badge bg-danger mb-2"><?php echo "Risparmia " . $percentuale_sconto . "%"; ?></span>
                            <?php else: ?>
                                <div class="pro-prezzo-scontato display-4 fw-bold mb-3"><?php echo $prices['annuale']['price']; ?></div>
                                <p class="pro-prezzo-scontato-mese text-muted">al mese</p>
                            <?php endif; ?>
                            
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Ottieni <?php echo AMOUNT_PUNTI_PRO_ABBPRO365; ?> punti Pro <i class="fas fa-info-circle" tooltip="Senza rinnovo automatico, i punti scadono con il piano. Con rinnovo automatico attivo, li conservi."></i></li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Accesso ai documenti Pro</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Esercitazioni illimitate*</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Accesso agli strumenti AI*</li>
                        </ul>
                        <?php if (is_abbonamento_attivo(get_current_user_id())) { ?>
                            <a class="btn-error-abbonamento-attivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-link-points-page="<?php echo get_permalink(get_page_by_path('compra-pacchetti-punti')->ID); ?>"
                                aria-label="Aggiungi al carrello: &quot;Annuale&quot;" rel="nofollow">
                                Scegli Annuale
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['annuale'] . ']'); ?>" data-quantity="1"
                                class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100"
                                data-product_id="<?php echo $product_ids['annuale']; ?>" aria-label="Aggiungi al carrello: &quot;Annuale&quot;" rel="nofollow"
                                data-product_name="<?php echo $products_names['annuale']; ?>" data-price="<?php echo $prices['annuale']['regular_price']; ?>" product-title="<?php echo $products_names['annuale']; ?>">
                                Scegli Annuale
                            </a>
                        <?php } ?>
                        <p class="text-muted mt-2">in un'unica soluzione di <?php echo isset($prices['annuale']['sale_price']) && $prices['annuale']['sale_price'] ? wc_price($prices['annuale']['sale_price']) : $prices['annuale']['price']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center mt-5 pt-5">
            <h2 class="h3 mb-4">Pronto a migliorare la tua esperienza?</h2>
            <p class="lead text-muted mb-4">Unisciti a migliaia di studenti che hanno già scelto il piano Pro</p>
            <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $product_ids['annuale'] . ']'); ?>" class="btn btn-primary btn-lg">Inizia Ora</a>
        </div>

        <!-- Note Section -->
        <div class="text-muted mt-4">
            <p>* Esercitazioni illimitate e Accesso agli strumenti AI saranno disponibili a breve.</p>
        </div>
    </div>
</div>

<?php
get_footer();

