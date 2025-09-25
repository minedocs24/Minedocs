<?php
/**
 * Gestione Coda Job per Studia con AI
 * 
 * Questo file gestisce la coda dei job di generazione riassunti, quiz, ecc.
 * tramite una tabella dedicata nel database WordPress.
 */

// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Assicura che la costante della tabella esista (fallback)
if (!defined('TABELLA_STUDIA_AI_JOBS')) {
    global $wpdb;
    if (isset($wpdb) && !empty($wpdb->prefix)) {
        define('TABELLA_STUDIA_AI_JOBS', $wpdb->prefix . 'studia_ai_jobs');
    } else {
        define('TABELLA_STUDIA_AI_JOBS', 'wp_studia_ai_jobs');
    }
}

/**
 * Crea la tabella della coda job all'attivazione del tema
 */
function studia_ai_create_jobs_table() {
    error_log('studia_ai_create_jobs_table');
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        request_type varchar(50) NOT NULL DEFAULT 'summary',
        request_date datetime NOT NULL,
        file_id bigint(20) NOT NULL,
        file_path varchar(500) NOT NULL,
        request_params longtext NOT NULL,
        points_cost int(11) DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        priority int(11) NOT NULL DEFAULT 0,
        started_at datetime NULL,
        completed_at datetime NULL,
        result_file varchar(500) NULL,
        result_url varchar(500) NULL,
        error_message text NULL,
        retry_count int(11) NOT NULL DEFAULT 0,
        max_retries int(11) NOT NULL DEFAULT 3,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY status (status),
        KEY request_type (request_type),
        KEY priority (priority),
        KEY request_date (request_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
// add_action('init', 'studia_ai_create_jobs_table');

/**
 * Inserisce un nuovo job nella coda
 */
function studia_ai_insert_job($user_id, $request_type, $file_id, $file_path, $params, $priority = 0) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    $current_time = current_time('mysql');
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'request_type' => $request_type,
            'request_date' => $current_time,
            'file_id' => $file_id,
            'file_path' => $file_path,
            'request_params' => json_encode($params),
            'points_cost' => isset($params['points_cost']) ? intval($params['points_cost']) : null,
            'status' => 'pending',
            'priority' => $priority,
            'created_at' => $current_time,
            'updated_at' => $current_time
        ),
        array('%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Errore nell\'inserimento del job nel database');
    }
    
    return $wpdb->insert_id;
}

/**
 * Ottiene tutti i job di un utente
 */
function studia_ai_get_user_jobs($user_id, $limit = 50, $offset = 0) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE user_id = %d 
         ORDER BY request_date DESC 
         LIMIT %d OFFSET %d",
        $user_id, $limit, $offset
    );
    
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Ottieni il conteggio totale dei job di un utente
 */
function studia_ai_get_user_jobs_count($user_id) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
        $user_id
    );
    
    return $wpdb->get_var($sql);
}

/**
 * Ottiene tutti i job pending di un utente
 */
function studia_ai_get_user_pending_jobs($user_id) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE user_id = %d AND status IN ('pending', 'processing') 
         ORDER BY priority DESC, request_date ASC",
        $user_id
    );
    
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Ottiene un job specifico per ID
 */
function studia_ai_get_job($job_id) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $job_id
    );
    
    return $wpdb->get_row($sql, ARRAY_A);
}

/**
 * Aggiorna lo stato di un job
 */
function studia_ai_update_job_status($job_id, $status, $additional_data = array()) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    $current_time = current_time('mysql');
    
    $update_data = array(
        'status' => $status,
        'updated_at' => $current_time
    );
    
    // Aggiungi timestamp specifici in base allo stato
    switch ($status) {
        case 'processing':
            $update_data['started_at'] = $current_time;
            break;
        case 'completed':
        case 'error':
            $update_data['completed_at'] = $current_time;
            break;
    }
    
    // Aggiungi dati aggiuntivi se forniti
    if (!empty($additional_data)) {
        $update_data = array_merge($update_data, $additional_data);
    }
    
    $result = $wpdb->update(
        $table_name,
        $update_data,
        array('id' => $job_id),
        null,
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Ottiene tutti i job pending per l'elaborazione (per Flask)
 */
function studia_ai_get_pending_jobs($limit = 10) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE status = 'pending' 
         ORDER BY priority DESC, request_date ASC 
         LIMIT %d",
        $limit
    );
    
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Ottiene statistiche dei job per un utente
 */
function studia_ai_get_user_job_stats($user_id) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT 
            status,
            COUNT(*) as count,
            request_type,
            COUNT(CASE WHEN request_type = 'summary' THEN 1 END) as summary_count,
            COUNT(CASE WHEN request_type = 'quiz' THEN 1 END) as quiz_count
         FROM $table_name 
         WHERE user_id = %d 
         GROUP BY status, request_type",
        $user_id
    );
    
    $results = $wpdb->get_results($sql, ARRAY_A);
    
    $stats = array(
        'total' => 0,
        'pending' => 0,
        'processing' => 0,
        'completed' => 0,
        'error' => 0,
        'by_type' => array()
    );
    
    foreach ($results as $row) {
        $stats['total'] += $row['count'];
        $stats[$row['status']] += $row['count'];
        
        if (!isset($stats['by_type'][$row['request_type']])) {
            $stats['by_type'][$row['request_type']] = 0;
        }
        $stats['by_type'][$row['request_type']] += $row['count'];
    }
    
    return $stats;
}

/**
 * Elimina un job (solo per l'utente proprietario)
 */
function studia_ai_delete_job($job_id, $user_id) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $result = $wpdb->delete(
        $table_name,
        array(
            'id' => $job_id,
            'user_id' => $user_id
        ),
        array('%d', '%d')
    );
    
    return $result !== false;
}

/**
 * Incrementa il contatore di retry per un job
 */
function studia_ai_increment_retry($job_id) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "UPDATE $table_name 
         SET retry_count = retry_count + 1, 
             updated_at = %s 
         WHERE id = %d",
        current_time('mysql'),
        $job_id
    );
    
    return $wpdb->query($sql);
}

/**
 * Ottiene job che hanno superato il numero massimo di retry
 */
function studia_ai_get_failed_jobs() {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = "SELECT * FROM $table_name 
            WHERE retry_count >= max_retries AND status = 'error' 
            ORDER BY updated_at DESC";
    
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Pulisce i job vecchi (più di 30 giorni)
 */
function studia_ai_cleanup_old_jobs($days = 30) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "DELETE FROM $table_name 
         WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY) 
         AND status IN ('completed', 'error')",
        $days
    );
    
    return $wpdb->query($sql);
}

/**
 * Ottiene job per tipo di richiesta
 */
function studia_ai_get_jobs_by_type($user_id, $request_type, $limit = 20) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE user_id = %d AND request_type = %s 
         ORDER BY request_date DESC 
         LIMIT %d",
        $user_id, $request_type, $limit
    );
    
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Esegue il rimborso dei punti e marca il job come fallito
 */
function studia_ai_refund_and_fail_job($job) {
    if (empty($job) || !isset($job['id'])) return false;
    global $wpdb;
    $table_name = TABELLA_STUDIA_AI_JOBS;

    $job_id = intval($job['id']);
    $user_id = intval($job['user_id']);
    $points = isset($job['points_cost']) ? intval($job['points_cost']) : 0;

    // Determina il nome del file per log e email
    $file_name_for_log = '';
    if (!empty($job['file_id'])) {
        $attachment = get_post($job['file_id']);
        if ($attachment && !empty($attachment->post_title)) {
            $file_name_for_log = $attachment->post_title;
        }
    }
    if (empty($file_name_for_log) && !empty($job['file_path'])) {
        $file_name_for_log = basename($job['file_path']);
    }

    // Aggiorna lo stato del job a error
    $additional = array('error_message' => 'Timeout: job ritenuto bloccato e annullato (rimborso effettuato)');
    studia_ai_update_job_status($job_id, 'error', $additional);

    // Rimborso punti se presente sistema
    if ($points > 0 && function_exists('get_sistema_punti')) {
        try {
            $sistema_pro = get_sistema_punti('pro');
            if ($sistema_pro) {
                $data_log = array(
                    'description' => 'Rimborso per generazione fallita per "' . sanitize_text_field($file_name_for_log) . '"',
                    'hidden_to_user' => false,
                );
                $sistema_pro->aggiungi_punti($user_id, $points, $data_log);
            }
        } catch (Exception $e) {
            error_log('Errore durante il rimborso punti per job ' . $job_id . ': ' . $e->getMessage());
        }
    }

    // Invia email all'utente informandolo del rimborso
    try {
        $user = get_user_by('id', $user_id);
        if ($user && isset($user->user_email)) {
            $to = $user->user_email;
            // Prepara variabili per template
            $points_refunded = $points;
            // Recupera path template
            $template = locate_template('inc/email-templates/email-ai-generation-failed-refund.php');
            if ($template) {
                ob_start();
                // rendi disponibili le variabili nel template
                include $template;
                $html = ob_get_clean();
                $subject = esc_html(get_bloginfo('name')) . ' - Rimborso punti per generazione non riuscita';
                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($to, $subject, $html, $headers);
            }
        }
    } catch (Exception $e) {
        error_log('Errore invio mail rimborso per job ' . $job_id . ': ' . $e->getMessage());
    }

    return true;
}

/**
 * Controlla e gestisce job bloccati (più di 10 minuti in pending/processing)
 * Esegue rimborso punti e marca come failed.
 * Viene eseguita al massimo ogni 5 minuti usando transient.
 */
function studia_ai_check_and_refund_stuck_jobs() {
    error_log('studia_ai_check_and_refund_stuck_jobs (cron)');

    global $wpdb;
    $table_name = TABELLA_STUDIA_AI_JOBS;

    // Seleziona job pending/processing più vecchi di 10 minuti (usa UTC per evitare mismatch timezone)
    $sql = "SELECT * FROM $table_name WHERE status IN ('pending','processing') AND request_date < DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
    $jobs = $wpdb->get_results($sql, ARRAY_A);
    error_log('jobs: ' . print_r($jobs, true));
    if (empty($jobs)) return;

    foreach ($jobs as $job) {
        try {
            studia_ai_refund_and_fail_job($job);
            error_log('Job ' . intval($job['id']) . ' considerato bloccato e rimborsato.');
        } catch (Exception $e) {
            error_log('Errore durante la gestione job bloccato ' . intval($job['id']) . ': ' . $e->getMessage());
        }
    }
}

// Registrazione WP-Cron: intervallo personalizzato da 5 minuti e hook cron

add_filter('cron_schedules', 'studia_ai_cron_schedules');
function studia_ai_cron_schedules($schedules) {
    if (!isset($schedules['ten_minutes'])) {
        $schedules['ten_minutes'] = array(
            'interval' => 10 * 60,
            'display'  => 'Every Ten Minutes'
        );
    }
    return $schedules;
}

add_action('init', 'studia_ai_schedule_cron');
function studia_ai_schedule_cron() {
    $hook = 'studia_ai_check_stuck_jobs_cron';

    // check if there is any scheduled event for the hook with recurrence 'ten_minutes'
    $needs_schedule = true;
    if ( wp_next_scheduled( $hook ) ) {
        // inspect cron array to find recurrence
        if ( function_exists('_get_cron_array') ) {
            $crons = _get_cron_array();
            foreach ( (array) $crons as $timestamp => $events ) {
                if ( empty( $events ) || ! is_array( $events ) ) continue;
                foreach ( $events as $event_hook => $event_data ) {
                    if ( $event_hook !== $hook ) continue;
                    // if any event uses ten_minutes recurrence, we don't need to reschedule
                    if ( isset( $event_data['schedule'] ) && $event_data['schedule'] === 'ten_minutes' ) {
                        $needs_schedule = false;
                        break 2;
                    }
                }
            }
        }
    }

    if ( $needs_schedule ) {
        // remove any existing scheduled instances for the hook to avoid duplicates
        if ( function_exists('wp_clear_scheduled_hook') ) {
            wp_clear_scheduled_hook( $hook );
        } else {
            $timestamp = wp_next_scheduled( $hook );
            if ( $timestamp ) wp_unschedule_event( $timestamp, $hook );
        }

        // schedule with 10 minutes recurrence
        if ( ! wp_next_scheduled( $hook ) ) {
            wp_schedule_event( time(), 'ten_minutes', $hook );
        }
    }
}

add_action('studia_ai_check_stuck_jobs_cron', 'studia_ai_check_and_refund_stuck_jobs');

// Pulizia dello schedule al cambio tema
add_action('switch_theme', 'studia_ai_clear_cron');
function studia_ai_clear_cron() {
    $timestamp = wp_next_scheduled('studia_ai_check_stuck_jobs_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'studia_ai_check_stuck_jobs_cron');
    }
}

/**
 * Ottiene job completati recentemente
 */
function studia_ai_get_recent_completed_jobs($user_id, $hours = 24) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE user_id = %d 
         AND status = 'completed' 
         AND completed_at > DATE_SUB(NOW(), INTERVAL %d HOUR) 
         ORDER BY completed_at DESC",
        $user_id, $hours
    );
    
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Verifica se un utente ha job in elaborazione
 */
function studia_ai_user_has_processing_jobs($user_id) {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name 
         WHERE user_id = %d AND status IN ('pending', 'processing')",
        $user_id
    ));
    
    return $count > 0;
}

/**
 * Ottiene il numero di job in coda per tipo
 */
function studia_ai_get_queue_count_by_type($request_type = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'studia_ai_jobs';
    
    if ($request_type) {
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE status = 'pending' AND request_type = %s",
            $request_type
        );
    } else {
        $sql = "SELECT request_type, COUNT(*) as count 
                FROM $table_name 
                WHERE status = 'pending' 
                GROUP BY request_type";
    }
    
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Controlla se esiste già un riassunto completato per il file con parametri identici
 */
function studia_ai_find_existing_summary($file_id, $config, $request_type = 'summary') {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    
    // Normalizza la configurazione per il confronto
    $normalized_config = studia_ai_normalize_config($config);
    $config_json = json_encode($normalized_config);
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE file_id = %d 
         AND request_type = %s 
         AND status = 'completed' 
         AND result_file IS NOT NULL 
         AND result_file != ''
         ORDER BY completed_at DESC",
        $file_id,
        $request_type
    );
    
    $existing_jobs = $wpdb->get_results($sql, ARRAY_A);
    
    // Controlla se esiste un job con configurazione identica
    foreach ($existing_jobs as $job) {
        $job_config = json_decode($job['request_params'], true);
        $normalized_job_config = studia_ai_normalize_config($job_config);
        $job_config_json = json_encode($normalized_job_config);
        
        if ($config_json === $job_config_json) {
            return $job;
        }
    }
    
    return null;
}

/**
 * Normalizza la configurazione per confronti consistenti
 */
function studia_ai_normalize_config($config) {
    // Rimuovi parametri che non influenzano il risultato o sono vuoti
    $normalized = array();
    
    // Parametri principali obbligatori
    $main_params = array('mode', 'detail_level', 'language', 'reading_time');
    foreach ($main_params as $param) {
        if (isset($config[$param]) && !empty($config[$param])) {
            $normalized[$param] = $config[$param];
        }
    }
    
    // Parametri avanzati opzionali (solo se specificati)
    $advanced_params = array('max_words', 'min_words', 'include_quotes', 'tone', 'comprehension_level', 'summary_objective');
    foreach ($advanced_params as $param) {
        if (isset($config[$param]) && !empty($config[$param]) && $config[$param] !== '') {
            $normalized[$param] = $config[$param];
        }
    }
    
    // Ordina le chiavi per confronto consistente
    ksort($normalized);
    
    return $normalized;
}

/**
 * Crea un job duplicato completato che riutilizza un riassunto esistente
 */
function studia_ai_create_duplicate_job($user_id, $file_id, $config, $existing_job, $request_type = 'summary') {
    global $wpdb;
    
    $table_name = TABELLA_STUDIA_AI_JOBS;
    $current_time = current_time('mysql');
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'request_type' => $request_type,
            'request_date' => $current_time,
            'file_id' => $file_id,
            'file_path' => $existing_job['file_path'],
            'request_params' => json_encode($config),
            'points_cost' => isset($existing_job['points_cost']) ? intval($existing_job['points_cost']) : null,
            'status' => 'completed',
            'priority' => 0,
            'started_at' => $current_time,
            'completed_at' => $current_time,
            'result_file' => $existing_job['result_file'],
            'result_url' => $existing_job['result_url'],
            'error_message' => null,
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => $current_time,
            'updated_at' => $current_time
        ),
        array('%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Errore nella creazione del job duplicato');
    }
    
    return $wpdb->insert_id;
}

// Hook per creare la tabella all'attivazione del tema
add_action('after_switch_theme', 'studia_ai_create_jobs_table');

// Hook per la pulizia automatica dei job vecchi (una volta al giorno)
add_action('wp_scheduled_delete', 'studia_ai_cleanup_old_jobs');
