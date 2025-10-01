<?php
global $product;
$reviews = get_comments(array(
    'post_id' => $product->get_id(),
    'status' => 'approve',
    'type'   => 'review',
));

$total_reviews = $product->get_review_count();  // Numero totale di recensioni
#$average_rating = round($product->get_average_rating(), 1);  // Media delle stelle
#$rating_counts = $product->get_rating_counts(); // Array con il numero di recensioni per ogni voto (1-5)


if (!empty($reviews)) : ?>
    <?php

    $rating_counts = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);

    foreach ($reviews as $review) {
        $rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
        if ($rating >= 1 && $rating <= 5) {
            $rating_counts[$rating]++;
        }
    }

    $average_rating = 0;
    $total_ratings = 0;
    $total_weighted_ratings = 0;

    foreach ($rating_counts as $rating => $count) {
        $total_ratings += $count;
        $total_weighted_ratings += $rating * $count;
    }

    if ($total_ratings > 0) {
        $average_rating = round($total_weighted_ratings / $total_ratings, 1);
    }
    ?>
    <button class="btn btn-primary" id="show-reviews">Mostra tutte le recensioni (<?php echo $total_reviews; ?>)</button>

    <div class="modal fade" id="reviewsModal" tabindex="-1" aria-labelledby="reviewsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-lg shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewsModalLabel">Tutte le recensioni</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="review-summary p-4 mb-4 bg-light rounded">
                        <div class="text-center">
                            <h4 class="fw-bold mb-2">Valutazione media</h4>
                            <div class="review-stars mb-2">
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <i class="fas fa-star<?php echo $i <= $average_rating ? ' text-warning' : ' text-secondary'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted">(Basato su <?php echo $total_reviews; ?> recensioni)</p>
                        </div>

                        <div class="rating-distribution">
                            <?php for ($stars = 5; $stars >= 1; $stars--) :
                                $count = isset($rating_counts[$stars]) ? $rating_counts[$stars] : 0;
                                $percentage = ($total_reviews > 0) ? ($count / $total_reviews) * 100 : 0;
                            ?>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="me-2"><?php echo $stars; ?> ⭐</span>
                                    <div class="progress flex-grow-1" style="height: 10px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="ms-2 text-muted"><?php echo $count; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Lista delle singole recensioni -->
                    <?php foreach ($reviews as $review) : ?>
                        <?php get_template_part('template-parts/single-document/singola-recensione', null, array('review' => $review, 'prefix'=>'modal_all_')); ?>
                        
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; 