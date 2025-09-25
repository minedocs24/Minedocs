<?php
/**
 * Template per l'email di segnalazione recensioni
 */

// Verifica che le variabili necessarie siano definite
if (!isset($post_id) || !isset($post) || !isset($post_author) || !isset($review_author) || !isset($reporting_user) || !isset($reason)) {
    return;
}

$post_permalink = get_permalink($post_id);
$subject = esc_html(get_bloginfo('name')) . ' - Nuova Segnalazione Recensione';

$body = '
    <p>L\'utente con ID <strong>' . $reporting_user->ID . '</strong> ha segnalato la recensione con ID <strong>' . $review_id . '</strong> per il seguente motivo:</p>
    
    <div class="alert alert-danger">
        <blockquote>' . $reason . '</blockquote>
    </div>

    <h3>Dettagli della Segnalazione</h3>
    <table class="table">
        <tr>
            <th>ID Post</th>
            <td>' . $post_id . '</td>
        </tr>
        <tr>
            <th>Nome Post</th>
            <td>' . $post->post_title . '</td>
        </tr>
        <tr>
            <th>Link al Post</th>
            <td><a href="' . $post_permalink . '">' . $post_permalink . '</a></td>
        </tr>
        <tr>
            <th>ID Autore Post</th>
            <td>' . $post_author->ID . '</td>
        </tr>
        <tr>
            <th>Nome Autore Post</th>
            <td>' . $post_author->display_name . '</td>
        </tr>
        <tr>
            <th>Link Autore Post</th>
            <td><a href="' . get_author_posts_url($post_author->ID) . '">' . get_author_posts_url($post_author->ID) . '</a></td>
        </tr>
        <tr>
            <th>ID Recensione</th>
            <td>' . $review_id . '</td>
        </tr>
        <tr>
            <th>Testo Recensione</th>
            <td>' . get_comment($review_id)->comment_content . '</td>
        </tr>
        <tr>
            <th>ID Utente Autore Recensione</th>
            <td>' . $review_author->ID . '</td>
        </tr>
        <tr>
            <th>Nome Utente Autore Recensione</th>
            <td>' . $review_author->display_name . '</td>
        </tr>
        <tr>
            <th>Link Autore Recensione</th>
            <td><a href="' . get_author_posts_url($review_author->ID) . '">' . get_author_posts_url($review_author->ID) . '</a></td>
        </tr>
        <tr>
            <th>ID Utente che ha Segnalato</th>
            <td>' . $reporting_user->ID . '</td>
        </tr>
        <tr>
            <th>Nome Utente che ha Segnalato</th>
            <td>' . $reporting_user->display_name . '</td>
        </tr>
        <tr>
            <th>Link Utente che ha Segnalato</th>
            <td><a href="' . get_author_posts_url($reporting_user->ID) . '">' . get_author_posts_url($reporting_user->ID) . '</a></td>
        </tr>
    </table>

    <p>Puoi visualizzare la recensione segnalata al seguente link: <a href="' . $post_permalink . '">' . $post_permalink . '</a></p>
';

get_template_part('inc/email-templates/minedocs-email-header', null, array(
    'subject' => $subject,
    'body' => $body
)); 