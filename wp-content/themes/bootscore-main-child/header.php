<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Definizione costanti
/*
define('MINEDOCS_LOGO', get_template_directory_uri() . '/assets/img/logo.png');
define('CARICAMENTO_DOCUMENTO_PAGE', home_url('/carica'));
define('DIVENTA_VENDITORE', home_url('/diventa-venditore'));
define('PIANI_PRO_PAGE', home_url('/piani-pro'));
define('RICERCA_PAGE', home_url('/ricerca'));
define('PROFILO_UTENTE_PAGE', home_url('/profilo'));
define('PROFILO_UTENTE_DOCUMENTI_CARICATI', home_url('/profilo/documenti-caricati'));
define('PROFILO_UTENTE_DOCUMENTI_ACQUISTATI', home_url('/profilo/documenti-acquistati'));
define('PROFILO_UTENTE_GUADAGNI', home_url('/profilo/guadagni'));
define('PROFILO_UTENTE_MOVIMENTI', home_url('/profilo/movimenti'));
define('PROFILO_UTENTE_VENDITE', home_url('/profilo/vendite'));
define('PROFILO_UTENTE_IMPOSTAZIONI', home_url('/profilo/impostazioni'));
define('LOGIN_PAGE', home_url('/login'));
define('CHI_SIAMO_PAGE', home_url('/chi-siamo'));
*/


$sistema_blu = get_sistema_punti('blu');
$icon_blu = $sistema_blu->get_icon();
$punti_blu = $sistema_blu->ottieni_totale_punti(get_current_user_id());

$sistema_pro = get_sistema_punti('pro');
$icon_pro = $sistema_pro->get_icon();
$punti_pro = $sistema_pro->ottieni_totale_punti(get_current_user_id());

if (is_user_logged_in()) {
    $numero_richieste_fattura = get_number_of_orders_with_invoice_request();
} ?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>

<script type="text/javascript">
var _iub = _iub || [];
_iub.csConfiguration = {"askConsentAtCookiePolicyUpdate":true,"googleAdditionalConsentMode": true, "enableFadp":true,"enableLgpd":true,"fadpApplies":true,"floatingPreferencesButtonCaptionColor":"#FFFFFF","floatingPreferencesButtonColor":"#0099CC","floatingPreferencesButtonDisplay":"bottom-right","floatingPreferencesButtonZIndex":1099,"perPurposeConsent":true,"preferenceCookie":{"expireAfter":180},"reloadOnConsent":true,"siteId":3938705,"storage":{"useSiteId":true},"usPreferencesWidgetDisplay":"inline-center","cookiePolicyId":61111609,"lang":"it","banner":{"acceptButtonCaptionColor":"#FFFFFF","acceptButtonColor":"#0073CE","acceptButtonDisplay":true,"backgroundColor":"#FFFFFF","closeButtonDisplay":false,"customizeButtonCaptionColor":"#4D4D4D","customizeButtonColor":"#DADADA","customizeButtonDisplay":true,"explicitWithdrawal":true,"listPurposes":true,"ownerName":"minedocs.it","position":"float-bottom-center","rejectButtonCaptionColor":"#FFFFFF","rejectButtonColor":"#0073CE","rejectButtonDisplay":true,"showTitle":false,"showTotalNumberOfProviders":true,"textColor":"#000000"}};
</script>
<script type="text/javascript" src="https://cs.iubenda.com/autoblocking/3938705.js"></script>
<script type="text/javascript" src="//cdn.iubenda.com/cs/iubenda_cs.js" charset="UTF-8" async></script>



    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php wp_body_open(); ?>
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="bootstrap" viewBox="0 0 118 94">
            <title>Bootstrap</title>
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M24.509 0c-6.733 0-11.715 5.893-11.492 12.284.214 6.14-.064 14.092-2.066 20.577C8.943 39.365 5.547 43.485 0 44.014v5.972c5.547.529 8.943 4.649 10.951 11.153 2.002 6.485 2.28 14.437 2.066 20.577C12.794 88.106 17.776 94 24.51 94H93.5c6.733 0 11.714-5.893 11.491-12.284-.214-6.14.064-14.092 2.066-20.577 2.009-6.504 5.396-10.624 10.943-11.153v-5.972c-5.547-.529-8.934-4.649-10.943-11.153-2.002-6.484-2.28-14.437-2.066-20.577C105.214 5.894 100.233 0 93.5 0H24.508zM80 57.863C80 66.663 73.436 72 62.543 72H44a2 2 0 01-2-2V24a2 2 0 012-2h18.437c9.083 0 15.044 4.92 15.044 12.474 0 5.302-4.01 10.049-9.119 10.88v.277C75.317 46.394 80 51.21 80 57.863zM60.521 28.34H49.948v14.934h8.905c6.884 0 10.68-2.772 10.68-7.727 0-4.643-3.264-7.207-9.012-7.207zM49.948 49.2v16.458H60.91c7.167 0 10.964-2.876 10.964-8.281 0-5.406-3.903-8.178-11.425-8.178H49.948z">
            </path>
        </symbol>
        <symbol id="facebook" viewBox="0 0 16 16">
            <path
                d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z">
            </path>
        </symbol>
        <symbol id="instagram" viewBox="0 0 16 16">
            <path
                d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92-.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z">
            </path>
        </symbol>
        <symbol id="twitter" viewBox="0 0 16 16">
            <path
                d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z">
            </path>
        </symbol>
        <symbol id="tiktok" viewBox="0 0 24 24">
            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
        </symbol>
    </svg>

    <header class="shadow-sm fixed-top header-container">
        <div class="container-fluid">
            <div class="row align-items-center py-2">
                <!-- Logo -->
                <div class="col-auto">
                    <a class="navbar-brand fw-bold" href="<?php echo home_url(); ?>">
                        <img class="logo-header" src="<?php echo MINEDOCS_LOGO; ?>" alt="Logo">
                    </a>
                </div>

                <!-- Menu e Search (Centro) -->
                <div class="col d-none d-lg-block">
                    <div class="d-flex align-items-center justify-content-between">
                        <!-- Menu -->
                        <nav class="navbar navbar-expand-lg p-0" style="white-space: nowrap; box-shadow: none !important; background-color: transparent !important;">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" style="white-space: nowrap;" href="#" id="studiaDropdown" role="button" data-bs-toggle="dropdown">
                                        Studia
                                    </a>
                                    <ul class="dropdown-menu p-3" aria-labelledby="studiaDropdown" style="width: 16rem;">
                                        <li><a class="dropdown-item" href="<?php echo RICERCA_PAGE; ?>">Cerca un documento</a></li>
                                        <li><a class="dropdown-item" href="<?php echo STUDIA_CON_AI_PAGE; ?>">Studia con l'AI</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" style="white-space: nowrap;" href="#" id="chisiamoDropdown" role="button" data-bs-toggle="dropdown">
                                        Vendi
                                    </a>
                                    <ul class="dropdown-menu p-3" aria-labelledby="chisiamoDropdown" style="width: 16rem;">
                                        <li><a class="dropdown-item" href="<?php echo DIVENTA_VENDITORE; ?>">Diventa venditore</a></li>
                                        <li><a class="dropdown-item" href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>">Carica un documento</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" style="white-space: nowrap;" href="<?php echo PIANI_PRO_PAGE; ?>">Piani <span class="pro">Pro</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" style="white-space: nowrap;" href="<?php echo CHI_SIAMO_PAGE; ?>">Chi Siamo</a>
                                </li>
                            </ul>
                        </nav>

                        <!-- Search -->
                        <div id="search-container-header" class="flex-grow-1 mx-3 "></div>
                    </div>
                </div>

                <!-- Auth Button e Carrello (Destra) -->
                <div class="col-auto ms-auto">
                    <div class="d-flex align-items-center">
                        <?php if (is_user_logged_in()) { ?>
                            <!-- Punti -->
                            <div class="d-none d-lg-block me-2">
                                <?php get_template_part('template-parts/commons/box-points', null); ?>
                            </div>

                            <!-- Profilo -->
                            <div class="dropdown d-none d-lg-block me-2">
                                <a href="<?php echo PROFILO_UTENTE_PAGE; ?>" class="bottom-nav-item" id="dropdownUser1" data-bs-toggle="dropdown">
                                    <img src="<?php echo get_user_avatar_url(get_current_user_id()) ?>" alt="mdo" width="30" height="30" class="rounded-circle">
                                    <span>Profilo</span>
                                </a>
                                <!-- <button class="btn btn-primary" id="dropdownUser1" data-bs-toggle="dropdown">
                                    <img src="<?php echo get_user_avatar_url(get_current_user_id()) ?>" alt="mdo" width="26" height="26" class="rounded-circle">
                                </button> -->
                                <div class="dropdown-menu text-small" aria-labelledby="dropdownUser1">
                                    <div class="px-3 py-2">
                                        <p class="mb-0">Ciao,
                                            <?php echo wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name; ?>!
                                        </p>
                                        <small class="text-muted"><?php echo wp_get_current_user()->user_email; ?></small>
                                    </div>
                                    <hr class="dropdown-divider">
                                        <?php get_template_part('template-parts/commons/box-points', null); ?>
                                    <hr class="dropdown-divider">
                                    <ul class="list-unstyled mb-0 text-start">
                                        <li><a class="dropdown-item" href="<?php echo PROFILO_UTENTE_PAGE ?>">Il mio
                                                profilo</a></li>
                                        <li><a class="dropdown-item"
                                                href="<?php echo PROFILO_UTENTE_DOCUMENTI_CARICATI ?>">Documenti
                                                caricati</a></li>
                                        <li><a class="dropdown-item"
                                                href="<?php echo PROFILO_UTENTE_DOCUMENTI_ACQUISTATI ?>">Documenti
                                                acquistati</a></li>
                                        <li><a class="dropdown-item" href="<?php echo PROFILO_UTENTE_GUADAGNI ?>">I tuoi
                                                guadagni</a></li>
                                        <li><a class="dropdown-item" href="<?php echo PROFILO_UTENTE_MOVIMENTI ?>">I tuoi
                                                movimenti</a></li>
                                        <li><a class="dropdown-item" href="<?php echo PROFILO_UTENTE_VENDITE ?>">Le tue
                                                vendite <?php if ($numero_richieste_fattura > 0) { ?> <span class="badge badge-pill badge-primary"><?php echo $numero_richieste_fattura; ?></span> <?php } ?></a></li>
                                        <li><a class="dropdown-item"
                                                href="<?php echo PROFILO_UTENTE_IMPOSTAZIONI ?>">Impostazioni</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="<?php echo wp_logout_url(home_url()); ?>">Esci</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php } else { ?>
                            <a href="<?php echo LOGIN_PAGE; ?>" class="btn btn-primary d-none d-lg-block me-2">Accedi o registrati</a>
                        <?php } ?>

                        <!-- Carrello -->
                        <a href="#" class="bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-cart">
                            <div class="position-relative d-inline-block">
                                <!-- <img src="<?php /* echo get_stylesheet_directory_uri(); */ ?>/assets/icons/cart-1-white.svg" alt="Carrello" width="30" height="30" /> -->
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/Icone_Minedocs_carrello.svg" alt="Carrello" width="35" height="35" />

                                <?php if (WC()->cart->get_cart_contents_count() > 0): ?>
                                    <span class="badge bg-primary text-white position-absolute top-0 start-100 translate-middle rounded-pill" style="font-size: 0.75rem; padding: 0.25em 0.4em;">
                                        <?php echo WC()->cart->get_cart_contents_count(); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span>Carrello</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchContainer = document.getElementById('search-container-header');

            if (searchContainer && document.querySelectorAll('#search-form').length === 0) {
                const searchFormHtml = `
                <form id="search-form" role="search" class="search-form-header" method="get" action="<?php echo esc_url(RICERCA_PAGE); ?>">
                    <div class="search-bar-header d-flex align-items-center rounded-lg overflow-hidden">
                        <input type="text" 
                               class="form-control-header search-input-header" 
                               placeholder="Cerca documenti, corsi o libri" 
                               aria-label="Ricerca" 
                               name="search">
                    </div>
                    <div class="search-suggestions"></div>
                </form>
            `;
                searchContainer.innerHTML = searchFormHtml;

                // Funzione per mostrare/nascondere in base alla larghezza
                function toggleSearchVisibility() {
                    const width = searchContainer.parentElement.offsetWidth;
                    searchContainer.style.display = (width < 200) ? 'none' : 'block';
                }

                // Iniziale
                toggleSearchVisibility();

                // Al resize della finestra
                window.addEventListener('resize', toggleSearchVisibility);
            }
        });
    </script>


    <!-- Bottom Navigation Bar per Mobile -->
    <nav class="bottom-nav d-lg-none">
        <a href="#" class="bottom-nav-item hamburger-icon principal-menu-offcanvas-icon" id="hamburger-menu" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
            <span>Menu</span>
        </a>

        <div class="minedocs-mobile-menu" id="offcanvas-menu">
            <div class="minedocs-mobile-menu-header">
                <div class="minedocs-mobile-menu-logo">
                    <img src="<?php echo MINEDOCS_LOGO; ?>" alt="Logo MineDocs" class="minedocs-mobile-menu-logo-img">
                </div>
                <button class="minedocs-mobile-menu-close" aria-label="Chiudi menu">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="minedocs-mobile-menu-content">
                <div class="minedocs-mobile-menu-item">
                    <a href="#" class="minedocs-mobile-menu-link" data-toggle="submenu">
                        <i class="fas fa-graduation-cap minedocs-mobile-menu-icon"></i>
                        <span>Studia</span>
                        <i class="fas fa-chevron-down minedocs-mobile-menu-arrow"></i>
                    </a>
                    <div class="minedocs-mobile-submenu">
                        <a href="<?php echo RICERCA_PAGE; ?>" class="minedocs-mobile-submenu-link">
                            <i class="fas fa-search minedocs-mobile-submenu-icon"></i>
                            <span>Cerca un documento</span>
                        </a>
                        <a href="<?php echo STUDIA_CON_AI_PAGE; ?>" class="minedocs-mobile-submenu-link">
                            <i class="fas fa-robot minedocs-mobile-submenu-icon"></i>
                            <span>Studia con l'AI</span>
                        </a>
                    </div>
                </div>

                <div class="minedocs-mobile-menu-item">
                    <a href="#" class="minedocs-mobile-menu-link" data-toggle="submenu">
                        <i class="fas fa-store minedocs-mobile-menu-icon"></i>
                        <span>Vendi</span>
                        <i class="fas fa-chevron-down minedocs-mobile-menu-arrow"></i>
                    </a>
                    <div class="minedocs-mobile-submenu">
                        <a href="<?php echo DIVENTA_VENDITORE; ?>" class="minedocs-mobile-submenu-link">
                            <i class="fas fa-user-plus minedocs-mobile-submenu-icon"></i>
                            <span>Diventa venditore</span>
                        </a>
                        <a href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>" class="minedocs-mobile-submenu-link">
                            <i class="fas fa-upload minedocs-mobile-submenu-icon"></i>
                            <span>Carica un documento</span>
                        </a>
                    </div>
                </div>

                <div class="minedocs-mobile-menu-item">
                    <a href="<?php echo PIANI_PRO_PAGE; ?>" class="minedocs-mobile-menu-link">
                        <i class="fas fa-crown minedocs-mobile-menu-icon"></i>
                        <span>Piani <span class="pro">Pro</span></span>
                    </a>
                </div>

                <div class="minedocs-mobile-menu-item">
                    <a href="<?php echo CHI_SIAMO_PAGE; ?>" class="minedocs-mobile-menu-link">
                        <i class="fas fa-users minedocs-mobile-menu-icon"></i>
                        <span>Chi Siamo</span>
                    </a>
                </div>
            </div>
        </div>
        <script>
            document.querySelector('.principal-menu-offcanvas-icon').addEventListener('click', function() {
                const menu = document.getElementById('offcanvas-menu');
                if (menu.style.display === 'none' || menu.style.display === '') {
                    menu.style.display = 'block';
                    menu.style.opacity = 0;
                    menu.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        menu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        menu.style.opacity = 1;
                        menu.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    menu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    menu.style.opacity = 0;
                    menu.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        menu.style.display = 'none';
                    }, 300);
                }
            });

            document.querySelector('.minedocs-mobile-menu-close').addEventListener('click', function() {
                const menu = document.getElementById('offcanvas-menu');
                menu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                menu.style.opacity = 0;
                menu.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    menu.style.display = 'none';
                }, 300);
            });

            document.querySelectorAll('.minedocs-mobile-menu-link[data-toggle="submenu"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const submenu = this.nextElementSibling;
                    const arrow = this.querySelector('.minedocs-mobile-menu-arrow');
                    
                    if (submenu.style.display === 'none' || submenu.style.display === '') {
                        submenu.style.display = 'block';
                        submenu.style.opacity = 0;
                        submenu.style.transform = 'translateY(10px)';
                        arrow.style.transform = 'rotate(180deg)';
                        setTimeout(() => {
                            submenu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            submenu.style.opacity = 1;
                            submenu.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        submenu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        submenu.style.opacity = 0;
                        submenu.style.transform = 'translateY(10px)';
                        arrow.style.transform = 'rotate(0deg)';
                        setTimeout(() => {
                            submenu.style.display = 'none';
                        }, 300);
                    }
                });
            });
        </script>
        <a href="<?php echo home_url(); ?>" class="bottom-nav-item">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo RICERCA_PAGE; ?>" class="bottom-nav-item">
            <i class="fa-solid fa-search"></i>
            <span>Cerca</span>
        </a>
        <a href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>" class="bottom-nav-item">
            <i class="fa-solid fa-upload"></i>
            <span>Carica</span>
        </a>
        <?php if (is_user_logged_in()): ?>
            <!-- <a href="<?php echo PROFILO_UTENTE_PAGE; ?>" class="bottom-nav-item">
                <i class="fa-solid fa-user"></i>
                <span>Profilo</span>
            </a> -->



            <a href="#" class="bottom-nav-item hamburger-icon profilo-menu-offcanvas-icon" id="hamburger-menu-profilo" aria-label="Menu">
                <i class="fa-solid fa-user"></i>
                <span>Profilo</span>
            </a>

            <div class="offcanvas-menu" id="offcanvas-menu-profilo" style="display: none; position: absolute; bottom: 100%; left: 0; right: 0; height: calc(100vh - 100px); background: white; z-index: 1000; border: 1px solid var(--gray-200); overflow-y: auto;">
                <button class="close-menu-profilo" aria-label="Chiudi menu" style="background: none; border: none; font-size: 1.5rem; position: absolute; top: 50px; right: 20px; cursor: pointer;">
                    <i class="fa-solid fa-times"></i>
                </button>
                <script>
                    document.querySelector('.close-menu-profilo').addEventListener('click', function() {
                        const menu = document.getElementById('offcanvas-menu-profilo');
                        menu.style.display = 'none';
                    });
                </script>

                <div class="logo-container text-center" style="padding: 20px;">
                    <img src="<?php echo MINEDOCS_LOGO; ?>" alt="Logo MineDocs" class="img-fluid" style="max-width: 200px;">
                </div>

                <div class="offcanvas-item profilo-menu-offcanvas-item" style="padding: 20px; border-bottom: 1px solid var(--gray-200);">
                <div class="px-3 py-2">
                                        <p class="mb-0">Ciao,
                                            <?php echo wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name; ?>!
                                        </p>
                                        <small class="text-muted"><?php echo wp_get_current_user()->user_email; ?></small>
                                    </div>
                                    <hr class="dropdown-divider">    
                
                <!-- Punti -->
                    <div class="text-center me-2">
                        <?php get_template_part('template-parts/commons/box-points', null); ?>
                    </div>
                    <hr class="dropdown-divider">
                    <ul class="nav flex-column mb-3">
                        <li class="nav-item dropdown-item">
                            <a href="<?php echo PROFILO_UTENTE_PAGE; ?>" class="offcanvas-link profilo-menu-offcanvas-link" style="display: block; padding: 10px 0;">
                                <i class="fas fa-user"></i> Il mio profilo
                            </a>
                        </li>
                        <li class="nav-item dropdown-item">
                            <a href="<?php echo PROFILO_UTENTE_DOCUMENTI_CARICATI; ?>" class="offcanvas-link profilo-menu-offcanvas-link" style="display: block; padding: 10px 0;">
                                <i class="fas fa-upload"></i> Documenti caricati
                            </a>
                        </li>
                        <li class="nav-item dropdown-item">
                            <a href="<?php echo PROFILO_UTENTE_DOCUMENTI_ACQUISTATI; ?>" class="offcanvas-link profilo-menu-offcanvas-link" style="display: block; padding: 10px 0;">
                                <i class="fas fa-shopping-cart"></i> Documenti acquistati
                            </a>
                        </li>
                        <li class="nav-item dropdown-item">
                            <a href="<?php echo PROFILO_UTENTE_GUADAGNI; ?>" class="offcanvas-link profilo-menu-offcanvas-link" style="display: block; padding: 10px 0;">
                                <i class="fas fa-coins"></i> I miei guadagni
                            </a>
                        </li>
                        <li class="nav-item dropdown-item">
                            <a href="<?php echo PROFILO_UTENTE_MOVIMENTI; ?>" class="offcanvas-link profilo-menu-offcanvas-link" style="display: block; padding: 10px 0;">
                                <i class="fas fa-exchange-alt"></i> I miei movimenti
                            </a>
                        </li>
                        <li class="nav-item dropdown-item">
                            <a href="<?php echo PROFILO_UTENTE_VENDITE; ?>" class="offcanvas-link profilo-menu-offcanvas-link" style="display: block; padding: 10px 0;">
                                <i class="fas fa-comment-dollar"></i> Le mie vendite
                            </a>
                        </li>
                        <li class="nav-item dropdown-item">
                            <a href="<?php echo PROFILO_UTENTE_IMPOSTAZIONI; ?>" class="offcanvas-link profilo-menu-offcanvas-link" style="display: block; padding: 10px 0;">
                                <i class="fas fa-cog"></i> Impostazioni
                            </a>
                        </li>

                    </ul>
                    <hr class="dropdown-divider">
                    <?php

                    get_template_part('template-parts/profilo-utente/logout-button');

                    ?>
                </div>





            </div>
            <script>
                document.querySelector('.profilo-menu-offcanvas-icon').addEventListener('click', function() {
                    const menu = document.getElementById('offcanvas-menu-profilo');
                    if (menu.style.display === 'none' || menu.style.display === '') {
                        menu.style.display = 'block';
                        menu.style.opacity = 0;
                        menu.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            menu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            menu.style.opacity = 1;
                            menu.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        menu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        menu.style.opacity = 0;
                        menu.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            menu.style.display = 'none';
                        }, 300);
                    }
                });

                document.querySelectorAll('.profilo-menu-offcanvas-link').forEach(link => {
                    link.addEventListener('click', function() {
                        const submenu = this.nextElementSibling;
                        if (submenu) {
                            if (submenu.style.display === 'none' || submenu.style.display === '') {
                                submenu.style.display = 'block';
                                submenu.style.opacity = 0;
                                submenu.style.transform = 'translateY(10px)';
                                setTimeout(() => {
                                    submenu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                    submenu.style.opacity = 1;
                                    submenu.style.transform = 'translateY(0)';
                                }, 10);
                            } else {
                                submenu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                submenu.style.opacity = 0;
                                submenu.style.transform = 'translateY(10px)';
                                setTimeout(() => {
                                    submenu.style.display = 'none';
                                }, 300);
                            }
                        }
                    });
                });
            </script>










        <?php else: ?>
            <a href="<?php echo LOGIN_PAGE; ?>" class="bottom-nav-item">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>Accedi</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Offcanvas User and Cart -->
    <?php
    if (class_exists('WooCommerce')):
        get_template_part('template-parts/header/offcanvas', 'woocommerce');
    endif;
    ?>

    <style>
        .header-container {
            transition: all 0.3s ease;
            min-height: 100px !important;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-container.shrink {
            min-height: 70px !important;
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(10px) !important;
        }

        .search-input-header {
            border: 2px solid var(--gray-300);
            border-radius: var(--border-radius);
            padding: 0.5rem 1rem;
            transition: var(--transition);
            color: var(--gray-800);
            width: 100%;
        }

        .search-bar-header {
            border-radius: var(--border-radius-lg);
            padding: 0.5rem;
            box-shadow: none !important;
        }

        .search-form-header {
            width: 100%;
            margin: 0;
        }

        .logo-header {
            min-width: 200px !important;
            max-width: 400px !important;
            height: auto;
            transition: max-width 0.3s ease;
            /* Aggiunta dell'animazione */
        }

        .logo-header.shrink {
            max-width: 150px !important;
        }

        @media (max-width: 991.98px) {
            .navbar-nav {
                white-space: nowrap;
            }
        }

        .profilo-menu-offcanvas-link {
            color: var(--gray-800) !important;
        }

        .dropdown-divider {
            margin: 10px !important;
            border-top: 1px solid var(--gray-200) !important;
        }

    </style>

    <script>
        window.onscroll = function() {
            var header = document.querySelector('.header-container');
            var logo = document.querySelector('.logo-header');
            if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                header.classList.add('shrink');
                logo.classList.add('shrink');
            } else {
                header.classList.remove('shrink');
                logo.classList.remove('shrink');
            }
        };
    </script>


    <section class="minedocs-content"> <!-- minedocs-content - si chiude in footer.php -->