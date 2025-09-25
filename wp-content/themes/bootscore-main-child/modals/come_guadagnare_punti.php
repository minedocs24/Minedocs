<?php

function ottieni_contenuto_popup_come_guadagnare_punti() {
    // Genera il contenuto HTML
    $html = '

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Punti Blu</h6>
                            <p>I punti blu sono i punti gratuiti di MineDocs, si possono guadagnare condividendo documenti con il resto della community di MineDocs e recensendo documenti scaricati.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Punti <span class="pro">Pro</span></h6>
                            <p>I punti Pro sono utilizzabili se hai un Abbonamento Pro attivo. 
                            Si possono acquisire sottoscrivendo un abbonamento Pro oppure acquistando una ricarica Pro.
                            </p>
                            <p>I punti Pro possono essere utilizzati per scaricare documenti Pro, accedere ai tool AI e altro ancora.</p>
                        </div>
                    </div>
                </div>
            </div>
            ';

        if (is_user_logged_in() && is_abbonamento_attivo(get_current_user_id())) {
            $html .= '
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="text-muted">Hai un abbonamento attivo! Considera di acquistare una ricarica Pro per guadagnare ancora più punti.</p>
                    <a href="' . PACCHETTI_PUNTI_PAGE . '" class="btn btn-primary btn-lg">Acquista Ricarica Pro</a>
                </div>
            </div>
            ';
        } else {
            $html .= '
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="text-muted">Vuoi utilizzare MineDocs al massimo delle potenzialità? Sottoscrivi un abbonamento Pro!</p>
                    <a href="' . PIANI_PRO_PAGE . '" class="btn btn-primary btn-lg">Sottoscrivi Abbonamento Pro</a>
                </div>
            </div>
            ';
        }
    

    // Invia l'HTML come risposta JSON
    wp_send_json_success(array(
        'titolo' => 'Qual è la differenza tra punti blu e punti pro?',
        'contenuto' =>   $html,
        'larghezza' => '60%',
    )
    );
}

// Registra le azioni AJAX
add_action('wp_ajax_popup_come_guadagnare_punti', 'ottieni_contenuto_popup_come_guadagnare_punti');
add_action('wp_ajax_nopriv_popup_come_guadagnare_punti', 'ottieni_contenuto_popup_come_guadagnare_punti');
