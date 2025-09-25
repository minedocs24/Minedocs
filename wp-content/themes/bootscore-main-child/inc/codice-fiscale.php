<?php
function add_codice_fiscale_field($user) {
    $codice_fiscale = get_user_meta($user->ID, 'codice_fiscale', true);
    ?>
    <h3>Codice Fiscale</h3>
    <table class="form-table">
        <tr>
            <th><label for="codice_fiscale">Codice Fiscale</label></th>
            <td>
                <input type="text" name="codice_fiscale" id="codice_fiscale" 
                       value="<?php echo esc_attr($codice_fiscale); ?>" 
                       class="regular-text" />
                <p class="description">Inserisci il codice fiscale dell'utente.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'add_codice_fiscale_field');
add_action('edit_user_profile', 'add_codice_fiscale_field');

function save_codice_fiscale_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    if (isset($_POST['codice_fiscale'])) {
        update_user_meta($user_id, 'codice_fiscale', sanitize_text_field($_POST['codice_fiscale']));
    }
}
add_action('personal_options_update', 'save_codice_fiscale_field');
add_action('edit_user_profile_update', 'save_codice_fiscale_field');