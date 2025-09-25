<?php

add_filter('pre_comment_approved', 'block_duplicate_product_reviews', 10, 2);

function block_duplicate_product_reviews($approved, $commentdata) {
    // Verifica se il commento è associato a un prodotto WooCommerce
    if (get_post_type($commentdata['comment_post_ID']) !== 'product') {
        return $approved;
    }

    // Ottieni l'ID dell'utente che sta pubblicando la recensione
    $user_id = isset($commentdata['user_id']) ? $commentdata['user_id'] : 0;

    // Se l'utente non è autenticato, consentire la pubblicazione (modifica se necessario)
    if ($user_id === 0) {
        return $approved;
    }

    // Controlla se l'utente ha già inserito una recensione per questo prodotto
    $existing_reviews = get_comments([
        'post_id' => $commentdata['comment_post_ID'],
        'user_id' => $user_id,
        'type'    => 'review',
    ]);

    if (!empty($existing_reviews)) {
        // Impedisce la pubblicazione e restituisce un errore
        wp_die(
            __('Hai già pubblicato una recensione per questo prodotto.', 'your-text-domain'),
            __('Recensione già esistente', 'your-text-domain'),
            ['response' => 403]
        );
    }

    return $approved;
}





function handle_ajax_review_submission() {

    error_log('handle_ajax_review_submission');


    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in.'));
    }

    if ( ! isset($_POST['comment']) || ! isset($_POST['rating']) || (!isset($_POST['comment_post_ID']) && !isset($_POST['comment_post_Hid'])) ) {
        wp_send_json_error(array('message' => 'Invalid data.'));
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'submit_review_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
    }

    if( isset($_POST['comment_post_Hid']) ) {
        $product_hid = sanitize_text_field($_POST['comment_post_Hid']);
        $product_id = get_product_id_by_hash($product_hid);
        if (!$product_id) {
            wp_send_json_error(array('message' => 'Product not found.'));
        }
    } else {
        $product_id = intval($_POST['comment_post_ID']);
        if (!$product_id) {
            wp_send_json_error(array('message' => 'Product not found.'));
        }
    }

    $comment = sanitize_text_field($_POST['comment']);
    if (empty($comment)) {
        wp_send_json_error(array('message' => 'Comment cannot be empty.'));
    }
    if (strlen($comment) > 500) {
        wp_send_json_error(array('message' => 'Comment exceeds maximum length.'));
    }
    error_log('comment: ' . $comment);
    if (!preg_match('/^[a-zA-Z0-9\s.,;:!?\'"()àèéìòùÀÈÉÌÒÙ\-]+$/', $comment)) {
        wp_send_json_error(array('message' => 'Comment contains invalid characters.'));
    }
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
    if ($rating === false) {
        wp_send_json_error(array('message' => 'Invalid rating. Rating must be an integer between 1 and 5.'));
    }

    if(!user_has_purchased_product(get_current_user_id(), $product_id)) {
        wp_send_json_error(array('message' => 'User has not purchased this product.'));
    }
    // Controlla se l'utente ha già recensito questo prodotto
    $existing_reviews = get_comments(array(
        'post_id' => $product_id,
        'user_id' => get_current_user_id(),
        'type'    => 'review',
    ));

    if (!empty($existing_reviews)) {
        wp_send_json_error(array('message' => 'You have already reviewed this product.'));
    }

    $comment_data = array(
        'comment_post_ID' => $product_id,
        'comment_author' => wp_get_current_user()->display_name,
        'comment_author_email' => wp_get_current_user()->user_email,
        'comment_content' => $comment,
        'comment_type' => 'review',
        'comment_approved' => 1,
        'user_id' => wp_get_current_user()->ID,
    );

    $product_id = intval($_POST['comment_post_ID']);
    $product = wc_get_product($product_id);

    $comment_id = wp_insert_comment($comment_data);

    error_log('rating: ' . intval($_POST['rating']));

    if ( $comment_id ) {
        error_log('comment_id: '. $comment_id);
        update_comment_meta($comment_id, 'rating', intval($_POST['rating']), true);

        $comment_hid = get_comment_hash($comment_id);
        error_log('comment_hid: '. $comment_hid);

        wp_send_json_success(
            array(
                //'comment_id' => $comment_id,
                'comment_hid' => get_comment_hash($comment_id),
                'review_html' => return_template_part('template-parts/single-document/singola-recensione', null, array('review' => get_comment($comment_id), 'prefix' => 'my_'))
            )
        );
    } else {
        wp_send_json_error(array('message' => 'Failed to submit review.'));
    }
}
add_action('wp_ajax_submit_review', 'handle_ajax_review_submission');
add_action('wp_ajax_nopriv_submit_review', 'handle_ajax_review_submission');

// DI REGOLA INUTILIZZATA
function reload_comments() {
    if ( ! isset($_POST['post_id']) ) {
        wp_send_json_error(array('message' => 'Invalid data.'));
    }

    error_log('reload_comments');
    // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'reload_comments_nonce')) {
    //     wp_send_json_error(array('message' => 'Nonce verification failed.'));
    // }

    error_log('post_id: ' . $_POST['post_id']);

    $post_id = intval($_POST['post_id']);
    $comments = get_comments(array('post_id' => $post_id));

    ob_start();
    get_template_part("template-parts/single-document/recensioni-documento", null, array('recensioni' => $comments));
    $comments_html = ob_get_clean();

    wp_send_json_success(array('comments_html' => $comments_html));
}
// add_action('wp_ajax_reload_comments', 'reload_comments');
// add_action('wp_ajax_nopriv_reload_comments', 'reload_comments');

function get_product_review_info($product_id) {
    $comments = get_comments(array(
        'post_id' => $product_id,
        'type'    => 'review',
        'status'  => 'approve'
    ));

    if (empty($comments)) {
        return array(
            'average_rating' => 0,
            'total_reviews' => 0
        );
    }

    $total_rating = 0;
    foreach ($comments as $comment) {
        $rating = get_comment_meta($comment->comment_ID, 'rating', true);
        $total_rating += intval($rating);
    }

    $average_rating = $total_rating / count($comments);

    

    return array(
        'average_rating' => $average_rating,
        'total_reviews' => count($comments)
    );
}

function handle_like_dislike() {

    error_log('handle_like_dislike');
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in.'));
    }

    if (!isset($_POST['comment_id']) || !isset($_POST['action_type'])) {
        wp_send_json_error(array('message' => 'Invalid data.'));
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'like_dislike_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
    }

    $comment_hid = sanitize_text_field($_POST['comment_id']);
    $comment_id = get_comment_id_by_hash($comment_hid);
    if (!$comment_id) {
        wp_send_json_error(array('message' => 'Comment not found.'));
    }
    $user_id = get_current_user_id();
    $action_type = sanitize_text_field($_POST['action_type']);

    $likes_dislikes = get_comment_meta($comment_id, 'likes_dislikes', true);
    if (!$likes_dislikes) {
        $likes_dislikes = array();
    }

    if(array_key_exists($user_id, $likes_dislikes)) {
        if($likes_dislikes[$user_id] === $action_type) {
            unset($likes_dislikes[$user_id]);
            $action_type = null;
        } else {
            $likes_dislikes[$user_id] = $action_type;
        }
    } else {
        $likes_dislikes[$user_id] = $action_type;
    }

    update_comment_meta($comment_id, 'likes_dislikes', $likes_dislikes);


    $likes_count = 0;
    $dislikes_count = 0;

    foreach ($likes_dislikes as $action) {
        if ($action === 'like') {
            $likes_count++;
        } elseif ($action === 'dislike') {
            $dislikes_count++;
        }
    }

    $response = array(
        'likes' => $likes_count,
        'dislikes' => $dislikes_count,
        'current_user' => $action_type,
  
    );



    wp_send_json_success($response);
}
add_action('wp_ajax_handle_like_dislike', 'handle_like_dislike');
add_action('wp_ajax_nopriv_handle_like_dislike', 'handle_like_dislike');

function display_likes_dislikes_in_admin($comment) {
    $comment_id = $comment->comment_ID;
    $likes_dislikes = get_comment_meta($comment_id, 'likes_dislikes', true);

    $users_liked = [];
    $users_disliked = [];

    if ($likes_dislikes) {
        foreach ($likes_dislikes as $user_id => $action) {
            $user_info = get_userdata($user_id);
            if ($action === 'like') {
                $users_liked[] = $user_info->user_login;
            } elseif ($action === 'dislike') {
                $users_disliked[] = $user_info->user_login;
            }
        }
    }

    echo '<h3>Likes and Dislikes</h3>';
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>Likes</th><th>Dislikes</th></tr></thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>' . implode(', ', $users_liked) . '</td>';
    echo '<td>' . implode(', ', $users_disliked) . '</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
}
add_action('add_meta_boxes_comment', function() {
    add_meta_box('comment_likes_dislikes', 'Likes and Dislikes', 'display_likes_dislikes_in_admin', 'comment', 'normal', 'high');
});

function handle_report_review() {

    check_ajax_referer('report_review_nonce', 'nonce');

    if (!is_user_logged_in()) {
       wp_send_json_error(array('message' => 'User not logged in.'));
    }

    if (!isset($_POST['review_id']) || !isset($_POST['reason'])) {
        wp_send_json_error(array('message' => 'Invalid data.'));
    }
 
    $review_hid = sanitize_text_field($_POST['review_id']);
    $review_id = get_comment_id_by_hash($review_hid);
    if (!$review_id) {
        wp_send_json_error(array('message' => 'Comment not found.'));
    }

    $user_id = get_current_user_id();
    $reason = sanitize_text_field($_POST['reason']);
    if(!valida_stringa($reason, 1, 500, '/^[a-zA-Z0-9\s.,;:!?\'"()àèéìòùÀÈÉÌÒÙ\-]+$/')) {
        wp_send_json_error(array('message' => 'Invalid reason.'));
    }

    $reports = get_comment_meta($review_id, 'review_reports', true);
    if (!$reports) {
        $reports = array();
    }

    // Controlla se l'utente ha già segnalato questa recensione
    if (array_key_exists($user_id, $reports)) {
        wp_send_json_error(array('message' => 'You have already reported this review.'));
    }

    $reports[$user_id] = $reason;
    update_comment_meta($review_id, 'review_reports', $reports);

    // Ottieni le informazioni necessarie
    $post_id = get_comment($review_id)->comment_post_ID;
    $post = get_post($post_id);
    $post_author = get_userdata($post->post_author);
    $review_author = get_userdata(get_comment($review_id)->user_id);
    $reporting_user = get_userdata($user_id);

    // Prepara l'email
    $subject = 'Nuova segnalazione recensione';
    
    // Includi il template dell'email
    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/email-report-review.php');
    $message = ob_get_clean();

    // Imposta gli header per email in formato HTML
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Ottieni gli amministratori
    $admin_email = get_users(array(
        'role' => 'administrator',
        'fields' => array('user_email')
    ));

    // Invia l'email ad ogni amministratore
    foreach ($admin_email as $admin) {
        error_log("Invio email a: ".$admin->user_email);
        wp_mail($admin->user_email, $subject, $message, $headers);
    }

    wp_send_json_success(array('message' => 'Segnalazione inviata con successo.'));
}
add_action('wp_ajax_segnala_recensione', 'handle_report_review');
add_action('wp_ajax_nopriv_segnala_recensione', 'handle_report_review');


function generate_comment_hash($comment_id) {
    $comment = get_comment($comment_id);
    if ($comment) {
        $salt = 'COMMENT_HASH_SALT'; 
        $hash = hash('sha256', $comment->comment_ID . $salt);
        return $hash;
    }
    return false;
}
add_action('wp_ajax_generate_comment_hash', 'generate_comment_hash');


function set_comment_hash($comment_id) {
    $hash = generate_comment_hash($comment_id);
    if ($hash) {
        update_comment_meta($comment_id, 'hid', $hash);
    }
}

add_action('wp_insert_comment', 'set_comment_hash', 10, 1);

add_action('wp_update_comment', 'set_comment_hash', 10, 1);


function get_comment_hash($comment_id) {
    $hash = get_comment_meta($comment_id, 'hid', true);
    return $hash;
}


function set_hash_for_existing_comments() {
    $comments = get_comments(array('number' => 0)); // Ottieni tutti i commenti
    foreach ($comments as $comment) {
        error_log('Setting hash for comment ID: ' . $comment->comment_ID);
        set_comment_hash($comment->comment_ID);
        error_log('Hash set for comment ID: ' . get_comment_hash($comment->comment_ID));
    }
}

add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        __('Set Comment Hash', 'your-text-domain'),
        __('Set Comment Hash', 'your-text-domain'),
        'manage_options',
        'set-comment-hash',
        function() {
            if (isset($_POST['set_hash_action']) && check_admin_referer('set_hash_nonce')) {
                set_hash_for_existing_comments();
                echo '<div class="updated"><p>' . __('Hashes have been set for all comments.', 'your-text-domain') . '</p></div>';
            }
            ?>
            <div class="wrap">
                <h1><?php _e('Set Comment Hash', 'your-text-domain'); ?></h1>
                <form method="post">
                    <?php wp_nonce_field('set_hash_nonce'); ?>
                    <p><?php _e('Click the button below to generate and set hashes for all existing comments.', 'your-text-domain'); ?></p>
                    <p><input type="submit" name="set_hash_action" class="button-primary" value="<?php _e('Set Hashes', 'your-text-domain'); ?>"></p>
                </form>
            </div>
            <?php
        }
    );
});

function get_comment_id_by_hash($hash) {

    $hash = sanitize_text_field($hash);

    global $wpdb;
    $comment_id = $wpdb->get_var($wpdb->prepare(
        "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = 'hid' AND meta_value = %s",
        $hash
    ));
    return $comment_id ? intval($comment_id) : null;
}

add_filter('comment_text', function($comment_text, $comment) {
    if (is_admin()) {
        $hash = get_comment_hash($comment->comment_ID);
        if ($hash) {
            $comment_text .= '<p><strong>Hash:</strong> ' . esc_html($hash) . '</p>';
        }
    }
    return $comment_text;
}, 10, 2);

function add_comment_hash_metabox($comment) {
    $hash = get_comment_hash($comment->comment_ID);
    ?>
    <div class="comment-meta-box">
        <p><strong><?php _e('Comment Hash:', 'your-text-domain'); ?></strong></p>
        <p><?php echo $hash ? esc_html($hash) : __('No hash available.', 'your-text-domain'); ?></p>
    </div>
    <?php
}

add_action('add_meta_boxes_comment', function() {
    add_meta_box(
        'comment_hash_metabox',
        __('Comment Hash', 'your-text-domain'),
        'add_comment_hash_metabox',
        'comment',
        'normal',
        'high'
    );
});
