<?php
/**
 * Gestione dei template email di WordPress
 */

// Modifica il template dell'email di reset password
function custom_retrieve_password_message($message, $key, $user_login, $user_data) {
    error_log("custom_retrieve_password_message");
    $user = get_user_by('login', $user_login);
    $reset_link = network_site_url("reset-password/?key=$key&login=" . rawurlencode($user_login), 'login');
    
    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/email-reset-password.php');
    return ob_get_clean();
}
add_filter('retrieve_password_message', 'custom_retrieve_password_message', 10, 4);

// Modifica il template dell'email di cambio password
function custom_password_change_email($pass_change_email, $user, $userdata) {
    error_log("custom_password_change_email");
    $new_pass = $pass_change_email['message'];
    
    ob_start();
    include(get_stylesheet_directory() . '/inc/email-templates/email-password-changed.php');
    $pass_change_email['message'] = ob_get_clean();
    
    return $pass_change_email;
}
add_filter('password_change_email', 'custom_password_change_email', 10, 3);

// Modifica il template dell'email di nuovo utente
function custom_new_user_notification_email($wp_new_user_notification_email, $user, $blogname) {
    $user_name = get_user_meta($user->ID, 'first_name', true) ?: $user->display_name;
    $subject = esc_html($blogname) . ' - Benvenuto!';
    
    $body = '
        <p>Ciao ' . esc_html($user_name) . ',</p>

        <p>Benvenuto su ' . esc_html($blogname) . '!</p>

        <div class="alert alert-success">
            <p>Il tuo account è stato creato con successo.</p>
        </div>

        <p>Per accedere al tuo account, visita il nostro sito:</p>

        <p style="text-align: center;">
            <a href="' . esc_url(home_url('/login/')) . '" class="button">Accedi al tuo Account</a>
        </p>

        <p>Se hai bisogno di assistenza, non esitare a contattarci.</p>
    ';

    ob_start();
    get_template_part('inc/email-templates/minedocs-email-header', null, array(
        'subject' => $subject,
        'body' => $body
    ));
    
    $wp_new_user_notification_email['message'] = ob_get_clean();
    $wp_new_user_notification_email['subject'] = $subject;
    
    return $wp_new_user_notification_email;
}
add_filter('wp_new_user_notification_email', 'custom_new_user_notification_email', 10, 3);

// Modifica il template dell'email di commento
function custom_comment_moderation_email($notify_message, $comment_id) {
    $comment = get_comment($comment_id);
    $post = get_post($comment->comment_post_ID);
    $subject = esc_html(get_bloginfo('name')) . ' - Nuovo Commento da Moderare';
    
    $body = '
        <p>Un nuovo commento è stato pubblicato sul post "' . esc_html($post->post_title) . '".</p>

        <div class="alert alert-info">
            <p>Il commento richiede la tua moderazione.</p>
        </div>

        <h3>Dettagli del Commento</h3>
        <table class="table">
            <tr>
                <th>Autore</th>
                <td>' . esc_html($comment->comment_author) . '</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>' . esc_html($comment->comment_author_email) . '</td>
            </tr>
            <tr>
                <th>Commento</th>
                <td>' . esc_html($comment->comment_content) . '</td>
            </tr>
        </table>

        <p style="text-align: center;">
            <a href="' . esc_url(get_edit_comment_link($comment_id)) . '" class="button">Modera Commento</a>
        </p>
    ';

    ob_start();
    get_template_part('inc/email-templates/minedocs-email-header', null, array(
        'subject' => $subject,
        'body' => $body
    ));
    
    return ob_get_clean();
}
add_filter('comment_moderation_email', 'custom_comment_moderation_email', 10, 2);

// Modifica il template dell'email di notifica commento
function custom_comment_notification_email($notify_message, $comment_id) {
    $comment = get_comment($comment_id);
    $post = get_post($comment->comment_post_ID);
    $subject = esc_html(get_bloginfo('name')) . ' - Nuovo Commento';
    
    $body = '
        <p>Un nuovo commento è stato pubblicato sul tuo post "' . esc_html($post->post_title) . '".</p>

        <div class="alert alert-info">
            <p>Puoi visualizzare e rispondere al commento dal tuo pannello di amministrazione.</p>
        </div>

        <h3>Dettagli del Commento</h3>
        <table class="table">
            <tr>
                <th>Autore</th>
                <td>' . esc_html($comment->comment_author) . '</td>
            </tr>
            <th>Commento</th>
            <td>' . esc_html($comment->comment_content) . '</td>
        </table>

        <p style="text-align: center;">
            <a href="' . esc_url(get_edit_comment_link($comment_id)) . '" class="button">Visualizza Commento</a>
        </p>
    ';

    ob_start();
    get_template_part('inc/email-templates/minedocs-email-header', null, array(
        'subject' => $subject,
        'body' => $body
    ));
    
    return ob_get_clean();
}
add_filter('comment_notification_email', 'custom_comment_notification_email', 10, 2); 