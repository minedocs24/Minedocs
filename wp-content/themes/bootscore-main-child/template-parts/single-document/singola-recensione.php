<?php

$review = $args['review'];
$prefix = $args['prefix'] ?? '';
$id = $review->comment_ID;
$hid = get_comment_hash($id);
$current_user_id = get_current_user_id();
$is_current_user_review = $review->user_id == $current_user_id;

$unique_id = $prefix . $id;
$unique_hid = $prefix . $hid;
$rating = get_comment_meta($review->comment_ID, 'rating', true);

$reports = get_comment_meta($review->comment_ID, 'review_reports', true);
if(!$reports) {
    $reports = array();
}

$gia_segnalato = false;
if (array_key_exists($current_user_id, $reports)) {
    $gia_segnalato = true;
}


$class_user_review = $is_current_user_review ? 'current-user-review' : '';
$class_user_label = $is_current_user_review ? 'current-user-label' : '';

if ($is_current_user_review) {
    switch ($rating) {
        case 1:
        case 2:
            $class_user_review .= ' current-user-review-bad';
            $class_user_label .= ' current-user-label-bad';
            break;
        case 3:
            $class_user_review .= ' current-user-review-neutral';
            $class_user_label .= ' current-user-label-neutral';
            break;
        case 4:
        case 5:
            $class_user_review .= ' current-user-review-good';
            $class_user_label .= ' current-user-label-good';
            break;
    }
}


$likes_dislikes = get_comment_meta($id, 'likes_dislikes', true);

$users_liked = 0;
$users_disliked = 0;
$current_user_action = null;

if ($likes_dislikes) {
    foreach ($likes_dislikes as $user_id => $action) {
        if ($action === 'like') {
            $users_liked++;
        } elseif ($action === 'dislike') {
            $users_disliked++;
        }
    }
    $current_user_action = $likes_dislikes[$current_user_id] ?? null;
}


?>

<div class="review-item <?php echo $class_user_review; ?>">
    <?php if ($is_current_user_review): ?>
    <div class="<?php echo $class_user_label ?>">La tua recensione</div>
    <?php endif; ?>
    <div class="review-header d-flex  justify-content-between">
        <div class="d-flex align-items-center">
            <img src="<?php echo get_avatar_url($review->user_id, ['size' => 50]); ?>" class="rounded-circle me-2"
                alt="Avatar">
            <div>
                <strong><?php echo esc_html(get_user_by('email', $review->comment_author_email)->nickname)  //echo esc_html($review->comment_author); ?></strong>
                <div class="review-date text-muted"><?php echo date('d M Y', strtotime($review->comment_date)); ?></div>
            </div>
        </div>
        <div class="review-rating<?php echo $unique_hid; ?>">
            <div id="reviewRating" class="rating-box readonly">
                <?php
                
                for ($i = 5; $i >= 1; $i--) {
                    $checked = $i == $rating ? 'checked' : '';
                    echo '<input type="radio" id="star' . $i . '-'.$unique_hid.'" name="rating'.$unique_hid.'" value="' . $i . '" ' . $checked . ' disabled>';
                    echo '<label for="star' . $i . '-'.$unique_hid.'" title="' . $i . ' stars">&#9733;</label>';
                }
                ?>
            </div>
        </div>
    </div>
    <p class="review-text"><?php echo esc_html($review->comment_content); ?></p>
    <hr>

    <?php if ($is_current_user_review): ?>
        <div class="review-actions d-flex">
        <div class="mx-1">
            <button disabled class="btn <?php echo $current_user_action=='like' ? 'btn-success': 'btn-muted' ?> btn-sm"
                tooltip="Hai trovato utile questa recensione">

                <div style="display: flex; align-items: center; gap: 5px;">
                    <span id="numero-like-<?php echo $hid?>" class="numero-like-<?php echo $hid?>"><?php echo $users_liked ?></span>
                    <?php get_template_part('template-parts/commons/icon', null, array('icon_name' => 'like', 'size' => 20))  ?>
                </div>
            </button>
        </div>
        <div class="mx-1">
            <button disabled class="btn <?php echo $current_user_action=='dislike' ? 'btn-danger': 'btn-muted' ?> btn-sm"
                tooltip="Non hai trovato utile questa recensione">

                <div style="display: flex; align-items: center; gap: 5px;">
                    <span id="numero-dislike-<?php echo $hid?>" class="numero-dislike-<?php echo $hid?>"><?php echo $users_disliked ?></span>
                    <?php get_template_part('template-parts/commons/icon', null, array('icon_name' => 'dislike', 'size' => 20))  ?>
                </div>
            </button>
        </div>

    </div>
        <?php else: ?>
    <div class="review-actions d-flex">
    <div class="mx-1">
            <button id="review-like-<?php echo $hid?>" data-id-review="<?php echo $hid ?>" class="review-like review-like-<?php echo $hid?> btn <?php echo $current_user_action=='like' ? 'btn-success': 'btn-muted' ?> btn-sm"
                tooltip="Hai trovato utile questa recensione">

                <div style="display: flex; align-items: center; gap: 5px;">
                <div  class="icon-loading-like-<?php echo $hid?> btn-loader" hidden style="display: inline-block;">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                    <span id="numero-like-<?php echo $hid?>" class="numero-like-<?php echo $hid?>"><?php echo $users_liked ?></span>
                    <?php get_template_part('template-parts/commons/icon', null, array('icon_name' => 'like', 'size' => 20))  ?>
                </div>
            </button>
        </div>
        <div class="mx-1">
            <button id="review-dislike-<?php echo $hid?>"  data-id-review="<?php echo $hid ?>" class="review-dislike review-dislike-<?php echo $hid?> btn <?php echo $current_user_action=='dislike' ? 'btn-danger': 'btn-muted' ?> btn-sm"
                tooltip="Non hai trovato utile questa recensione">

                <div style="display: flex; align-items: center; gap: 5px;">
                <div class="icon-loading-like-<?php echo $hid?> btn-loader" hidden style="display: inline-block;">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                    <span id="numero-dislike-<?php echo $hid?>" class="numero-dislike-<?php echo $hid?>"><?php echo $users_disliked ?></span>
                    <?php get_template_part('template-parts/commons/icon', null, array('icon_name' => 'dislike', 'size' => 20))  ?>
                </div>
            </button>
        </div>
        
        <?php if(!$gia_segnalato): ?>
        
            <div class="mr-auto mx-1">
                <button  id="review-report-<?php echo $hid?>"  data-id-review="<?php echo $hid ?>" class="review-report review-report-<?php echo $hid?> btn btn-warning btn-sm ml-auto"
                    tooltip="Segnala recensione">
                    <?php get_template_part('template-parts/commons/icon', null, array('icon_name' => 'block', 'size' => 20)) ?>
                </button>
            </div>
        <?php else: ?>
            <div class="mr-auto mx-1">
                <button disabled id="review-report-<?php echo $hid?>" class=" btn btn-warning btn-sm ml-auto"
                    tooltip="Segnala recensione">
                    Segnalata
                </button>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>



</div>