<?php
function get_product_purchase_count($product_id) {
    // Ottieni tutti gli ordini completati
    $orders = wc_get_orders(array(
        'status' => 'completed',
        'limit' => -1, // Nessun limite per ottenere tutti gli ordini
    ));

    $purchase_count = 0;

    // Itera attraverso gli ordini
    foreach ($orders as $order) {
        // Ottieni gli articoli dell'ordine
        $items = $order->get_items();

        // Itera attraverso gli articoli dell'ordine
        foreach ($items as $item) {
            // Verifica se l'ID del prodotto corrisponde
            if ($item->get_product_id() == $product_id) {
                $purchase_count += $item->get_quantity();
            }
        }
    }

    return $purchase_count;
}

