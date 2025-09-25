<?php
$product = $args['product'];
$product_info = $args['product_info'];
global $post;
?>

<div class="col-md-4">
    <div class="card mb-4 shadow-sm card-custom zoom <?php echo array_key_exists('class', $product_info) ? $product_info['class'] : ''; ?>">
        <div class="card-header">
            <h3 class="my-0 fw-bold">
                <?php echo isset($product_info['titolo-html']) ? $product_info['titolo-html'] : $product->post_title; ?>
            </h3>
        </div>
        <div class="card-body p-5">
            <h1 class="card-title pricing-card-title">
                <?php echo get_price_html_divided_by(wc_get_product($product->ID), $product_info['mesi']); ?><small class="text-black fs-5"> /mese</small>
            </h1>
            <p class="pt-2">Vantaggi:</p>
            <ul class="list-unstyled mt-3 mb-4">
                <li><?php get_template_part('template-parts/premium-plans/feature', null, array('feature' => array('icon' => 'puntipro.svg', 'text' => $product_info['punti-pro'] . ' punti Pro'))); ?></li>
                <li><?php get_template_part('template-parts/premium-plans/feature', null, array('feature' => array('icon' => 'libro.svg', 'text' => 'Accesso ai documenti Pro'))); ?></li>
                <li><?php get_template_part('template-parts/premium-plans/feature', null, array('feature' => array('icon' => 'penna.svg', 'text' => 'Esercitazioni illimitate'))); ?></li>
            </ul>
            <div class="pt-4">
                <?php if (is_abbonamento_attivo(get_current_user_id())) { ?>
                    <a class="btn-error-abbonamento-attivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary mt-auto btn-custom"
                       data-link-points-page="<?php echo get_permalink(get_page_by_path('compra-pacchetti-punti')->ID); ?>"
                       aria-label="Aggiungi al carrello: &quot;<?php echo $product->post_title; ?>&quot;" 
                       rel="nofollow">
                        <div class="btn-loader">
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/premium-plans/cart.svg" class="me-2" style="width: 20px; height: 20px;">
                        Passa a Pro
                    </a>
                <?php } else { ?>
                    <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $post->ID . ']'); ?>" 
                    data-quantity="1"
                       class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary mt-auto btn-custom"
                       data-product_id="<?php echo $product->ID; ?>" 
                       aria-label="Aggiungi al carrello: &quot;<?php echo $product->post_title; ?>&quot;" rel="nofollow"
                       data-product_name="<?php echo $product->post_title; ?>" 
                       data-price="<?php echo $product_info['prezzo-scontato']; ?>">
                        <div class="btn-loader">
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/premium-plans/cart.svg" class="me-2" style="width: 20px; height: 20px;">
                        Passa a Pro
                    </a>
                <?php } ?>
            </div>
            <p class="mt-3 fs-6">In un'unica soluzione da <?php echo wc_get_product($product->ID)->get_price_html(); ?></p>
        </div>
    </div>
</div>