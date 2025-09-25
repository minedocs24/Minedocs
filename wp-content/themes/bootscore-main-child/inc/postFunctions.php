<?php
function getPostAverageRating($post_ids) {
    $reviews_on_user_documents_count = 0;
    foreach ($post_ids as $document_id) {
        $reviews_on_user_documents_count += count(get_comments(array(
            'post_id' => $document_id,
            'status' => 'approve',
        )));
    }

    // Calcolo della media delle recensioni ricevute dall'utente sui suoi documenti
    $reviews_on_user_documents_rating_sum = 0;
    foreach ($post_ids as $document_id) {
        $reviews = get_comments(array(
            'post_id' => $document_id,
            'status' => 'approve',
        ));
        foreach ($reviews as $review) {
            $reviews_on_user_documents_rating_sum += intval(get_comment_meta($review->comment_ID, 'rating', true));
        }
    }
    // Calcolo della media delle recensioni ricevute dall'utente sui suoi documenti
    $reviews_on_user_documents_rating_avg = 0;
    if ($reviews_on_user_documents_count > 0) {
        $reviews_on_user_documents_rating_avg = $reviews_on_user_documents_rating_sum / $reviews_on_user_documents_count;
    }
    // Arrotondamento alla prima cifra decimale
    $reviews_on_user_documents_rating_avg = round($reviews_on_user_documents_rating_avg, 1);
    return $reviews_on_user_documents_rating_avg;
}

?>