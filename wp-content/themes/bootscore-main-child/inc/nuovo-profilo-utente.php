<?php

add_action('rest_api_init', function () {
    register_rest_route('profilo-utente', '/load-section', array(
        'methods' => 'GET',
        'callback' => function ($data) {
            //$section = sanitize_text_field($data['section']);
            $template_part = sanitize_text_field($data['template_part']);
            /*$nonce = sanitize_text_field($data['nonce']);
            if (!wp_verify_nonce($nonce, 'load-section')) {
                return new WP_Error('error', 'Invalid nonce', array('status' => 403));
            }*/
            try {
                ob_start();
                get_template_part($template_part, null, $data);
                return ob_get_clean();
            } catch (Exception $e) {
                return new WP_Error('error', $e->getMessage(), array('status' => 500));
            }

        }
    ));
});




function get_dati_menu_profilo_utente() {
    $menu_data = array(
        array(
            'sezione' => 'Generale',
            'mostra_nome_sezione' => false,
            'voci' => array(
                'carica_documento' => array(
                    'nome' => 'Carica documento',
                    'tipo' => 'menu_template_part',
                    'contenuto' => 'template-parts/nuovo-profilo-utente/sezione-generale',
                    'attivo' => true
                ),
                'amplia_la_tua_raccolta' => array(
                    'nome' => 'Amplia la tua raccolta',
                    'tipo' => 'menu_template_part',
                    'contenuto' => 'template-parts/profilo-utente/btn-amplia-raccolta',
                    'attivo' => false
                ),
            )
        ),
        array(
             'sezione' => 'Esplora',
             'mostra_nome_sezione' => true,
             'voci' => array(
                'il_mio_profilo' => array(
                    'nome' => 'Il mio profilo',
                    'slug' => 'il_mio_profilo',
                    'icona' => 'fas fa-user',
                    'tipo' => 'contenuto',
                    'contenuto' => 'template-parts/profilo-utente/sezione-utente',
                    'url' => '#section-profilo',
                    'attivo' => true,
                    'default' => true,
                    
                ),
                'il_mio_studio' => array(
                    'nome' => 'Il mio studio',
                    'slug' => 'il_mio_studio',
                    'icona' => 'fas fa-book-open',
                    'tipo' => 'contenuto',
                    'contenuto' => 'template-parts/profilo-utente/sezione-utente',
                    'url' => '#sezione-miei-documenti',
                    'attivo' => true
                ),
                'documenti_caricati' => array(
                    'nome' => 'Documenti caricati',
                    'slug' => 'documenti-caricati',
                    'icona' => 'fas fa-file-upload',
                    'tipo' => 'contenuto',
                    'contenuto' => 'template-parts/profilo-utente/sezione-documenti-caricati',
                    'attivo' => true
                ),
                'i_miei_guadagni' => array(
                    'nome' => 'I miei guadagni',
                    'slug' => 'i_miei_guadagni',
                    'icona' => 'fas fa-coins',
                    'tipo' => 'contenuto',
                    'contenuto' => 'template-parts/profilo-utente/sezione-guadagni',
                    'attivo' => true
                ),
                'i_miei_movimenti' => array(
                    'nome' => 'I miei movimenti',
                    'slug' => 'i_miei_movimenti',
                    'icona' => 'fas fa-exchange-alt',
                    'tipo' => 'contenuto',
                    'contenuto' => 'template-parts/profilo-utente/sezione-movimenti',
                    'attivo' => true
                ),
                'ricarica_punti' => array(
                    'nome' => 'Ricarica punti',
                    'slug' => 'ricarica_punti',
                    'icona' => 'fas fa-dollar-sign',
                    'tipo' => 'link_esterno',
                    'url' => "/wp1/compra-pacchetti-punti",
                    'attivo' => true
                ),
                'impostazioni' => array(
                    'nome' => 'Impostazioni',
                    'slug' => 'impostazioni',
                    'pagina' => 'impostazioni',
                    'icona' => 'fas fa-cog',
                    'tipo' => 'contenuto',
                    'contenuto' => 'template-parts/profilo-utente/sezione-impostazioni',
                    'attivo' => true
                ),
             )
            ),
        array(
            'sezione' => 'Raccolta',
            'mostra_nome_sezione' => true,
            'voci' => array(
                'il_mio_piano_di_studio' => array(
                    'nome' => 'Il mio piano di studio',
                    'slug' => 'il-mio-piano-di-studio',
                    'icona' => 'fas fa-folder',
                    'tipo' => 'link_esterno',
                    'url' => '#',
                    'attivo' => true
                ),
                'i_miei_documenti' => array(
                    'nome' => 'I miei documenti',
                    'slug' => 'i-miei-documenti',
                    'icona' => 'fas fa-folder',
                    'tipo' => 'link_esterno',
                    'url' => '#',
                    'attivo' => true
                ),
                'quiz_di_esercitazione' => array(
                    'nome' => 'Quiz di esercitazione',
                    'slug' => 'quiz-di-esercitazione',
                    'icona' => 'fas fa-folder',
                    'tipo' => 'link_esterno',
                    'url' => '#',
                    'attivo' => false
                ),
                'i_miei_libri' => array(
                    'nome' => 'I miei libri',
                    'slug' => 'i-miei-libri',
                    'icona' => 'fas fa-folder',
                    'tipo' => 'link_esterno',
                    'url' => '#',
                    'attivo' => false
                ),
            )
        )
            );
    return $menu_data;
}


function enqueue_profilo_utente_scripts() {
    if (is_page('nuovo-profilo-utente')) {
        wp_enqueue_script('nuovo-profilo-utente-script', get_stylesheet_directory_uri() . '/assets/js/nuovo-profilo-utente.js', array('jquery'), null, true);

        wp_localize_script('nuovo-profilo-utente-script', 'env_nuovo_profilo_utente', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'menu_data' => json_encode(get_dati_menu_profilo_utente()),
            'nonce' => wp_create_nonce('load-section'),
            'base_url' => site_url()
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_profilo_utente_scripts');
