<?php


function controllo_download() {

    error_log('controllo_download');

    global $sistemiPunti;
    $free_for_already_purchased = FREE_FOR_ALREADY_PURCHASED; // Imposta su true se il download è gratuito per chi ha già acquistato il prodotto

    #errore agli utenti non loggati
    if(get_current_user_id(  )==0){
        wp_send_json_error(array('message' => 'Accedi per poter scaricare il documento!'));
    }

    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nonce_download_file')) {
        wp_send_json_error(['message' => 'Nonce non valido.']);
    }

    // Esegui i tuoi controlli qui
    $controllo_superato = true; // Sostituisci con i tuoi veri controlli


    $product_hid = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : 0;
    $product_id = get_product_id_by_hash($product_hid);
    if($product_id==0){
        wp_send_json_error(array('message' => 'Prodotto non valido'));
    }
    $error = "";
    $user_point = 0;
    $is_autore = false;

    if($free_for_already_purchased && user_has_purchased_product(get_current_user_id(  ), $product_id)){
        $download_url = get_download_code(get_current_user_id(  ), $product_id);
        wp_send_json_success(array('download_url' => $download_url, 'message' => 'Hai già acquistato questo file in precedenza'));

    }
    if(get_post($product_id)->post_author == get_current_user_id()){
        $is_autore = true;
        $download_url = download_from_owner_link($product_id, get_current_user_id());
        wp_send_json_success(array('download_url' => $download_url, 'message' => 'Sei tu l\'autore del documento'));
        
    }


    $controls = array(
        array(
            'status' => true,
            'control' => function() use ($product_id) {
                return $product_id > 0;
            },
            'error' => 'Prodotto non valido'
        ),
        array(
            'status' => true,
            'control' => function() use ($product_id, &$user_point_pro) {
                global $sistemiPunti;

                $costo_punti_pro = (int)(get_post_meta($product_id, '_costo_in_punti_pro', true));

                if ($costo_punti_pro > 0) {
                    $user_has_active_subscription = is_abbonamento_attivo(get_current_user_id());
                    return $user_has_active_subscription;
                }

                return true;
            },
            'error' => 'Non hai un abbonamento attivo per utilizzare i Punti Pro',
            'error_code' => 'no_subscription'
        ),
        array(
            'status' => false,
            'control' => function() use ($product_id, &$user_point) {  // Usa il riferimento per $user_point
                global $sistemiPunti;
                $user_point = (int) ($sistemiPunti['blu']->ottieni_totale_punti(get_current_user_id()));
                //$user_point =  (int)(get_user_meta(get_current_user_id(), $sistemiPunti['blu']->get_meta_key(), true));
                $costo_punti_blu = (int)(get_post_meta($product_id,'_costo_in_punti_blu', true));

                return true; 
            },
            'error' => function() use (&$user_point) { // Funzione anonima per l'errore
                return "Hai " . $user_point . " punti";
            }
        ),
        array(
            'status' => true,
            'control' => function() use ($product_id, &$user_point_pro) {  // Usa il riferimento per $user_point
                global $sistemiPunti;

                //$user_point_pro =  (int)(get_user_meta(get_current_user_id(), $sistemiPunti['pro']->get_meta_key(), true));
                $user_point_pro = (int) ($sistemiPunti['pro']->ottieni_totale_punti(get_current_user_id()));
                $costo_punti_pro = (int)(get_post_meta($product_id,'_costo_in_punti_pro', true));

                if($user_point_pro>=$costo_punti_pro){
                    return true;
                }

                return false;
            },
            'error' => function() use (&$user_point_pro) { // Funzione anonima per l'errore
                return "Non hai abbastanza Punti Pro";
            },
            'error_code' => 'no_enough_points_pro'
        ),
        array(
            'status' => true,
            'control' => function() use ($product_id, &$user_point_blu) {  // Usa il riferimento per $user_point
                global $sistemiPunti;
                $user_point_blu = (int) ($sistemiPunti['blu']->ottieni_totale_punti(get_current_user_id()));
                //$user_point_blu =  (int)(get_user_meta(get_current_user_id(), $sistemiPunti['blu']->get_meta_key(), true));

                $costo_punti_blu = (int)(get_post_meta($product_id,'_costo_in_punti_blu', true));

                if($user_point_blu>=$costo_punti_blu){
                    return true;
                }

                return false;
            },
            'error' => function() use (&$user_point) { // Funzione anonima per l'errore
                return "Non hai abbastanza Punti Blu";
            }
        ),

        
    );

        $n_controls = 0 ;
        while ( $controllo_superato && $n_controls < sizeof($controls)){
            if($controls[$n_controls]['status']=true){
            $controllo_superato = $controllo_superato && $controls[$n_controls]['control']();

            if (!$controllo_superato && is_callable($controls[$n_controls]['error'])) {
                $error = $controls[$n_controls]['error']();
                $error_code = isset($controls[$n_controls]['error_code']) ? $controls[$n_controls]['error_code'] : null;
            } elseif (!$controllo_superato) {
                $error = $controls[$n_controls]['error'];
                $error_code = isset($controls[$n_controls]['error_code']) ? $controls[$n_controls]['error_code'] : null;
            }
        }

            $n_controls+=1;
        }
    
    

    if ($controllo_superato) {
        // Crea un nuovo ordine
        $order = wc_create_order();

        error_log('Creazione ordine per il download del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id . ' - Utente: ' . get_current_user_id(  ));

        // Aggiungi il prodotto scaricabile all'ordine
        $product = wc_get_product($product_id);
        $order->add_product($product, 1); // Aggiunge 1 quantità del prodotto

        // Imposta i dettagli del cliente (puoi prendere i dati utente se loggato)
        $user_id = get_current_user_id();
        $order->set_customer_id($user_id);

        // Imposta lo stato dell'ordine come completato
        $order->update_status('completed');

        $billing_data = get_user_billing_data();

        // Aggiungi i dettagli di fatturazione all'ordine
        $order->set_billing_address_1($billing_data['billing_address_1']);
        $order->set_billing_address_2($billing_data['billing_address_num']);
        $order->set_billing_city($billing_data['billing_city']);
        $order->set_billing_postcode($billing_data['billing_postcode']);
        $order->set_billing_country($billing_data['billing_country']);
        $order->set_billing_email($billing_data['email']);
        $order->set_billing_phone($billing_data['billing_phone']);
        $order->set_billing_first_name($billing_data['first_name']);
        $order->set_billing_last_name($billing_data['last_name']);
        $order->update_meta_data('_billing_codice_fiscale', $billing_data['codice_fiscale']);

        $order->save();


        // Invia la mail di conferma ordine (opzionale)
        wc_mail($user_id, 'Conferma ordine', 'Il tuo ordine è stato creato con successo.');

        // Ottieni il link di download
        $downloads = $order->get_downloadable_items();
        if (!empty($downloads)) {
            $download_url = $downloads[0]['download_url']; // Link unico generato da WooCommerce
        }


            

        $order_id = $order->get_id();
        $vendor_id = get_post($product_id)->post_author;

        $data_log = [
            'description' => 'Download del file ' . get_the_title($product_id),
            'order_id' => $order_id,
            'internal_note' => 'Download del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id,
        ];


        $costo_punti_pro = (int)(get_post_meta($product_id,'_costo_in_punti_pro', true));
        $costo_punti_blu = (int)(get_post_meta($product_id,'_costo_in_punti_blu', true));
        
        set_id_venditore($order_id, $vendor_id);

        $costo_in_euro = 0;
        $guadagno_venditore = 0;
        $commissione = 0;

        if($costo_punti_pro>0){

            try {
                $risultato_rimozione_punti = $sistemiPunti['pro']->rimuovi_punti(get_current_user_id(  ), $costo_punti_pro, $data_log);
            } catch (Exception $e) {
                $order->delete(true);
                wp_send_json_error(array('message' => $e->getMessage()));
            }
            
            error_log("Risultato rimozione punti:" . print_r($risultato_rimozione_punti, true));
            error_log('COSTO IN PUNTI PRO Download del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id . ' - Utente: ' . get_current_user_id(  ));
            $costo_in_euro = $risultato_rimozione_punti['valore_in_euro'];
            
            $guadagno_venditore = calcola_guadagno_venditore($costo_in_euro, $vendor_id);
            $commissione = calcola_commissione_vendita($costo_in_euro, $vendor_id);                
            
            set_guadagno_lordo_venditore($order_id, $guadagno_venditore + $commissione);
            set_guadagno_netto_venditore($order_id, $guadagno_venditore);
            set_commissione_minedocs($order_id, $commissione);
            set_richiesta_fattura_venditore($order_id, 'non_richiesta');                
            
            error_log('CONVERSIONE PUNTI IN DENARO Download del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id . ' - Utente: ' . get_current_user_id(  ));

            // Gestione income del venditore
            $data_vendor_log = [
                'amount' => $guadagno_venditore,
                'description' => 'Vendita del file ' . get_the_title($product_id) . ' Guadagno venditore: ' . $guadagno_venditore . ' - Commissione: ' . $commissione,
                'order_id' => $order->get_id(),
                'internal_note' => 'Vendita del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id,
                'hidden_to_user' => $guadagno_venditore == 0 ? true : false,//per l'utente queste vendite non rappresentano nè un'entrata nè un'uscita => non le mostro
            ];

            $data_commissione_log = [
                'amount' => $commissione,
                'description' => 'Commissione MineDocs per vendita del file ' . get_the_title($product_id),
                'order_id' => $order->get_id(),
                'internal_note' => 'Commissione MineDocs per vendita del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id,
                'hidden_to_user' => false,
                ];

            aggiungi_saldo_venditore($product_id, $guadagno_venditore, $data_vendor_log);
            registra_commissione_vendita($product_id,  $data_commissione_log);
            error_log('Vendita del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id . ' - Utente: ' . get_current_user_id(  ));

        
        }

        if($costo_punti_blu>0){
            try {
                $risultato_rimozione_punti = $sistemiPunti['blu']->rimuovi_punti(get_current_user_id(  ), $costo_punti_blu, $data_log);
                $sistemiPunti['blu']->aggiungi_punti($vendor_id, GUADAGNO_PUNTI_BLU_DOWNLOAD_DOCUMENTO, $data_log);
            } catch (Exception $e) {
                $order->delete(true);
                wp_send_json_error(array('message' => $e->getMessage()));
            }
            //$sistemiPunti['blu']->rimuovi_punti(get_current_user_id(  ), $costo_punti_blu, $data_log);
            error_log('COSTO IN PUNTI BLU Download del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id . ' - Utente: ' . get_current_user_id(  ));
        }

        

        error_log('Download del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id . ' - Utente: ' . get_current_user_id(  ));
        // Rispondi con il link di download*/
        
        
        
        wp_send_json_success(array('download_url' => $download_url));


    } else {

        error_log('Errore nel download del file ' . get_the_title($product_id) . ' - ID Prodotto: ' . $product_id . ' - Utente: ' . get_current_user_id(  ));

        // Se i controlli falliscono, ritorna un messaggio di errore
        wp_send_json_error(array('message' => $error, 'error_code' => $error_code, 'user_point' => $user_point));
    }
    

    
}
add_action('wp_ajax_controllo_download', 'controllo_download');
add_action('wp_ajax_nopriv_controllo_download', 'controllo_download'); // Per utenti non loggati


add_action( 'woocommerce_download_product', 'elimina_ordine_scaricando_prodotto', 10, 6 );
function elimina_ordine_scaricando_prodotto( $download_user_id, $order_key, $product_id, $user_id, $user_download_id, $order_id ) {


    $product = wc_get_product($product_id);
    $user_id = get_current_user_id();

    if ($product->is_downloadable() && get_post($product_id)->post_author == $user_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $order->delete(true);
        }
    }
}

function user_has_purchased_product($user_id, $product_id) {
    // Ottieni tutti gli ordini dell'utente
    $customer_orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status' => array('completed') // Stati rilevanti degli ordini
    ));

    //error_log(var_dump($customer_orders));
    // Controlla ogni ordine per vedere se il prodotto è incluso
    foreach ($customer_orders as $order) {
        foreach ($order->get_items() as $item) {
            //error_log($item);
            if ($item->get_product_id() == $product_id) {
                return true; // Prodotto trovato in un ordine
            }
        }
    }

    return false; // Nessun ordine trovato con il prodotto specificato
}

function download_from_owner_link($product_id, $user_id) {
    $token = hash('sha256', $product_id . $user_id . 'download_from_owner');
    $download_url = add_query_arg(array(
        //'download_file' => $product_id,
        'download_file' => get_product_hash_id($product_id),
        //'user_id' => $user_id,
        'token' => $token
    ), home_url('/download-owner-file/'));

    return $download_url;
}

add_action('init', 'handle_file_download');

function handle_file_download() {
    if (isset($_GET['download_file']) && isset($_GET['token'])) {
        $product_hash_id = sanitize_text_field($_GET['download_file']);
        $product_id = get_product_id_by_hash($product_hash_id);
        //$product_id = intval($_GET['download_file']);
        //$user_id = intval($_GET['user_id']);
        $user_id = get_current_user_id();
        $token = sanitize_text_field($_GET['token']);

        // Verifica il token
        $expected_token = hash('sha256', $product_id . $user_id . 'download_from_owner');
        if ($token !== $expected_token) {
            wp_die('Token non valido.');
        }

        if (!is_user_logged_in()) {
            wp_die('Devi essere loggato per scaricare questo file.');
        }
        // Verifica se l'utente è l'autore del prodotto
        if (get_post($product_id)->post_author != $user_id) {
            wp_die('Accesso negato.');
        }

        $product = wc_get_product($product_id);
        $downloadeables = $product->get_downloads();
        foreach($downloadeables as $key => $download){
            $file_path = $download['file'];  
            break;
        }

        $file_path = str_replace(home_url('/'), ABSPATH, $file_path);

        /*wp_die(print_r($file_path));
        

        // Ottieni il percorso del file
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/protected/purchaseable_files/' . $product_id . '.pdf';*/

        /*if (!file_exists($file_path)) {
            wp_die('File non trovato.' . $file_path);
        }*/

        // Imposta gli header per il download del file
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        // Leggi il file e invialo al browser
        readfile($file_path);
        exit;
    } 
}




function get_download_code($user_id, $product_id) {
    // Ottieni tutti gli ordini dell'utente
    $customer_orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status' => array('completed')
    ));

    // Controlla ogni ordine
    foreach ($customer_orders as $order) {
        foreach ($order->get_items() as $item) {
            // Verifica se l'articolo è il prodotto scaricabile richiesto
            if ($item->get_product_id() == $product_id) {

                $downloads = $order->get_downloadable_items();
                //wp_send_json_error( $downloads );
                if (!empty($downloads)) {
                    $download_url = $downloads[0]['download_url']; // Link unico generato da WooCommerce
                    return $download_url;
                }
            }
        }
    }

    return false; // Nessun download trovato
}


add_filter( 'woocommerce_downloads_allowed_directories', function( $directories ) {
    $directories[] = WP_CONTENT_DIR . '/uploads/protected/purchaseable_files';
    return $directories;
});


add_filter('woocommerce_download_product_filepath', 'add_watermark_to_pdf', 10, 5);

function add_watermark_to_pdf($file_path, $email_address, $order, $product, $download) {

    // Se l'utente che scarica il file è l'autore del prodotto, non aggiungere la filigrana
    if (get_current_user_id() == get_post($product->id)->post_author) {
        return $file_path;
    }

    $temp_pdf_path = tempnam(sys_get_temp_dir(), 'temp_pdf_') . '.pdf';

    // Ottieni il percorso locale del file
    $upload_dir = wp_upload_dir();
    $local_file_path = $upload_dir['basedir'] . str_replace($upload_dir['baseurl'], '', $file_path);

    if (!file_exists($local_file_path)) {
        throw new Exception('Impossibile trovare il file PDF.');
    }

    error_log("Local file path: " . $local_file_path);
    if (mime_content_type($local_file_path) == 'application/pdf') {
        error_log("Aggiungi filigrana al file PDF: " . $local_file_path);
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pageCount = $pdf->setSourceFile($local_file_path);
        $codice_univoco = $order->get_meta('_unique_code');

        $pdf->SetCreator('minedocs.com');
        //$pdf->SetAuthor(get_author_name( $product->ID ) );
        $pdf->SetAuthor($email_address);
        $pdf->SetTitle($product->get_name());
        $pdf->SetSubject($product->get_description());


        // Aggiungi una prima pagina con il logo e il nome dell'azienda
        $pdf->AddPage('P', 'A4'); // Dimensione standard A4 (puoi personalizzarla se necessario)
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);
    
        // Aggiungi il logo
        $logoWidth = 1393/10;
        $logoHeight = 588/10;
        $logoX = (int) (($pdf->GetPageWidth() - $logoWidth) / 2);
        $logoY = (int) (($pdf->GetPageHeight() / 2) - $logoHeight - 10);
        //$pdf->Image(get_stylesheet_directory() . '/assets/img/logo/book.png', $logoX, $logoY, $logoWidth, $logoHeight, 'PNG');
        $pdf->Image(get_stylesheet_directory() . '/assets/img/logo/logo-minedocs-vect-rect-con-margini-risp.png', $logoX, $logoY, $logoWidth, $logoHeight, 'PNG');


        // Aggiungi il piè di pagina alla nuova pagina
        $pageWidth = $pdf->GetPageWidth();
        $pageHeight = $pdf->GetPageHeight();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetXY($pageWidth / 2 - 30, $pageHeight - 12); // Centrato orizzontalmente
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Write(0, 'File scaricato da minedocs.it');
    
        // Aggiungi tutte le pagine originali e il testo personalizzato
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplIdx = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplIdx);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplIdx);
    
            // Imposta font e testo per la filigrana
            $pdf->SetFont('helvetica', '', 4);
            $pdf->SetAlpha(0.5);
            $pdf->SetTextColor(128, 128, 128, 230);
    
            // Posizioni casuali per il codice univoco
            $pageWidth = $pdf->GetPageWidth();
            $pageHeight = $pdf->GetPageHeight();
            $ripetizioni_codice_pagina = rand(3, 5);
    
            for ($i = 0; $i < $ripetizioni_codice_pagina; $i++) {
                $textWidth = $pdf->GetStringWidth($codice_univoco);
                $textHeight = 10;
                $x = rand(20, $pageWidth - $textWidth - 20);
                $y = rand(20 + ($i / $ripetizioni_codice_pagina) * $pageHeight, (($i + 1) / $ripetizioni_codice_pagina) * $pageHeight - $textHeight - 20);
                $pdf->SetXY($x, $y);
                $pdf->Write(0, $codice_univoco);
            }
    
            // Aggiungi un piè di pagina in fondo
            $pdf->SetAutoPageBreak(false);
            $pdf->SetXY($pageWidth / 2 - 30, $pageHeight - 12);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetAlpha(1);
            $pdf->Write(0, 'File scaricato da minedocs.it');
        }

        $upass=wp_get_current_user()->user_email;
        //$upass='ciaoooooo';
        $pdf->SetProtection(['copy', 'modify', 'extract', 'assemble', 'print-high'], '', '*AOle24!Coglione', 3);
    
        // Salva il file PDF modificato
        $path_info = pathinfo($local_file_path);
        $modified_pdf_path = $path_info['dirname'] . '/' . $path_info['filename'] . '_' . $codice_univoco . '.' . $path_info['extension'];
        $pdf->Output($modified_pdf_path, 'F');
    
        // Imposta un hook per eliminare il file temporaneo dopo il download
        add_action('shutdown', function() use ($modified_pdf_path) {
            if (file_exists($modified_pdf_path)) {
                unlink($modified_pdf_path);
            }
        });

        // Aggiorna il percorso del file da scaricare con il file PDF modificato
        return $modified_pdf_path;
        /*
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($modified_pdf_path) . '"');
        header('Content-Length: ' . filesize($modified_pdf_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        readfile($modified_pdf_path);
        exit;*/
    }




    return $file_path;

}