<?php
// Aggiungi il campo "saldo_utente" alla sezione del profilo utente
function add_saldo_utente_field($user) {
    // Recupera il saldo utente, se non è impostato, imposta a zero
    $saldo_utente = get_user_meta($user->ID, 'saldo_utente', true);
    if ($saldo_utente === '') {
        $saldo_utente = 0;
    }
    // Formatta il saldo utente per mostrare due cifre decimali
    $saldo_utente = number_format((float)$saldo_utente, 2, '.', '');
    
    ?>
    <h3>Saldo Utente</h3>
    <table class="form-table">
        <tr>
            <th><label for="saldo_utente">Saldo Utente (€)</label></th>
            <td>
                <input type="number" name="saldo_utente" id="saldo_utente" 
                       value="<?php echo esc_attr($saldo_utente); ?>" 
                       class="regular-text" step="0.01" min="0" />
                <p class="description">Inserisci il saldo attuale dell'utente.</p>
            </td>
        </tr>
    </table>
    <?php
}
//add_action('show_user_profile', 'add_saldo_utente_field');
//add_action('edit_user_profile', 'add_saldo_utente_field');

function save_saldo_utente_field($user_id) {
    // Verifica che l'utente abbia i permessi necessari
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Sanitizza e salva il saldo
    if (isset($_POST['saldo_utente'])) {
        update_user_meta($user_id, 'saldo_utente', sanitize_text_field($_POST['saldo_utente']));
    }
}
//add_action('personal_options_update', 'save_saldo_utente_field');
//add_action('edit_user_profile_update', 'save_saldo_utente_field');

function show_wallet_section($user) {
    global $wpdb;


    $total_prelevabile = ottieni_totale_prelevabile($user->ID);
    $total_prossimamente_prelevabile = ottieni_totale_prossimamente_prelevabile($user->ID);

    $total_prelevabile = number_format((float)$total_prelevabile, 2, '.', '');
    $total_prossimamente_prelevabile = number_format((float)$total_prossimamente_prelevabile, 2, '.', '');

    error_log('Total prelevabile: ' . print_r($total_prelevabile, true));
    error_log('Total prossimamente prelevabile: ' . print_r($total_prossimamente_prelevabile, true));

    ?>
    <h3>Wallet Utente</h3>
    <table class="form-table">
        <tr>
            <th><label for="total_prelevabile">Totale Prelevabile (€)</label></th>
            <td>
                <input type="text" name="total_prelevabile" id="total_prelevabile" 
                       value="<?php echo esc_attr($total_prelevabile); ?>" 
                       class="regular-text" readonly />
            </td>
        </tr>
        <tr>
            <th><label for="total_prossimamente_prelevabile">Totale Prossimamente Prelevabile (€)</label></th>
            <td>
                <input type="text" name="total_prossimamente_prelevabile" id="total_prossimamente_prelevabile" 
                       value="<?php echo esc_attr($total_prossimamente_prelevabile); ?>" 
                       class="regular-text" readonly />
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'show_wallet_section');
add_action('edit_user_profile', 'show_wallet_section');


