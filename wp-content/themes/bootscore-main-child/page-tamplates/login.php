<?php

/**
 * Template Name: Login
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();

if( is_user_logged_in() && !current_user_can('administrator') ) {
    echo '<script>window.location.href="'.PROFILO_UTENTE_PAGE.'";</script>';
    //wp_redirect( PROFILO_UTENTE_PAGE);
    //exit;
}

?>
<div id="content" class="login-page">
    
    <div class="container container-login position-relative mt-5 mb-5">
        <form class="access-main-wrapper" name="loginform" id="loginform"
            action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
            <div class="row variable-gutters justify-content-center pt-4 pt-xl-5">
                <div class="col-lg-5 access-mobile-bg">
                    <div class="access-login">
                        <?php get_template_part('template-parts/login-signin/header'); ?>

                        <div class="login-alerts">
                            <?php if (isset($_GET['login']) && $_GET['login'] == 'failed') : ?>
                                <script>
                                    showCustomAlert("Accesso non riuscito", "Username o password errati. Riprova inserendo le credenziali corrette!", "bg-danger btn-danger");
                                </script>
                            <?php elseif (isset($_GET['login']) && $_GET['login'] == 'empty') : ?>
                                <script>
                                    showCustomAlert("Accesso non riuscito", "Inserisci username e password!", "bg-danger btn-danger");
                                </script>
                            <?php elseif (isset($_GET['login']) && $_GET['login'] == 'false') : ?>
                                <script>
                                    showCustomAlert("Logout effettuato", "Hai effettuato correttamente il logout! Grazie per essere utente di MineDocs!", "bg-success btn-success");
                                </script>
                            <?php endif; ?>
                        </div>
                       
                        <div class="access-login-form">
                            <?php get_template_part('template-parts/login-signin/signin-form'); ?>
                            <?php get_template_part('template-parts/login-signin/login-form'); ?>
                            <?php get_template_part('template-parts/login-signin/google-apple-access'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
get_footer();