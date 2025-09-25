<?php
$product = $args['product'];
$product_info = $args['product_info'];
global $post;

?>

<div class="col-lg-4">
    <div
        class="card mb-4 shadow-sm card-custom zoom <?php if(array_key_exists('class', $product_info)) {echo $product_info['class'];} ?>">
        <div class="card-header">
            <h3 class="my-0 fw-bold">
                <?php if(isset($product_info['titolo-html'])){echo $product_info['titolo-html'];} else {echo $product->post_title; } ?>
            </h3>
        </div>
        <div class="card-body p-5">
            <h1 class="card-title pricing-card-title"><?php echo wc_get_product($product->ID)->get_price_html(); ?></h1>
            <div class="pt-4">
                <?php if(is_abbonamento_attivo(get_current_user_id())){ ?>
                <a href="<?php echo do_shortcode('[add_to_cart_url id=' . $post->ID . ']'); ?>" data-quantity="1"
                    class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary mt-auto btn-custom "
                    data-product_id="<?php echo $product->ID ?>" data-product_sku=""
                    aria-label="Aggiungi al carrello: &quot;<?php echo $product->post_title; ?>&quot;" rel="nofollow"
                    data-product_name="<?php echo $product->post_title; ?>"
                    data-price="<?php echo $product_info['prezzo-scontato']; ?>"
                    product-title="<?php echo $product->post_title; ?>">
                    <div class="btn-loader">
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                    <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/premium-plans/cart.svg"
                        style="width: 20px; height: 20px; margin-right: 8px;">
                    Aggiungi
                </a>
                <?php } else { ?>
                <a class="btn-error-abbonamento-disattivo product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary mt-auto btn-custom "
                    data-link-pro-page="<?php echo get_permalink( get_page_by_path( 'pacchetti-premium')->ID) ?>"
                    aria-label="Aggiungi al carrello: &quot;<?php echo $product->post_title; ?>&quot;" rel="nofollow">
                    <div class="btn-loader">
                        <span claf="ss="spinner-border spinner-border-sm"></span>
                    </div>
                    <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/premium-plans/cart.svg"
                        style="width: 20px; height: 20px; margin-right: 8px;">
                    Aggiungi
                </a>
                <?php } ?>
            </div>
            <!--<a href="<?php echo do_shortcode( '[add_to_cart_url id='.$post->ID.']'); ?>" data-quantity="1" class="product_type_simple add_to_cart_button ajax_add_to_cart btn btn-primary w-100 mt-auto" data-product_id="<?php echo $product->ID ?>" data-product_sku="" aria-label="Aggiungi al carrello: &quot;<?php echo $product->post_title; ?>&quot;" rel="nofollow" data-product_name="<?php echo $product->post_title; ?>" data-price="<?php echo $product_info['prezzo-scontato']; ?>" product-title="<?php echo $product->post_title; ?>"><div class="btn-loader"><span class="spinner-border spinner-border-sm"></span></div>Passa a Pro</a>-->
        </div>
    </div>
</div>