<?php


function get_price_html_divided_by($product, $d) {
    // Ottieni il prezzo HTML del prodotto
    $price_html = $product->get_price_html();

    // Usa una regex per trovare gli importi nel prezzo HTML
    preg_match_all('/\d+([,.]\d{1,2})?/', $price_html, $matches);

    // Se ci sono importi trovati, dividili
    if (!empty($matches[0])) {
        $amounts = $matches[0];
        $divided_amounts = array_map(function($amount) use ($d) {
            // Rimuovi eventuali virgole e converti in float
            $amount = str_replace(',', '', $amount);
            return number_format(((float) $amount) / (100*$d), 2);
        }, $amounts);

        // Sostituisci gli importi originali con quelli divisi nel prezzo HTML
        foreach ($amounts as $index => $amount) {
            $price_html = str_replace($amount, $divided_amounts[$index], $price_html);
        }
    }

    return $price_html;
}

function custom_sale_price( $price, $product ){
    // Now we check to see if the product is onsale.
    if ( $product->is_on_sale() ) {
        // Change this to whatever worked for you when you modified core files.
        $price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
   
    }

     return $price;  
}

add_filter('woocommerce_get_price_html', 'custom_sale_price', 10, 2 );