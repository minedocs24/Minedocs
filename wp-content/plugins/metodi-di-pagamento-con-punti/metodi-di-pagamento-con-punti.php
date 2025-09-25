<?php
/*
Plugin Name: Metodi di Pagamento con Punti
Description: Aggiunge Punti Premium e Punti Membro come metodi di pagamento.
Version: 1.0
Author: Il Tuo Nome
*/

require_once __DIR__ . '/vendor/autoload.php';
//include_once("impostazioni_utenti.php");
include_once("SistemaPunti.php");
include_once("funzioni_punti.php");

$sistemiPunti = array(
    'pro' => new SistemaPunti('pro'),
    'blu' => new SistemaPunti('blu')
);

// $sistemiPunti['pro']->set_icon(get_stylesheet_directory_uri(  ). "/assets/img/search/puntipro.svg");
// $sistemiPunti['blu']->set_icon(get_stylesheet_directory_uri(  ). "/assets/img/search/puntiblu.svg");
$sistemiPunti['pro']->set_icon(get_stylesheet_directory_uri(  ). "/assets/img/search/Icone_Minedocs_punti_rossi.svg");
$sistemiPunti['blu']->set_icon(get_stylesheet_directory_uri(  ). "/assets/img/search/Icone_Minedocs_punti_blu.svg");

//include_once("controlli_download_file.php");
//include_once("safe-download.php");


function get_sistema_punti($key): SistemaPunti {
    global $sistemiPunti;
    return $sistemiPunti[$key];
}

function get_sistemi_punti(){
    global $sistemiPunti;
    return $sistemiPunti;
}


// Aggiungi i campi per i punti premium e i punti membro all'update_network_cache( $networks:array )
function aggiungi_punti_utente($user_id) {
    global $sistemiPunti;
    foreach ($sistemiPunti as $sistema) {
        add_user_meta($user_id, $sistema->get_meta_key(), 0, true);        
    }

}
add_action('user_register', 'aggiungi_punti_utente');

function mostra_punti_profilo($user) {
    global $sistemiPunti;
    $punti = array();
    foreach ($sistemiPunti as $sistema) {
        $punti[$sistema->get_meta_key()] = get_user_meta($user->ID, $sistema->get_meta_key(), true);
    }

?>
<h3>Punti Utente</h3>
<table class="form-table">
    <?php 
        foreach($sistemiPunti as $sistema) {
        ?>
    <tr>
        <th><label for="<?php echo $sistema->get_meta_key(); ?>"><?php echo $sistema->get_name(); ?></label></th>
        <td>
            <input type="number" name="<?php echo $sistema->get_meta_key(); ?>"
                value="<?php echo esc_attr($sistema->ottieni_totale_punti($user->ID)); ?>" class="regular-text" /><br />
        </td>
    </tr>
    <?php } ?>
</table>


<?php foreach($sistemiPunti as $sistema) { 
    $registro = get_points_register_by_type($user->ID, $sistema->get_name_unformatted());
    $registro = array_slice($registro, -5);
    $totale_acquistati = get_total_purchased_points_by_type($user->ID, $sistema->get_name_unformatted());
    $totale_rimasti = get_total_remaining_points_by_type($user->ID, $sistema->get_name_unformatted());
    $totale_scaduti = get_total_expired_points_by_type($user->ID, $sistema->get_name_unformatted());

    ?>
    <h4><?php echo $sistema->get_name(); ?></h4>
    <table class="form-table" style="border-collapse: collapse; width: auto;">
        <thead>
            <tr>
                <th style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;">Punti Acquistati</th>
                <th style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;">Punti Rimasti</th>
                <th style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;">Data di Scadenza</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($registro as $r) { ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;"><?php echo $r->purchased_points; ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;"><?php echo $r->remaining_points; ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;"><?php echo $r->expiring_date; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;"><strong>Totale Acquistati:</strong> <?php echo $totale_acquistati; ?></td>
                <td style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;"><strong>Totale Rimasti:</strong> <?php echo $totale_rimasti; ?></td>
                <td style="border: 1px solid #ccc; padding: 5px; white-space: nowrap;"><strong>Totale Scaduti:</strong> <?php echo $totale_scaduti; ?></td>
            </tr>
        </tbody>
    </table>
<?php } ?>



<?php
}

function salva_punti_profilo($user_id) {
    global $sistemiPunti;
    foreach ($sistemiPunti as $sistema ){
    if (current_user_can('edit_user', $user_id)) {
        update_user_meta($user_id, $sistema->get_meta_key(), $_POST[$sistema->get_meta_key()]);
    }
}
}

add_action('show_user_profile', 'mostra_punti_profilo');
add_action('edit_user_profile', 'mostra_punti_profilo');
add_action('personal_options_update', 'salva_punti_profilo');
add_action('edit_user_profile_update', 'salva_punti_profilo');


function aggiungi_pagina_gestione_punti() {
    add_menu_page(
        'Gestione Punti',
        'Gestione Punti',
        'manage_options',
        'gestione-punti',
        'mostra_tabella_punti',
        'dashicons-tickets-alt',
        20
    );
}
add_action('admin_menu', 'aggiungi_pagina_gestione_punti');



function mostra_tabella_punti() {
    global $sistemiPunti;
    $utenti = get_users();
    ?>
<div class="wrap">
    <h1>Gestione Punti</h1>
    <p>Questa pagina ti permette di gestire i punti degli utenti.</p>
    <input type="text" id="search-user" placeholder="Cerca utente..." onkeyup="cercaUtente()">

    <script>
    function cercaUtente() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("search-user");
        filter = input.value.toUpperCase();
        table = document.getElementById("tabella-punti");
        tr = table.getElementsByTagName("tr");

        // Filtra gli utenti globalmente
        var utentiFiltrati = utenti.filter(function(utente) {
            return utente.nome.toUpperCase().indexOf(filter) > -1;
        });

        // Mostra solo gli utenti filtrati nella tabella
        var tbody = document.getElementById('tabella-corpo');
        tbody.innerHTML = ''; // Reset tabella

        utentiFiltrati.forEach(function(utente) {
            var row = '<tr>';
            row += '<td>' + utente.ID + '</td>';
            row += '<td><a href="' + utente.utente_url + '">'+utente.nome+'</a></td>';
            row += '<td>' + utente.scadenza_abbonamento + '</td>';
            row += '<td style="font-size: 15px; color:' + (utente.stato_abbonamento ? 'green' : 'red') + ';">' + (utente.stato_abbonamento ? 'Attivo' : 'Non attivo') + '</td>';
            utente.sistemi.forEach(function(sistema) {
                row += '<td><span style="font-size: 20px;" id="' + sistema.meta_key + '_totale_' + utente.ID + '">' + sistema.punti + ' </span>' + sistema.icona +
                    '  <button class="button modifica-punti" data-utente-id="' + utente.ID + '" data-tipo="' +
                    sistema.meta_key + '" data-action="aggiungi">+</button>' +
                    '<button class="button modifica-punti" data-utente-id="' + utente.ID + '" data-tipo="' +
                    sistema.meta_key + '" data-action="rimuovi">-</button>' +
                    '</td>';
            });
            row += '<td><button class="button visualizza-log" data-utente-id="' + utente.ID + '">Visualizza log</button></td>';
            row += '</tr>';
            tbody.innerHTML += row;
        });
    }

    /*function cercaUtente() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("search-user");
        filter = input.value.toUpperCase();
        table = document.getElementById("tabella-punti");
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[1];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }       
        }
    }*/
    </script>
    <table id="tabella-punti" class="wp-list-table widefat fixed striped users">
        <thead>
            <tr>
                <th>ID</th>
                <th>Utente</th>
                <th>Scadenza Abbonamento</th>
                <th>Stato Abbonamento</th>
                <?php foreach($sistemiPunti as $sistema) { ?>
                <th><?php echo $sistema->get_name(); ?></th>
                <?php } ?>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody id="tabella-corpo"></tbody>
    </table>
    <div id="paginazione" style="margin-top: 20px;">
        <button id="pagina-precedente" class="button">Precedente</button>
        <span id="pagina-attuale">1</span>
        <button id="pagina-successiva" class="button">Successivo</button>
    </div>
</div>

<script>
// Dati degli utenti passati da PHP a JS
var utenti = <?php echo json_encode(array_map(function($utente) use ($sistemiPunti) {
            $sistemi = [];
            foreach ($sistemiPunti as $sistema) {
                $sistemi[] = [
                    'nome' => $sistema->get_name(),
                    'punti' => $sistema->ottieni_totale_punti($utente->ID),
                    'meta_key' => $sistema->get_meta_key(),
                    'icona' => $sistema->print_icon()
                ];
            }
            return [
                'ID' => $utente->ID,
                'nome' => $utente->display_name,
                'scadenza_abbonamento' => get_user_meta($utente->ID, 'scadenza_abbonamento', true),
                'stato_abbonamento' => is_abbonamento_attivo($utente->ID),
                'sistemi' => $sistemi,
                'utente_url' => get_edit_user_link($utente->ID)
            ];
        }, $utenti)); ?>;

var perPagina = 20; // Utenti per pagina
var paginaCorrente = 1;

// Funzione per visualizzare la tabella
function visualizzaTabella() {
    var inizio = (paginaCorrente - 1) * perPagina;
    var fine = inizio + perPagina;
    var utentiPagina = utenti.slice(inizio, fine);
    var tbody = document.getElementById('tabella-corpo');
    tbody.innerHTML = ''; // Reset tabella

    utentiPagina.forEach(function(utente) {
        var row = '<tr>';
        row += '<td>' + utente.ID + '</td>';
        row += '<td><a href="' + utente.utente_url + '">'+utente.nome+'</a></td>';
        row += '<td>' + utente.scadenza_abbonamento + '</td>';
        row += '<td style="font-size: 15px; color:' + (utente.stato_abbonamento ? 'green' : 'red') + ';">' + (utente.stato_abbonamento ? 'Attivo' : 'Non attivo') + '</td>';
        utente.sistemi.forEach(function(sistema) {
            row += '<td><span style="font-size: 20px;" id="' + sistema.meta_key + '_totale_' + utente
                .ID + '">' + sistema.punti + ' </span>' + sistema.icona +
                '  <button class="button modifica-punti" data-utente-id="' + utente.ID +
                '" data-tipo="' +
                sistema.meta_key + '" data-action="aggiungi">+</button>' +
                '<button class="button modifica-punti" data-utente-id="' + utente.ID + '" data-tipo="' +
                sistema.meta_key + '" data-action="rimuovi">-</button>' +
                '</td>';
        });
        row += '<td><button class="button visualizza-log" data-utente-id="' + utente.ID +
            '">Visualizza log</button></td>';
        row += '</tr>';
        tbody.innerHTML += row;
    });

    // Aggiorna il numero di pagina attuale
    document.getElementById('pagina-attuale').textContent = paginaCorrente;
}

// Funzioni di navigazione della paginazione
document.getElementById('pagina-precedente').addEventListener('click', function() {
    if (paginaCorrente > 1) {
        paginaCorrente--;
        visualizzaTabella();
    }
});

document.getElementById('pagina-successiva').addEventListener('click', function() {
    if (paginaCorrente * perPagina < utenti.length) {
        paginaCorrente++;
        visualizzaTabella();
    }
});

// Carica la tabella inizialmente
visualizzaTabella();
</script>

<!-- Popup Modal -->
<div id="punti-popup" style="display:none;">
    <h2 class="titolo-popup">Modifica Punti</h2>
    <form id="modifica-punti-form">
        <input type="hidden" id="utente-id" name="utente_id">
        <input type="hidden" id="tipo-punti" name="tipo_punti">
        <input type="hidden" id="action" name="action">
        <label for="punti">Punti:</label>
        <input type="number" id="punti" name="punti" required>
        <label for="motivo">Motivazione:</label>
        <input type="text" id="motivo" name="motivo" required>
        <button type="submit" class="button button-primary">Conferma</button>
        <button type="button" class="button button-secondary" id="btn-close-popup">Chiudi</button>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Usa delegazione evento per gestire i pulsanti aggiunti dinamicamente
    $(document).on('click', '.modifica-punti', function() {
        var utenteId = $(this).data('utente-id');
        var tipoPunti = $(this).data('tipo');
        var action = $(this).data('action');

        $('#utente-id').val(utenteId);
        $('#tipo-punti').val(tipoPunti);
        $('#action').val(action);

        $('.titolo-popup').text(action + ' punti');

        // Mostra il popup
        $('#punti-popup').show();
    });

    $('#btn-close-popup').on('click', function() {
        $('#punti-popup').hide();
    });

    // Nascondi il popup quando il form viene inviato
    $('#modifica-punti-form').on('submit', function(e) {
        e.preventDefault();

        var _action = $('#action').val();

        var data = {
            action: _action + '_' + $('#tipo-punti').val(),
            user_id: $('#utente-id').val(),
            tipo_punti: $('#tipo-punti').val(),
            punti: $('#punti').val(),
            motivo: $('#motivo').val(),
            security: '<?php echo wp_create_nonce('sistema_punti_nonce'); ?>'
        };
        console.log(data);

        $.post(ajaxurl, data, function(response) {
            //alert(response.data);
            if (response.success) {
                console.log(response);
                alert(response.data.messaggio);
                console.log("#" + response.data.meta_key + "_totale_" + response.data.user_id)
                $("#" + response.data.meta_key + "_totale_" + response.data.user_id).text(
                    response.data.punti_totali);
                //visualizzaTabella();
                // Ricarica la pagina per aggiornare i punti
                //location.reload();

            } else {
                alert(response.data);
            }
        });

        // Nascondi il popup
        $('#punti-popup').hide();
    });
});
</script>

<style>
/* Stile per il popup */
#punti-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    z-index: 1000;
}
</style>
<?php
} 


// add_action('wp_ajax_get_user_meta', 'get_user_meta_callback');
// add_action('wp_ajax_nopriv_get_user_meta', 'get_user_meta_callback');

function get_user_meta_callback() {
    // Verifica il nonce per la sicurezza
    if (!isset($_GET['security']) || !wp_verify_nonce($_GET['security'], 'get_user_meta_nonce')) {
        wp_send_json_error('Nonce non valido');
    }

    // Ottieni i parametri dalla richiesta
    $user_id = intval($_GET['user_id']);
    $meta_key = sanitize_text_field($_GET['meta_key']);

    // Ottieni i meta dati dell'utente
    $meta_value = get_user_meta($user_id, $meta_key, true);

    if ($meta_value !== false) {
        wp_send_json_success($meta_value);
    } else {
        wp_send_json_error('Meta dati non trovati');
    }

    // Importante: termina l'esecuzione dello script
    wp_die();
}








// Aggiungi un campo personalizzato per selezionare la valuta nel prodotto
function aggiungi_campo_valuta_prodotto() {
    global $woocommerce, $post, $sistemiPunti;

    $punti=array();
    foreach($sistemiPunti as $sistema){
        $punti[$sistema->get_meta_key()]=$sistema->get_name();
    }



    echo '<div class="options_group">';
    
    woocommerce_wp_select( array( 
        'id'      => '_valuta_personalizzata', 
        'label'   => __( 'Valuta di acquisto', 'woocommerce' ), 
        'options' => array_merge(
            array(
            'EUR'           => __( 'Euro', 'woocommerce' ),
            ),
            $punti )));
    
    echo '</div>';
}
add_action( 'woocommerce_product_options_pricing', 'aggiungi_campo_valuta_prodotto' );

// Salva il campo personalizzato della valuta
function salva_valuta_prodotto_personalizzata( $post_id ) {
    $valuta_selezionata = $_POST['_valuta_personalizzata'];
    if ( ! empty( $valuta_selezionata ) ) {
        update_post_meta( $post_id, '_valuta_personalizzata', esc_attr( $valuta_selezionata ) );
    }
}
add_action( 'woocommerce_process_product_meta', 'salva_valuta_prodotto_personalizzata' );


foreach($sistemiPunti as $sistema){
    // Aggiungi un campo di testo personalizzato per il "Costo in punti"
    add_action('woocommerce_product_options_pricing', function() use($sistema) {
        global $post;

        // Recupera il valore salvato precedentemente per il costo in punti
        $costo_in_punti = get_post_meta($post->ID, '_costo_in_'.$sistema->get_meta_key(), true);

        echo '<div class="options_group">';

        // Campo di input per il costo in punti
        woocommerce_wp_text_input( array( 
            'id'          => '_costo_in_'.$sistema->get_meta_key(), 
            'label'       => __( 'Costo in '.$sistema->get_name(), 'woocommerce' ), 
            'desc_tip'    => 'true',
            'description' => __( 'Inserisci il costo del prodotto in punti.', 'woocommerce' ),
            'type'        => 'number',
            'custom_attributes' => array(
                'step' => '1',
                'min' => '0'
            ),
            'value'       => $costo_in_punti
        ));

        echo '</div>';
    }
    );

    // Salva il campo personalizzato "Costo in punti"
    add_action( 'woocommerce_process_product_meta', function ( $post_id ) use($sistema) {
        if ( isset( $_POST['_costo_in_'.$sistema->get_meta_key()] ) ) {
            $costo_in_punti = $_POST['_costo_in_'.$sistema->get_meta_key()];
            update_post_meta( $post_id, '_costo_in_'.$sistema->get_meta_key(), esc_attr( $costo_in_punti ) );
        }
    }
     );

}



// Mostra i punti premium e i punti membro nella pagina Il Mio Account
function mostra_punti_nella_pagina_account() {
    $user_id = get_current_user_id();    
    
    
    echo '<h3>I tuoi Punti</h3>';

    ?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">I tuoi punti</h5>
        <p class="card-text">Attraverso i punti puoi scaricare i documenti presenti sul nostro portale. Scopri come
            guadagnare i tuoi punti!</p>
        <ul class="list-group list-group-flush">

            <?php

    global $sistemiPunti;
    foreach($sistemiPunti as $sistema){
        ?>
            <li class="list-group-item"><?php echo $sistema->print_icon();?>
                <span
                    class="show_count_<?php echo $sistema->get_meta_key(); ?>"><?php echo $sistema->ottieni_totale_punti(get_current_user_id());?></span>
                <?php echo $sistema->get_name();?>
                <a class="btn btn-primary" href="">Scopri come averli</a>
            </li>
            <?php
    }

?>
        </ul>
    </div>
</div>
<?php

}
add_action( 'woocommerce_account_dashboard', 'mostra_punti_nella_pagina_account' );


// Aggiungi meta box per file_anteprima e num_pagine
function aggiungi_campi_personalizzati_product() {
    add_meta_box(
        'woocommerce_file_anteprima',
        'File Anteprima',
        'campo_file_anteprima_callback',
        'product',
        'normal',
        'high'
    );
    add_meta_box(
        'woocommerce_num_pagine',
        'Numero di Pagine',
        'campo_num_pagine_callback',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'aggiungi_campi_personalizzati_product');

/*// Callback per il campo 'File Anteprima' con selettore media
function campo_file_anteprima_callback($post) {
    $file_anteprima = get_post_meta($post->ID, '_file_anteprima', true);
    ?>
<label for="file_anteprima">Seleziona un file dalla libreria:</label>
<div>
    <input type="text" id="file_anteprima" name="file_anteprima" value="<?php echo esc_attr($file_anteprima); ?>"
        style="width:80%;" />
    <button type="button" class="button" id="file_anteprima_button">Seleziona File</button>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
    var file_frame;
    $('#file_anteprima_button').on('click', function(event) {
        event.preventDefault();

        // Se il selettore esiste già, aprilo.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Crea il selettore media.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Seleziona un file',
            button: {
                text: 'Usa questo file',
            },
            multiple: false
        });

        // Quando un file viene selezionato.
        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#file_anteprima').val(attachment.url);
        });

        // Apri il selettore
        file_frame.open();
    });
});
</script>
<?php
}*/


// Callback per il campo 'File Anteprima' con selettore media
function campo_file_anteprima_callback($post) {
    $file_anteprima_id = get_post_meta($post->ID, '_file_anteprima', true);
    $file_anteprima_url = $file_anteprima_id ? wp_get_attachment_url($file_anteprima_id) : '';
    ?>
<label for="file_anteprima_url">Seleziona un file dalla libreria:</label>
<div>
    <input type="text" id="file_anteprima_url" value="<?php echo esc_attr($file_anteprima_url); ?>" style="width:80%;" readonly />
    <input type="hidden" id="file_anteprima_id" name="file_anteprima" value="<?php echo esc_attr($file_anteprima_id); ?>" />
    <button type="button" class="button" id="file_anteprima_button">Seleziona File</button>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
    var file_frame;
    $('#file_anteprima_button').on('click', function(event) {
        event.preventDefault();

        // Se il selettore esiste già, aprilo.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Crea il selettore media.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Seleziona un file',
            button: {
                text: 'Usa questo file',
            },
            multiple: false
        });

        // Quando un file viene selezionato.
        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#file_anteprima_url').val(attachment.url); // Mostra l'URL nel campo visibile
            $('#file_anteprima_id').val(attachment.id); // Salva l'ID nel campo nascosto
        });

        // Apri il selettore
        file_frame.open();
    });
});
</script>
<?php
}





// Callback per il campo 'Numero di Pagine'
function campo_num_pagine_callback($post) {
    $num_pagine = get_post_meta($post->ID, '_num_pagine', true);
    echo '<label for="num_pagine">Numero di Pagine:</label>';
    echo '<input type="number" id="num_pagine" name="num_pagine" value="' . esc_attr($num_pagine) . '" size="25" />';
}




// Salva i dati dei campi personalizzati
function salva_campi_personalizzati_product($post_id) {
    if (isset($_POST['file_anteprima'])) {
        // Sanitize and save the file ID
        $file_anteprima_id = sanitize_text_field($_POST['file_anteprima']);
        update_post_meta($post_id, '_file_anteprima', $file_anteprima_id);
    }
/*
    if (isset($_POST['file_anteprima'])) {
        update_post_meta($post_id, '_file_anteprima', sanitize_text_field($_POST['file_anteprima']));
        // Percorso al file PDF
        $pdfPath = sanitize_text_field($_POST['file_anteprima']);

        // Ottieni la directory degli upload in WordPress
        $upload_dir = wp_upload_dir();
        $upload_baseurl = $upload_dir['baseurl'];  // URL base della cartella upload
        $upload_basedir = $upload_dir['basedir'];  // Path fisico della cartella upload

        // Confronta l'URL del file con l'URL della cartella upload
        if (strpos($pdfPath, $upload_baseurl) !== false) {
            // Sostituisci la parte URL con il path fisico
            $pdfPath = str_replace($upload_baseurl, $upload_basedir, $pdfPath);
        } else {
            // Gestisci il caso in cui il file non sia nella cartella upload (opzionale)
            $pdfPath = ''; // Oppure altra logica per gestire file esterni
        }

        // Creazione della directory per le immagini convertite
        $outputDir = dirname($pdfPath) . "/" . pathinfo($pdfPath, PATHINFO_FILENAME);

        // Crea la cartella se non esiste
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        
       
    }
        */
    if (isset($_POST['num_pagine'])) {
        update_post_meta($post_id, '_num_pagine', sanitize_text_field($_POST['num_pagine']));
    }
}
add_action('save_post', 'salva_campi_personalizzati_product');





