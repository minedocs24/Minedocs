<?php
/**
 * Verifica se una email è valida e non usa e getta utilizzando l'API di Disify.
 *
 * @param string $email L'indirizzo email da verificare.
 * @return bool True se la mail è valida, False altrimenti.
 */
function verifica_email_valida($email) {
    $api_url = "https://www.disify.com/api/email/";
    error_log("API URL: " . $api_url);
    $endpoint = $api_url . $email;
    error_log("Endpoint URL: " . $endpoint);

    // Inizializza una richiesta cURL
    $ch = curl_init();
    error_log("cURL initialized.");
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    error_log("cURL options set.");

    // Esegui la richiesta e decodifica la risposta
    $response = curl_exec($ch);
    error_log("cURL response: " . ($response !== false ? $response : "false"));
    curl_close($ch);
    error_log("cURL closed.");

    if ($response === false) {
        error_log("Error: cURL request failed.");
        return false; // Errore nella richiesta
    }

    $data = json_decode($response, true);
    error_log("Decoded response: " . print_r($data, true));

    // Controlla se la risposta contiene il campo "disposable"
    if (isset($data['disposable'])) {
        error_log("Disposable field found: " . $data['disposable']);
        return !$data['disposable']; // Inverti la logica
    }

    error_log("Error: Disposable field not found in response.");
    return false; // Default in caso di errore
}