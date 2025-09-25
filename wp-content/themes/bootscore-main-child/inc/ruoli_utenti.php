<?php

function crea_ruolo_iubenda_manager() {
    if (!get_role('iubenda_manager')) {
        add_role('iubenda_manager', 'Iubenda Manager', [
            'read' => true,                           // Capacità fondamentale per accesso al dashboard
            'edit_posts' => true,
            'edit_others_posts' => true,
            'edit_published_posts' => true,
            'edit_private_posts' => true,
            'edit_pages' => true,
            'edit_others_pages' => true,
            'edit_published_pages' => true,
            'show_admin_bar' => true,
        ]);
    }
}
add_action('init', 'crea_ruolo_iubenda_manager');

// Modifica la funzione per concedere manage_options sempre per iubenda_manager
function consenti_accesso_iubenda_solo_per_ruolo($allcaps, $caps, $args, $user) {
    if (in_array('iubenda_manager', $user->roles)) {
        // Concede manage_options sempre per questo ruolo
        $allcaps['manage_options'] = true;
    }
    return $allcaps;
}
add_filter('user_has_cap', 'consenti_accesso_iubenda_solo_per_ruolo', 10, 4);

// Aggiungi questa funzione per permettere l'accesso al dashboard per iubenda_manager
function consenti_accesso_dashboard_iubenda_manager($allcaps, $caps, $args, $user) {
    if (in_array('iubenda_manager', $user->roles)) {
        // Aggiungi le capacità necessarie per l'accesso al dashboard
        $allcaps['view_admin_dashboard'] = true;
        $allcaps['manage_woocommerce'] = false; // Non dare accesso completo a WooCommerce
    }
    return $allcaps;
}
add_filter('user_has_cap', 'consenti_accesso_dashboard_iubenda_manager', 10, 4);

// Nascondi pagine specifiche per il ruolo iubenda_manager
function nascondi_pagine_iubenda_manager() {
    if (current_user_can('iubenda_manager') && !current_user_can('administrator')) {
        // Rimuovi le pagine transaction-logs e gestione-punti
        remove_menu_page('transaction-logs');
        remove_menu_page('gestione-punti');
        
        // Se sono submenu, prova anche questi metodi
        remove_submenu_page('admin.php', 'transaction-logs');
        remove_submenu_page('admin.php', 'gestione-punti');
        
        // Rimuovi anche da eventuali menu personalizzati
        global $menu, $submenu;
        
        // Rimuovi dai menu principali
        foreach ($menu as $key => $item) {
            if (isset($item[2]) && in_array($item[2], ['transaction-logs', 'gestione-punti'])) {
                unset($menu[$key]);
            }
        }
        
        // Rimuovi dai submenu
        if (isset($submenu['admin.php'])) {
            foreach ($submenu['admin.php'] as $key => $item) {
                if (isset($item[2]) && in_array($item[2], ['transaction-logs', 'gestione-punti'])) {
                    unset($submenu['admin.php'][$key]);
                }
            }
        }
    }
}
add_action('admin_init', 'nascondi_pagine_iubenda_manager');




