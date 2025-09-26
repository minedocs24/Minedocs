# Sistema di Download Sicuro per Riassunti Generati

## Panoramica

Il sistema implementa un meccanismo di download sicuro per i riassunti generati tramite "Studia con AI", basato sul sistema esistente di download sicuro dei documenti acquistati.

## Componenti Principali

### 1. Backend (PHP)

#### Funzioni Principali

- **`get_summary_download_url($job_id, $user_id)`**: Genera un URL di download sicuro con token
- **`handle_summary_download()`**: Gestisce il download effettivo del file
- **`handle_get_summary_download_url()`**: Endpoint AJAX per ottenere l'URL di download

#### Sicurezza

- **Token di sicurezza**: Generato con `hash('sha256', $job_id . $user_id . 'summary_download_secure')`
- **Verifica utente**: Solo l'utente proprietario del job può scaricare il file
- **Verifica stato**: Solo job completati possono essere scaricati
- **Verifica file**: Controllo dell'esistenza del file prima del download

### 2. Frontend (JavaScript)

#### Funzioni Principali

- **`downloadSummary(jobId)`**: Gestisce la richiesta di download sicuro
- **`getActionButtons(job)`**: Genera i pulsanti per le azioni sui job

#### Flusso di Download

1. L'utente clicca su "Scarica" nella sezione "Le mie generazioni"
2. Il sistema genera un URL di download sicuro tramite AJAX
3. Il browser viene reindirizzato all'URL sicuro
4. Il file viene scaricato con gli header appropriati

## Struttura del Database

### Tabella `wp_minedocs_studia_ai_jobs`

```sql
CREATE TABLE wp_minedocs_studia_ai_jobs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    request_type varchar(50) NOT NULL DEFAULT 'summary',
    request_date datetime NOT NULL,
    file_id bigint(20) NOT NULL,
    file_path varchar(500) NOT NULL,
    request_params longtext NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'pending',
    priority int(11) NOT NULL DEFAULT 0,
    started_at datetime NULL,
    completed_at datetime NULL,
    result_file varchar(500) NULL,  -- Percorso del file generato
    result_url varchar(500) NULL,   -- URL del file (deprecato)
    error_message text NULL,
    retry_count int(11) NOT NULL DEFAULT 0,
    max_retries int(11) NOT NULL DEFAULT 3,
    created_at datetime NOT NULL,
    updated_at datetime NOT NULL,
    PRIMARY KEY (id)
);
```

## Flusso Completo

### 1. Generazione Riassunto

1. L'utente carica un documento e configura i parametri
2. Viene creato un job nella coda con status 'pending'
3. Il servizio Flask elabora il documento in background
4. Al completamento, Flask chiama il callback `summary_completed`
5. Il job viene aggiornato con status 'completed' e `result_file`

### 2. Download Sicuro

1. L'utente visualizza i job completati in "Le mie generazioni"
2. Clicca su "Scarica" per un job completato
3. Il sistema genera un URL sicuro con token
4. Il browser scarica il file tramite l'URL sicuro
5. Il file viene servito con header appropriati

## Sicurezza

### Token di Sicurezza

```php
$token = hash('sha256', $job_id . $user_id . 'summary_download_secure');
```

Il token è unico per ogni combinazione job-utente e include una stringa segreta.

### Verifiche di Sicurezza

1. **Autenticazione**: Solo utenti loggati
2. **Autorizzazione**: Solo il proprietario del job
3. **Validazione token**: Verifica del token di sicurezza
4. **Stato job**: Solo job completati
5. **Esistenza file**: Verifica che il file esista

## Integrazione con Sistema Esistente

### Differenze dal Download Documenti

- **Nessun controllo punti**: I riassunti sono già pagati tramite l'acquisto del documento
- **Nessun watermark**: I riassunti sono generati per l'utente specifico
- **URL dinamico**: Ogni download genera un nuovo URL sicuro
- **Gestione asincrona**: I riassunti sono generati in background

### Somiglianze

- **Token di sicurezza**: Stesso meccanismo di token
- **Verifica utente**: Stessa logica di autorizzazione
- **Header di download**: Stessi header per il download
- **Gestione errori**: Stessa gestione degli errori

## Configurazione

### Nonce per AJAX

```php
'nonce_summary_download' => wp_create_nonce('nonce_summary_download')
```

### Handler AJAX

```php
add_action('wp_ajax_download_summary', 'handle_summary_download');
add_action('wp_ajax_get_summary_download_url', 'handle_get_summary_download_url');
```

## Testing

### Funzione di Test

```php
function test_summary_download_system() {
    // Crea un job di test
    // Simula il completamento
    // Testa la generazione dell'URL
    // Verifica il download
}
```

### Attivazione Test

```php
// Uncomment per testare il sistema (solo per amministratori)
// add_action('init', 'test_summary_download_system');
```

## Troubleshooting

### Problemi Comuni

1. **"Token non valido"**: Verificare che l'utente sia loggato e sia il proprietario
2. **"File non trovato"**: Verificare che il job sia completato e il file esista
3. **"Accesso negato"**: Verificare l'autorizzazione dell'utente
4. **"Job non completato"**: Verificare lo stato del job

### Log di Debug

```php
error_log('Test download URL generato con successo: ' . $download_url);
error_log('Errore nella generazione dell\'URL di download di test');
```

## Manutenzione

### Pulizia File

I file generati sono salvati in `wp-content/uploads/protected/ai/` e dovrebbero essere puliti periodicamente.

### Pulizia Job

I job vecchi vengono puliti automaticamente tramite `studia_ai_cleanup_old_jobs()`.

## Estensioni Future

1. **Watermark personalizzati**: Aggiungere watermark ai riassunti
2. **Formati multipli**: Supportare PDF, DOCX, TXT
3. **Compressione**: Comprimere i file per ridurre lo spazio
4. **CDN**: Utilizzare un CDN per i file generati
5. **Analytics**: Tracciare i download per analytics 