<?php
/**
 * Callback per gestire le risposte da Flask
 */

// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestisce il callback di completamento del riassunto da Flask
 */
function handle_summary_completed_callback() {
    // Log della richiesta ricevuta
    error_log('Callback Flask ricevuto: ' . file_get_contents('php://input'));

    // Decodifica il payload JSON
    $json_payload = file_get_contents('php://input');
    $payload = json_decode($json_payload, true);
    error_log('Payload: ' . print_r($payload, true));
    
    // Verifica che il payload sia valido
    if (!$payload || !isset($payload['job_id'])) {
        error_log('Callback Flask: payload invalido o job_id mancante');
        wp_send_json_error(['message' => 'Payload invalido o job_id mancante']);
        return;
    }
    
    $job_id = intval($payload['job_id']);
    
    // Verifica che il job esista
    $job = studia_ai_get_job($job_id);
    if (!$job) {
        error_log("Callback Flask: job $job_id non trovato");
        wp_send_json_error(['message' => "Job $job_id non trovato"]);
        return;
    }
    
    // Controlla l'esito dell'elaborazione
    $esito_status = $payload['esito']['status'] ?? 'unknown';
    
    if ($esito_status === 'success') {
        // Elaborazione completata con successo
        $pdf_riassunto_path = $payload['file']['pdf_riassunto'] ?? '';
        $txt_riassunto_path = $payload['file']['txt_riassunto'] ?? '';
        
        // Crea URL di download per il PDF (assumendo che sia accessibile via web)
        $pdf_url = '';
        if ($pdf_riassunto_path) {
            // Costruisci l'URL assumendo che i file siano serviti da Flask
            $pdf_url = FLASK_SUMMARY_API_URL_DOWNLOAD . urlencode($pdf_riassunto_path);
        }
        
        // Prepara i dati aggiuntivi per l'aggiornamento
        $additional_data = array(
            'result_file' => $pdf_riassunto_path,
            'result_url' => $pdf_url
        );
        
        // Aggiorna il job come completato
        $result = studia_ai_update_job_status($job_id, 'completed', $additional_data);
        
        if ($result) {
            error_log("Job $job_id completato con successo. PDF: $pdf_riassunto_path");
            wp_send_json_success([
                'message' => 'Job completato con successo',
                'job_id' => $job_id,
                'pdf_path' => $pdf_riassunto_path,
                'pdf_url' => $pdf_url
            ]);
        } else {
            error_log("Errore nell'aggiornamento del job $job_id");
            wp_send_json_error(['message' => "Errore nell'aggiornamento del job $job_id"]);
        }
        
    } else {
        // Errore nell'elaborazione
        $error_message = $payload['esito']['message'] ?? 'Errore sconosciuto nell\'elaborazione';
        
        // Aggiorna il job come errore
        $additional_data = array(
            'error_message' => $error_message
        );
        
        $result = studia_ai_update_job_status($job_id, 'error', $additional_data);
        
        if ($result) {
            error_log("Job $job_id completato con errore: $error_message");
            wp_send_json_success([
                'message' => 'Job aggiornato con errore',
                'job_id' => $job_id,
                'error' => $error_message
            ]);
        } else {
            error_log("Errore nell'aggiornamento del job $job_id con errore");
            wp_send_json_error(['message' => "Errore nell'aggiornamento del job $job_id"]);
        }
    }
}

// Registra il callback come azione AJAX (accessibile senza autenticazione per Flask)
add_action('wp_ajax_nopriv_summary_completed', 'handle_summary_completed_callback');
add_action('wp_ajax_summary_completed', 'handle_summary_completed_callback');
