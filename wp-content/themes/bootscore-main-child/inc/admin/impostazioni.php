<?php

$fields = [
    'FREE_FOR_ALREADY_PURCHASED' => [
        'label' => 'Free for Already Purchased',
        'type' => 'checkbox',
        'default' => false
    ],
    'PUNTI_BLU_PRIMA_RECENSIONE' => [
        'label' => 'Punti Blu Prima Recensione',
        'type' => 'number',
        'default' => 5
    ],
    'PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO' => [
        'label' => 'Punti Blu Caricamento Documento Condiviso',
        'type' => 'number',
        'default' => 20
    ],
    'PUNTI_BLU_AGGIORNAMENTO_DOCUMENTO_CONDIVISO' => [
        'label' => 'Punti Blu Aggiornamento Documento Condiviso',
        'type' => 'number',
        'default' => 10
    ],
    'SALDO_MINIMO_PRELEVABILE' => [
        'label' => 'Saldo Minimo Prelevabile',
        'type' => 'number',
        'default' => 20
    ],
    'VALORE_PUNTI_PRO' => [
        'label' => 'Valore Punti Pro',
        'type' => 'number',
        'default' => 0.086
    ],
    'PERCENTUALE_COMMISSIONE_VENDITA' => [
        'label' => 'Percentuale Commissione Vendita',
        'type' => 'number',
        'default' => 0.3
    ],
    'ABILITA_CAPTCHA' => [
        'label' => 'Abilita Captcha',
        'type' => 'checkbox',
        'default' => true
    ],
    'MOSTRA_FOLLOWERS' => [
        'label' => 'Mostra Followers',
        'type' => 'checkbox',
        'default' => false
    ],
    'APACHE_TIKA_SERVER_URL' => [
        'label' => 'Apache Tika Server URL',
        'type' => 'text',
        'default' => 'http://localhost:5000'
    ],
    'FLASK_ANALYSIS_API_URL' => [
        'label' => 'Flask Analysis API URL',
        'type' => 'text',
        'default' => 'http://localhost:4998/analizza-file'
    ],
    'FLASK_ANALYSIS_API_KEY' => [
        'label' => 'Flask Analysis API Key',
        'type' => 'text',
        'default' => '8\'w4VDl!+b/C:|p88G*c%6cb9'
    ],
    'SKU_ABBONAMENTO_30_GIORNI' => [
        'label' => 'SKU Abbonamento 30 Giorni',
        'type' => 'text',
        'default' => 'ABBPRO030'
    ],
    'SKU_ABBONAMENTO_90_GIORNI' => [
        'label' => 'SKU Abbonamento 90 Giorni',
        'type' => 'text',
        'default' => 'ABBPRO090'
    ],
    'SKU_ABBONAMENTO_365_GIORNI' => [
        'label' => 'SKU Abbonamento 365 Giorni',
        'type' => 'text',
        'default' => 'ABBPRO365'
    ],
    'AMOUNT_PUNTI_PRO_ABBPRO030' => [
        'label' => 'Amount Punti Pro ABBPRO030',
        'type' => 'number',
        'default' => 100
    ],
    'AMOUNT_PUNTI_PRO_ABBPRO090' => [
        'label' => 'Amount Punti Pro ABBPRO090',
        'type' => 'number',
        'default' => 400
    ],
    'AMOUNT_PUNTI_PRO_ABBPRO365' => [
        'label' => 'Amount Punti Pro ABBPRO365',
        'type' => 'number',
        'default' => 800
    ],
    'SKU_PUNTI_PRO_150' => [
        'label' => 'SKU Punti Pro 150',
        'type' => 'text',
        'default' => 'PUNTIPRO150'
    ],
    'SKU_PUNTI_PRO_500' => [
        'label' => 'SKU Punti Pro 500',
        'type' => 'text',
        'default' => 'PUNTIPRO500'
    ],
    'SKU_PUNTI_PRO_1000' => [
        'label' => 'SKU Punti Pro 1000',
        'type' => 'text',
        'default' => 'PUNTIPRO1000'
    ],
    'CUSTOM_ERROR_LOG' => [
        'label' => 'Custom Error Log',
        'type' => 'text',
        'default' => WP_CONTENT_DIR . '/custom_debug.log'
    ],
    'TABELLA_TRANSAZIONI' => [
        'label' => 'Tabella Transazioni',
        'type' => 'text',
        'default' => $wpdb->prefix . 'minedocs_transaction_log'
    ],
    'TABELLA_REGISTRO_PUNTI' => [
        'label' => 'Tabella Registro Punti',
        'type' => 'text',
        'default' => $wpdb->prefix . 'minedocs_points_register'
    ],
    'TABELLA_LOGIN_LOGS' => [
        'label' => 'Tabella Login Logs',
        'type' => 'text',
        'default' => $wpdb->prefix . 'minedocs_login_logs'
    ],
    'TABELLA_WALLET' => [
        'label' => 'Tabella Wallet',
        'type' => 'text',
        'default' => $wpdb->prefix . 'minedocs_wallet'
    ],
    'TABELLA_STUDIA_AI_JOBS' => [
        'label' => 'Tabella Studia AI Jobs',
        'type' => 'text',
        'default' => $wpdb->prefix . 'minedocs_studia_ai_jobs'
    ],
    'BLU_POINTS_EXPIRING_DATE' => [
        'label' => 'Blu Points Expiring Date',
        'type' => 'text',
        'default' => '2999-12-31'
    ],
    'USER_MAX_FAILED_LOGIN_ATTEMPTS' => [
        'label' => 'User Max Failed Login Attempts',
        'type' => 'number',
        'default' => 5
    ],
    'NUMERO_TENTATIVI_LOGIN_PRIMA_DI_CAPTCHA' => [
        'label' => 'Numero Tentativi Login Prima di Captcha',
        'type' => 'number',
        'default' => 3
    ],
    'USER_BLOCK_STAGE_1' => [
        'label' => 'User Block Stage 1',
        'type' => 'number',
        'default' => 15 * 60
    ],
    'USER_BLOCK_STAGE_2' => [
        'label' => 'User Block Stage 2',
        'type' => 'number',
        'default' => 60 * 60
    ],
    'USER_BLOCK_STAGE_3' => [
        'label' => 'User Block Stage 3',
        'type' => 'number',
        'default' => strtotime('2999-12-31') - time()
    ],
    'LOGIN_PAGE' => [
        'label' => 'Login Page',
        'type' => 'text',
        'default' => site_url('/login')
    ],
    'PROFILO_UTENTE_PAGE' => [
        'label' => 'Profilo Utente Page',
        'type' => 'text',
        'default' => site_url('/profilo-utente')
    ],
    'CARICAMENTO_DOCUMENTO_PAGE' => [
        'label' => 'Caricamento Documento Page',
        'type' => 'text',
        'default' => site_url('/upload-2')
    ],
    'PIANI_PRO_PAGE' => [
        'label' => 'Piani Pro Page',
        'type' => 'text',
        'default' => site_url('/pacchetti-premium')
    ],
    'PACCHETTI_PUNTI_PAGE' => [
        'label' => 'Pacchetti Punti Page',
        'type' => 'text',
        'default' => site_url('/compra-pacchetti-punti')
    ],
    'PRIVACY_POLICY_PAGE' => [
        'label' => 'Privacy Policy Page',
        'type' => 'text',
        'default' => site_url('/privacy-policy-2')
    ],
    'PROFILAZIONE_UTENTE_PAGE' => [
        'label' => 'Profilazione Utente Page',
        'type' => 'text',
        'default' => home_url('/registrazione-utente')
    ],
    'RICERCA_PAGE' => [
        'label' => 'Ricerca Page',
        'type' => 'text',
        'default' => site_url('/ricerca')
    ],
    'PROFILO_UTENTE_DOCUMENTI_CARICATI' => [
        'label' => 'Profilo Utente Documenti Caricati',
        'type' => 'text',
        'default' => site_url().'/profilo-utente-documenti-caricati'
    ],
    'PROFILO_UTENTE_DOCUMENTI_ACQUISTATI' => [
        'label' => 'Profilo Utente Documenti Acquistati',
        'type' => 'text',
        'default' => site_url().'/profilo-utente-documenti-acquistati'
    ],
    'PROFILO_UTENTE_GUADAGNI' => [
        'label' => 'Profilo Utente Guadagni',
        'type' => 'text',
        'default' => site_url().'/profilo-utente-guadagni'
    ],
    'PROFILO_UTENTE_MOVIMENTI' => [
        'label' => 'Profilo Utente Movimenti',
        'type' => 'text',
        'default' => site_url().'/profilo-utente-movimenti'
    ],
    'PROFILO_UTENTE_VENDITE' => [
        'label' => 'Profilo Utente Vendite',
        'type' => 'text',
        'default' => site_url().'/profilo-utente-vendite'
    ],
    'PROFILO_UTENTE_IMPOSTAZIONI' => [
        'label' => 'Profilo Utente Impostazioni',
        'type' => 'text',
        'default' => site_url().'/profilo-utente-impostazioni'
    ],
    'STUDIA_CON_AI_PAGE' => [
        'label' => 'Studia con AI Page',
        'type' => 'text',
        'default' => site_url().'/studia-con-ai'
    ],
    'PROFILO_UTENTE_GENERAZIONI_AI' => [
        'label' => 'Profilo Utente Generazioni AI',
        'type' => 'text',
        'default' => site_url().'/profilo-utente-generazioni-ai'
    ],
    'DIVENTA_VENDITORE' => [
        'label' => 'Diventa Venditore',
        'type' => 'text',
        'default' => site_url().'/diventa-venditore'
    ],
    'FAQ_PAGE' => [
        'label' => 'FAQ Page',
        'type' => 'text',
        'default' => site_url().'/faq'
    ],
    'CONTATTI_PAGE' => [
        'label' => 'Contatti Page',
        'type' => 'text',
        'default' => site_url().'/contatti'
    ],
    'UPLOAD_PAGE' => [
        'label' => 'Upload Page',
        'type' => 'text',
        'default' => site_url().'/upload-2'
    ],
    'CHI_SIAMO_PAGE' => [
        'label' => 'Chi Siamo Page',
        'type' => 'text',
        'default' => site_url().'/chi-siamo'
    ],
    'REGISTRAZIONE_PAGE' => [
        'label' => 'Registrazione Page',
        'type' => 'text',
        'default' => site_url().'/registrazione-utente'
    ],
    'TERMINI_E_CONDIZIONI_PAGE' => [
        'label' => 'Termini e Condizioni Page',
        'type' => 'text',
        'default' => site_url().'/termini-e-condizioni'
    ],
    'PATH_MODAL_COME_GUADAGNARE_PUNTI' => [
        'label' => 'Path Modal Come Guadagnare Punti',
        'type' => 'text',
        'default' => get_stylesheet_directory_uri() . '/modals/come_guadagnare_punti.php'
    ],
    'META_KEY_STATO_APPROVAZIONE_PRODOTTO' => [
        'label' => 'Meta Key Stato Approvazione Prodotto',
        'type' => 'text',
        'default' => '_stato_approvazione_prodotto'
    ],
    'PASSWORD_COMPLEXITY_MESSAGE' => [
        'label' => 'Password Complexity Message',
        'type' => 'text',
        'default' => 'La password deve contenere almeno 8 caratteri, di cui almeno una lettera maiuscola, una lettera minuscola, un numero e un carattere speciale.'
    ],
    'DEFAULT_THUMBNAIL' => [
        'label' => 'Default Thumbnail',
        'type' => 'text',
        'default' => get_stylesheet_directory_uri() . '/assets/img/documento/miniatura_default.webp'
    ],
    'MINEDOCS_LOGO' => [
        'label' => 'MineDocs Logo',
        'type' => 'text',
        'default' => get_stylesheet_directory_uri() . '/assets/img/logo/MineDocs_Logo.png'
    ],
    'GOOGLE_CLIENT_ID_LOGIN' => [
        'label' => 'Google Client ID Login',
        'type' => 'text',
        'default' => '738924129157-b8o9gt6uh8rd58987vrensensp3oor46.apps.googleusercontent.com'
    ],
    'GOOGLE_CLIENT_SECRET_LOGIN' => [
        'label' => 'Google Client Secret Login',
        'type' => 'text',
        'default' => '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'
    ],
    'GOOGLE_REDIRECT_URI_LOGIN' => [
        'label' => 'Google Redirect URI Login',
        'type' => 'text',
        'default' => ''
    ],
    'GOOGLE_RECAPTCHA_SITE_KEY_REGISTER' => [
        'label' => 'Google Recaptcha Site Key Register',
        'type' => 'text',
        'default' => '6LfBZasqAAAAACRX2S5qJYscG0mpluWV7DupnNwW'
    ],
    'GOOGLE_RECAPTCHA_SECRET_KEY_REGISTER' => [
        'label' => 'Google Recaptcha Secret Key Register',
        'type' => 'text',
        'default' => '6LfBZasqAAAAALaQ_p7Np2S05gG3ce6LNbJG7SW9'
    ],
    'GOOGLE_RECAPTCHA_SITE_KEY_LOGIN' => [
        'label' => 'Google Recaptcha Site Key Login',
        'type' => 'text',
        'default' => '6LeMptYqAAAAAKEW2tYZMCaiZQvJQ-DygtO6OxOK'
    ],
    'GOOGLE_RECAPTCHA_SECRET_KEY_LOGIN' => [
        'label' => 'Google Recaptcha Secret Key Login',
        'type' => 'text',
        'default' => '6LeMptYqAAAAAGeSDDwWJPQLZXASTLla0JffwjVK'
    ],
    'PAYPAL_API_BASE_URL' => [
        'label' => 'Paypal API Base URL',
        'type' => 'text',
        'default' => 'https://api-m.sandbox.paypal.com'
    ],
    'CONTACT_FORM_NUMERO_MASSIMO_CONTATTI' => [
        'label' => 'Contact Form Numero Massimo Contatti',
        'type' => 'number',
        'default' => 2
    ],
    'CONTACT_FORM_TEMPO_DI_BLOCCO' => [
        'label' => 'Contact Form Tempo di Blocco',
        'type' => 'number',
        'default' => 60*15
    ],
    'GUADAGNO_PUNTI_BLU_DOWNLOAD_DOCUMENTO' => [
        'label' => 'Guadagno Punti Blu Download Documento',
        'type' => 'number',
        'default' => 5
    ],
    'REINVIA_MAIL_NUMERO_MASSIMO_INVII' => [
        'label' => 'Reinvia mail Numero Massimo reinvii',
        'type' => 'number',
        'default' => 2
    ],
    'REINVIA_MAIL_TEMPO_DI_BLOCCO' => [
        'label' => 'Reinvia mail Tempo di Blocco',
        'type' => 'number',
        'default' => 60*15
    ],
    'FACEBOOK_URL' => [
        'label' => 'Facebook URL',
        'type' => 'text',
        'default' => 'https://www.facebook.com/people/MineDocs/61577700654679/'
    ],
    'INSTAGRAM_URL' => [
        'label' => 'Instagram URL',
        'type' => 'text',
        'default' => 'https://www.instagram.com/minedocs_'
    ],
    'TIKTOK_URL' => [
        'label' => 'TikTok URL',
        'type' => 'text',
        'default' => 'https://www.tiktok.com/@minedocs'
    ],

    
];

function inizializza_costanti() {
    global $fields;

    foreach ($fields as $key => $field) {
        //error_log($key);
        //error_log(get_option($key, ''));
        //if (!defined($key)) {
            if ($field['type'] === 'checkbox') {
                define($key, filter_var(get_option($key, $field['default']), FILTER_VALIDATE_BOOLEAN));
            } else {
                define($key, get_option($key, $field['default']));
            }
        //}
    }
}

inizializza_costanti();

// Aggiunge la pagina al menu di amministrazione
function mie_impostazioni_menu() {
    add_options_page(
        'Impostazioni Costanti', // Titolo della pagina
        'Impostazioni Costanti',       // Nome nel menu
        'manage_options',        // Permessi richiesti
        'impostazioni-costanti', // Slug della pagina
        'mie_impostazioni_render' // Funzione di callback
    );
}
add_action('admin_menu', 'mie_impostazioni_menu');

// Funzione che genera la pagina di amministrazione
function mie_impostazioni_render() {
    // Definizione dei campi
    global $fields;

    // Salvataggio dei dati se il form è stato inviato
    if (isset($_POST['submit'])) {
        check_admin_referer('salva_costanti_options'); // Sicurezza contro CSRF

        foreach ($fields as $key => $field) {
            if ($field['type'] === 'checkbox') {
                update_option($key, isset($_POST[$key]) ? '1' : '0'); // Checkbox
            } else {
                update_option($key, sanitize_text_field($_POST[$key] ?? ''));
            }
        }

        echo '<div class="updated"><p>Impostazioni salvate!</p></div>';
    }

    // Recupera i valori salvati
    $values = [];
    foreach ($fields as $key => $field) {
        $values[$key] = get_option($key, '');
    }

    ?>
    <div class="wrap">
        <h1>Impostazioni Costanti</h1>
        <form method="post">
            <?php wp_nonce_field('salva_costanti_options'); ?>

            <table class="form-table">
                <?php foreach ($fields as $key => $field) : ?>
                    <tr>
                        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                        <td>
                            <?php if ($field['type'] === 'checkbox') : ?>
                                <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="1" <?php checked($values[$key], '1'); ?>>
                            <?php else : ?>
                                <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($values[$key]); ?>">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p><input type="submit" name="submit" class="button-primary" value="Salva Impostazioni"></p>
        </form>
    </div>
    <?php
}


function registra_impostazioni_generali() {
    register_setting('impostazioni_generali_group', 'impostazioni_abbonamenti_opzioni');

    add_settings_section(
        'impostazioni_generali_sezione',
        'Impostazioni Generali',
        null,
        'impostazioni-generali'
    );

    $fields = [
        'FREE_FOR_ALREADY_PURCHASED' => [
            'label' => 'Free for Already Purchased',
            'type' => 'checkbox'
        ],
        'PUNTI_BLU_PRIMA_RECENSIONE' => [
            'label' => 'Punti Blu Prima Recensione',
            'type' => 'number'
        ],
        'PUNTI_BLU_CARICAMENTO_DOCUMENTO_CONDIVISO' => [
            'label' => 'Punti Blu Caricamento Documento Condiviso',
            'type' => 'number'
        ],
        'PUNTI_BLU_AGGIORNAMENTO_DOCUMENTO_CONDIVISO' => [
            'label' => 'Punti Blu Aggiornamento Documento Condiviso',
            'type' => 'number'
        ],
        'SALDO_MINIMO_PRELEVABILE' => [
            'label' => 'Saldo Minimo Prelevabile',
            'type' => 'number'
        ],
        'VALORE_PUNTI_PRO' => [
            'label' => 'Valore Punti Pro',
            'type' => 'number'
        ],
        'PERCENTUALE_COMMISSIONE_VENDITA' => [
            'label' => 'Percentuale Commissione Vendita',
            'type' => 'number'
        ],
        'ABILITA_CAPTCHA' => [
            'label' => 'Abilita Captcha',
            'type' => 'checkbox'
        ],
        'MOSTRA_FOLLOWERS' => [
            'label' => 'Mostra Followers',
            'type' => 'checkbox'
        ],
        'APACHE_TIKA_SERVER_URL' => [
            'label' => 'Apache Tika Server URL',
            'type' => 'text'
        ],
        'FLASK_ANALYSIS_API_URL' => [
            'label' => 'Flask Analysis API URL',
            'type' => 'text'
        ],
        'FLASK_ANALYSIS_API_KEY' => [
            'label' => 'Flask Analysis API Key',
            'type' => 'text'
        ],
        'SKU_ABBONAMENTO_30_GIORNI' => [
            'label' => 'SKU Abbonamento 30 Giorni',
            'type' => 'text'
        ],
        'SKU_ABBONAMENTO_90_GIORNI' => [
            'label' => 'SKU Abbonamento 90 Giorni',
            'type' => 'text'
        ],
        'SKU_ABBONAMENTO_365_GIORNI' => [
            'label' => 'SKU Abbonamento 365 Giorni',
            'type' => 'text'
        ],
        'AMOUNT_PUNTI_PRO_ABBPRO030' => [
            'label' => 'Amount Punti Pro ABBPRO030',
            'type' => 'number'
        ],
        'AMOUNT_PUNTI_PRO_ABBPRO090' => [
            'label' => 'Amount Punti Pro ABBPRO090',
            'type' => 'number'
        ],
        'AMOUNT_PUNTI_PRO_ABBPRO365' => [
            'label' => 'Amount Punti Pro ABBPRO365',
            'type' => 'number'
        ],
        'SKU_PUNTI_PRO_150' => [
            'label' => 'SKU Punti Pro 150',
            'type' => 'text'
        ],
        'SKU_PUNTI_PRO_500' => [
            'label' => 'SKU Punti Pro 500',
            'type' => 'text'
        ],
        'SKU_PUNTI_PRO_1000' => [
            'label' => 'SKU Punti Pro 1000',
            'type' => 'text'
        ],
        'CUSTOM_ERROR_LOG' => [
            'label' => 'Custom Error Log',
            'type' => 'text'
        ],
        'TABELLA_TRANSAZIONI' => [
            'label' => 'Tabella Transazioni',
            'type' => 'text'
        ],
        'TABELLA_REGISTRO_PUNTI' => [
            'label' => 'Tabella Registro Punti',
            'type' => 'text'
        ],
        'TABELLA_LOGIN_LOGS' => [
            'label' => 'Tabella Login Logs',
            'type' => 'text'
        ],
        'TABELLA_WALLET' => [
            'label' => 'Tabella Wallet',
            'type' => 'text'
        ],
        'TABELLA_STUDIA_AI_JOBS' => [
            'label' => 'Tabella Studia AI Jobs',
            'type' => 'text',
        ],
        'BLU_POINTS_EXPIRING_DATE' => [
            'label' => 'Blu Points Expiring Date',
            'type' => 'text'
        ],
        'USER_MAX_FAILED_LOGIN_ATTEMPTS' => [
            'label' => 'User Max Failed Login Attempts',
            'type' => 'number'
        ],
        'NUMERO_TENTATIVI_LOGIN_PRIMA_DI_CAPTCHA' => [
            'label' => 'Numero Tentativi Login Prima di Captcha',
            'type' => 'number'
        ],
        'USER_BLOCK_STAGE_1' => [
            'label' => 'User Block Stage 1',
            'type' => 'number'
        ],
        'USER_BLOCK_STAGE_2' => [
            'label' => 'User Block Stage 2',
            'type' => 'number'
        ],
        'USER_BLOCK_STAGE_3' => [
            'label' => 'User Block Stage 3',
            'type' => 'number'
        ],
        'LOGIN_PAGE' => [
            'label' => 'Login Page',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_PAGE' => [
            'label' => 'Profilo Utente Page',
            'type' => 'text'
        ],
        'CARICAMENTO_DOCUMENTO_PAGE' => [
            'label' => 'Caricamento Documento Page',
            'type' => 'text'
        ],
        'PIANI_PRO_PAGE' => [
            'label' => 'Piani Pro Page',
            'type' => 'text'
        ],
        'PACCHETTI_PUNTI_PAGE' => [
            'label' => 'Pacchetti Punti Page',
            'type' => 'text'
        ],
        'PRIVACY_POLICY_PAGE' => [
            'label' => 'Privacy Policy Page',
            'type' => 'text'
        ],
        'PROFILAZIONE_UTENTE_PAGE' => [
            'label' => 'Profilazione Utente Page',
            'type' => 'text'
        ],
        'RICERCA_PAGE' => [
            'label' => 'Ricerca Page',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_DOCUMENTI_CARICATI' => [
            'label' => 'Profilo Utente Documenti Caricati',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_DOCUMENTI_ACQUISTATI' => [
            'label' => 'Profilo Utente Documenti Acquistati',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_GUADAGNI' => [
            'label' => 'Profilo Utente Guadagni',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_MOVIMENTI' => [
            'label' => 'Profilo Utente Movimenti',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_VENDITE' => [
            'label' => 'Profilo Utente Vendite',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_IMPOSTAZIONI' => [
            'label' => 'Profilo Utente Impostazioni',
            'type' => 'text'
        ],
        'STUDIA_CON_AI_PAGE' => [
            'label' => 'Studia con AI Page',
            'type' => 'text'
        ],
        'PROFILO_UTENTE_GENERAZIONI_AI' => [
            'label' => 'Profilo Utente Generazioni AI',
            'type' => 'text'
        ],
        'DIVENTA_VENDITORE' => [
            'label' => 'Diventa Venditore',
            'type' => 'text'
        ],
        'FAQ_PAGE' => [
            'label' => 'FAQ Page',
            'type' => 'text'
        ],
        'CONTATTI_PAGE' => [
            'label' => 'Contatti Page',
            'type' => 'text'
        ],
        'UPLOAD_PAGE' => [
            'label' => 'Upload Page',
            'type' => 'text'
        ],
        'REGISTRAZIONE_PAGE' => [
            'label' => 'Registrazione Page',
            'type' => 'text'
        ],
        'TERMINI_E_CONDIZIONI_PAGE' => [
            'label' => 'Termini e Condizioni Page',
            'type' => 'text'
        ],
        'PATH_MODAL_COME_GUADAGNARE_PUNTI' => [
            'label' => 'Path Modal Come Guadagnare Punti',
            'type' => 'text'
        ],
        'META_KEY_STATO_APPROVAZIONE_PRODOTTO' => [
            'label' => 'Meta Key Stato Approvazione Prodotto',
            'type' => 'text'
        ],
        'PASSWORD_COMPLEXITY_MESSAGE' => [
            'label' => 'Password Complexity Message',
            'type' => 'text'
        ],
        'DEFAULT_THUMBNAIL' => [
            'label' => 'Default Thumbnail',
            'type' => 'text'
        ],
        'MINEDOCS_LOGO' => [
            'label' => 'MineDocs Logo',
            'type' => 'text'
        ],
        'GOOGLE_CLIENT_ID_LOGIN' => [
            'label' => 'Google Client ID Login',
            'type' => 'text'
        ],
        'GOOGLE_CLIENT_SECRET_LOGIN' => [
            'label' => 'Google Client Secret Login',
            'type' => 'text'
        ],
        'GOOGLE_REDIRECT_URI_LOGIN' => [
            'label' => 'Google Redirect URI Login',
            'type' => 'text'
        ],
        'GOOGLE_RECAPTCHA_SITE_KEY_REGISTER' => [
            'label' => 'Google Recaptcha Site Key Register',
            'type' => 'text'
        ],
        'GOOGLE_RECAPTCHA_SECRET_KEY_REGISTER' => [
            'label' => 'Google Recaptcha Secret Key Register',
            'type' => 'text'
        ],
        'GOOGLE_RECAPTCHA_SITE_KEY_LOGIN' => [
            'label' => 'Google Recaptcha Site Key Login',
            'type' => 'text'
        ],
        'GOOGLE_RECAPTCHA_SECRET_KEY_LOGIN' => [
            'label' => 'Google Recaptcha Secret Key Login',
            'type' => 'text'
        ],
        'PAYPAL_API_BASE_URL' => [
            'label' => 'Paypal API Base URL',
            'type' => 'text'
        ],
        'CONTACT_FORM_NUMERO_MASSIMO_CONTATTI' => [
            'label' => 'Contact Form Numero Massimo Contatti',
            'type' => 'number'
        ],
        'CONTACT_FORM_TEMPO_DI_BLOCCO' => [
            'label' => 'Contact Form Tempo di Blocco',
            'type' => 'number'
        ],
        'GUADAGNO_PUNTI_BLU_DOWNLOAD_DOCUMENTO' => [
            'label' => 'Guadagno Punti Blu Download Documento',
            'type' => 'number'
        ],
        'REINVIA_MAIL_NUMERO_MASSIMO_INVII' => [
            'label' => 'Reinvia mail Numero Massimo reinvii',
            'type' => 'number'
        ],
        'REINVIA_MAIL_TEMPO_DI_BLOCCO' => [
            'label' => 'Reinvia mail Tempo di Blocco',
            'type' => 'number'
        ],
        'FACEBOOK_URL' => [
            'label' => 'Facebook URL',
            'type' => 'text'
        ],
        'INSTAGRAM_URL' => [
            'label' => 'Instagram URL',
            'type' => 'text'
        ],
        'TIKTOK_URL' => [
            'label' => 'TikTok URL',
            'type' => 'text'
        ],
        
    ];

    foreach ($fields as $key => $value) {
        add_settings_field(
            $key,
            $value['label'],
            function() use ($key, $fields) {
                campo_generale_callback($key, $fields);
            },
            'impostazioni-generali',
            'impostazioni_generali_sezione'
        );
    }
}
//add_action('admin_init', 'registra_impostazioni_generali');

function campo_generale_callback($key, $fields) {
    $valore = get_option($key);
    $type = $fields[$key]['type'];
    $label = $fields[$key]['label'];    
    if ($type == 'checkbox') {
        ?>
        <input type="checkbox" name="<?php echo $key; ?>" <?php checked($valore, true); ?> />
        <?php
    } else {
        ?>
        <input type="<?php echo $type; ?>" name="<?php echo $key; ?>" value="<?php echo $valore; ?>" />
        <?php
    }
    ?>

    <?php
}



function registra_impostazioni_abbonamenti() {
    register_setting('impostazioni_abbonamenti_group', 'impostazioni_abbonamenti_opzioni');

    add_settings_section(
        'impostazioni_abbonamenti_sezione',
        'Impostazioni Abbonamenti',
        null,
        'impostazioni-abbonamenti'
    );

    add_settings_field(
        'durata_periodo_prova',
        'Durata Periodo di Prova (giorni)',
        'campo_durata_periodo_prova_callback',
        'impostazioni-abbonamenti',
        'impostazioni_abbonamenti_sezione'
    );

    add_settings_field(
        'codice_promozionale',
        'Codice Promozionale per Prova Gratuita',
        'campo_codice_promozionale_callback',
        'impostazioni-abbonamenti',
        'impostazioni_abbonamenti_sezione'
    );
    
    add_settings_field(
        'tipologia_prova_gratuita',
        'Tipologia di Prova Gratuita',
        'campo_tipologia_prova_gratuita_callback',
        'impostazioni-abbonamenti',
        'impostazioni_abbonamenti_sezione'
    );

    global $sistemiPunti;
    foreach ($sistemiPunti as $sistema) {
        add_settings_field(
            'punti_prova_' . $sistema->get_meta_key(),
            'Punti di Prova per ' . $sistema->get_name(),
            function() use ($sistema) {
                campo_punti_prova_callback($sistema);
            },
            'impostazioni-abbonamenti',
            'impostazioni_abbonamenti_sezione'
        );
    }
}
add_action('admin_init', 'registra_impostazioni_abbonamenti');

function campo_durata_periodo_prova_callback() {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    ?>
<input type="number" name="impostazioni_abbonamenti_opzioni[durata_periodo_prova]"
    value="<?php echo isset($opzioni['durata_periodo_prova']) ? esc_attr($opzioni['durata_periodo_prova']) : ''; ?>" />
<?php
}

function campo_punti_prova_callback($sistema) {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    ?>
<input type="number" name="impostazioni_abbonamenti_opzioni[punti_prova_<?php echo $sistema->get_meta_key(); ?>]"
    value="<?php echo isset($opzioni['punti_prova_' . $sistema->get_meta_key()]) ? esc_attr($opzioni['punti_prova_' . $sistema->get_meta_key()]) : ''; ?>" />
<?php
}

function campo_codice_promozionale_callback() {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    ?>
<input type="text" name="impostazioni_abbonamenti_opzioni[codice_promozionale_prova_pro]"
    value="<?php echo isset($opzioni['codice_promozionale_prova_pro']) ? esc_attr($opzioni['codice_promozionale_prova_pro']) : ''; ?>" />
<?php
}



function campo_tipologia_prova_gratuita_callback() {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    $tipologia = isset($opzioni['tipologia_prova_gratuita']) ? esc_attr($opzioni['tipologia_prova_gratuita']) : '';
    ?>
<select name="impostazioni_abbonamenti_opzioni[tipologia_prova_gratuita]">
    <option value="estensione_automatica" <?php selected($tipologia, 'estensione_automatica'); ?>>Estensione automatica
        del pro di n giorni</option>
    <option value="sconto_primo_abbonamento" <?php selected($tipologia, 'sconto_primo_abbonamento'); ?>>Sconto sul primo
        abbonamento (in tal caso, impostare l'effetto sul Codice promozionale)</option>
    <option value="nessun_pagamento" <?php selected($tipologia, 'nessun_pagamento'); ?>>Nessun pagamento per n giorni
    </option>
</select>
<?php
}

function aggiungi_pagina_impostazioni_abbonamenti() {
    add_submenu_page(
        'gestione-punti',
        'Impostazioni Abbonamenti',
        'Impostazioni Abbonamenti',
        'manage_options',
        'impostazioni-abbonamenti',
        'mostra_pagina_impostazioni_abbonamenti'
    );
}
add_action('admin_menu', 'aggiungi_pagina_impostazioni_abbonamenti');

function mostra_pagina_impostazioni_abbonamenti() {
    ?>
<div class="wrap">
    <h1>Impostazioni Abbonamenti</h1>
    <form method="post" action="options.php">
        <?php
            settings_fields('impostazioni_abbonamenti_group');
            do_settings_sections('impostazioni-abbonamenti');
            submit_button();
            ?>
    </form>
</div>
<?php
}

function get_durata_periodo_prova() {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    return isset($opzioni['durata_periodo_prova']) ? intval($opzioni['durata_periodo_prova']) : 0;
}

function get_punti_prova($sistema) {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    return isset($opzioni['punti_prova_' . $sistema->get_meta_key()]) ? intval($opzioni['punti_prova_' . $sistema->get_meta_key()]) : 0;
}

function get_codice_promozionale_prova_pro() {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    return isset($opzioni['codice_promozionale_prova_pro']) ? $opzioni['codice_promozionale_prova_pro'] : '';
}

function get_tipologia_prova_gratuita() {
    $opzioni = get_option('impostazioni_abbonamenti_opzioni');
    return isset($opzioni['tipologia_prova_gratuita']) ? $opzioni['tipologia_prova_gratuita'] : '';
}
