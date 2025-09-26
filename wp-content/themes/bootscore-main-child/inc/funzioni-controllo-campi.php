<?php

/* * Funzione per validare il numero di telefono
 * @param string $telefono Il numero di telefono da validare
 * @return bool True se il numero è valido, false altrimenti
 */
function valida_numero_telefono($telefono) {
    if (empty($telefono)) {
        return true; // Il numero di telefono può essere vuoto
    }
    // Verifica se il numero di telefono è valido
    if (preg_match('/^\+?[0-9]{10,15}$/', $telefono)) { 
        return true;
    } else {
        return false;
    }
}




/* * Funzione per validare l'email
 * @param string $email L'email da validare
 * @return bool True se l'email è valida, false altrimenti
 */
function valida_email($email) {
    // Verifica se l'email è valida
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}


/** Funzione per validare il nome e cognome
 * @param string $nome Il nome e cognome da validare
 * @return bool True se il nome e cognome sono validi, false altrimenti
 */
function valida_nome_cognome($nome) {
    if (empty($nome)) {
        return false; // Il nome e cognome non possono essere vuoti
    }

    if (strlen($nome) > 50) {
        return false; // Il nome e cognome non possono superare i 50 caratteri
    }
    error_log('Nome e cognome: ' . $nome);

    // Verifica se il nome e cognome sono validi
    if (preg_match("/^(?=.*[a-zA-ZÀ-ÖØ-öø-ÿ])[a-zA-ZÀ-ÖØ-öø-ÿ'’\-\s]+$/u", stripslashes($nome)) && !preg_match("/['’\-\s]{2,}/", stripslashes($nome))) {
        return true;
    } else {
        return false;
    }
}


/**
 *  Funzione per validare un numero intero
 * Questa funzione verifica se un numero è un intero valido.
 * @param mixed $numero
 * @return bool
 */
function valida_numero_intero($numero) {
    // Verifica se il numero è un intero
    if (filter_var($numero, FILTER_VALIDATE_INT) !== false) {
        return true;
    } else {
        return false;
    }
}

/**
 * Funzione per validare il CAP (codice postale)
 * @param string $cap Il codice postale da validare
 * @return bool True se il CAP è valido, false altrimenti
 */
function valida_cap($cap) {
    // Verifica se il CAP è vuoto
    if (empty($cap)) {
        return false; // Il CAP non può essere vuoto
    }
    // Verifica se il CAP è valido (5 cifre)
    if (preg_match('/^\d{5}$/', $cap)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Funzione per validare il numero civico
 * @param string $numero_civico Il numero civico da validare
 * @return bool True se il numero civico è valido, false altrimenti
 */
function valida_numero_civico($numero_civico) {
    // Verifica se il numero civico è vuoto
    if (empty($numero_civico)) {
        return false; // Il numero civico non può essere vuoto
    }
    // Verifica se il numero civico è valido (numeri e opzionalmente lettere, con un limite massimo di 10 caratteri)
    if (preg_match('/^\d{1,9}[a-zA-Z]?$/', $numero_civico)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Funzione per validare un codice fiscale o una partita IVA italiana
 * @param string $codice Il codice fiscale o partita IVA da validare
 * @param string $nazione La nazione di riferimento
 * @return bool True se il codice è valido, false altrimenti
 */
function valida_codice_fiscale_o_partita_iva($codice, $nazione) {
    // Controlla se la nazione è Italia
    if (strtoupper($nazione) === 'ITALIA' || strtoupper($nazione) === 'IT') {
        // Verifica se è un codice fiscale valido
        if (preg_match('/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[A-EHLMPR-T][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z]$/i', $codice)) {
            return true;
        }
        // Verifica se è una partita IVA valida (11 cifre)
        if (preg_match('/^\d{11}$/', $codice)) {
            return true;
        }
        return false;
    }
    // Se la nazione non è Italia, verifica che il codice sia una stringa alfanumerica con una lunghezza massima di 20 caratteri
    if (preg_match('/^[a-zA-Z0-9]{1,20}$/', $codice)) {
        return true;
    }
    return false;
}

/**
 * Funzione per validare un indirizzo
 * @param string $indirizzo L'indirizzo da validare
 * @return bool True se l'indirizzo è valido, false altrimenti
 */
function valida_indirizzo($indirizzo) {
    // Verifica se l'indirizzo è vuoto
    if (empty($indirizzo)) {
        return false; // L'indirizzo non può essere vuoto
    }

    // Verifica se l'indirizzo rispetta i criteri (caratteri alfanumerici, spazi, virgole, punti e trattini)
    if (preg_match('/^[a-zA-Z0-9À-ÖØ-öø-ÿ\s,.\'-]{1,100}$/u', $indirizzo)) {
        return true;
    } else {
        return false;
    }
}


function valida_stringa( $stringa, $lunghezza_minima = 1, $lunghezza_massima = 255, $pattern = '/^[a-zA-Z0-9\s]+$/' ) {
    if ( empty( $stringa ) ) {
        if ( $lunghezza_minima > 0 ) {
            return false; // La stringa non può essere vuota se la lunghezza minima è maggiore di 0
        } else {
            return true; // La stringa è vuota ma la lunghezza minima è 0, quindi è valida
        }
    }

    // Verifica se la stringa è valida
    if ( is_string( $stringa ) && 
         strlen( $stringa ) >= $lunghezza_minima && 
         strlen( $stringa ) <= $lunghezza_massima && 
         preg_match(  $pattern, $stringa) // Controlla che non ci siano caratteri speciali
    ) {
        return true;
    } else {
        return false;
    }
}


function valida_nazione( $nazione ) {
    // Verifica se la nazione è valida
    $nazioni = getNationArray();
    $nazione_uppercase = strtoupper($nazione);
    foreach ( $nazioni as $key => $value ) {
        if ( strtoupper($key) === $nazione_uppercase || strtoupper($value) === $nazione_uppercase ) {
            return true;
        }
    }
    return false;
}

function verifica_complessita_password($password) {
    $lunghezza_minima = 8;
    $lunghezza_massima = 50;
    $deve_contenere_lettere = preg_match('/[a-zA-Z]/', $password);
    $deve_contenere_lettere_maiuscole = preg_match('/[A-Z]/', $password);
    $deve_contenere_numeri = preg_match('/\d/', $password);
    $deve_contenere_caratteri_speciali = preg_match('/[^a-zA-Z\d]/', $password);

    if (strlen($password) < $lunghezza_minima) {
        return false;
    }

    if (strlen($password) > $lunghezza_massima) {
        return false;
    }

    if (!$deve_contenere_lettere || !$deve_contenere_lettere_maiuscole || !$deve_contenere_numeri || !$deve_contenere_caratteri_speciali) {
        return false;
    }

    return true;
}

function valida_lingua_utente($lingua) {
    // Verifica se la lingua è vuota
    if (empty($lingua)) {
        return true; // La lingua può essere vuota
    }
    // Verifica se la lingua è valida
    $lingue = getLanguagesArray();
    $lingua_uppercase = strtoupper($lingua);
    foreach ($lingue as $key => $value) {
        if (strtoupper($key) === $lingua_uppercase || strtoupper($value) === $lingua_uppercase) {
            return true;
        }
    }
    return false;
}