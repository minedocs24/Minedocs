<div class="modal-content">
    <h2>Modifica Dati di Fatturazione</h2>
    <div id="billingDataForm">
        <div class="mb-3">
            <label for="billing_first_name" class="form-label">Nome</label>
            <input type="text" class="form-control" id="billing_first_name" name="billing_first_name"
                value="<?php echo esc_attr($args['first_name']); ?>" placeholder="Inserisci il tuo nome">
            <label id="billing_first_name_error" class="form-label text-danger small" hidden>Campo obbligatorio</label>
        </div>
        <div class="mb-3">
            <label for="billing_last_name" class="form-label">Cognome</label>
            <input type="text" class="form-control" id="billing_last_name" name="billing_last_name"
                value="<?php echo esc_attr($args['last_name']); ?>" placeholder="Inserisci il tuo cognome">
            <label id="billing_last_name_error" class="form-label text-danger small" hidden>Campo obbligatorio</label>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="billing_address" class="form-label">Indirizzo</label>
                <input type="text" class="form-control" id="billing_address" name="billing_address"
                    value="<?php echo esc_attr($args['billing_address_1']); ?>" placeholder="Inserisci il tuo indirizzo">
                <label id="billing_address_error" class="form-label text-danger small" hidden>Campo obbligatorio</label>
            </div>
            <div class="col-md-3">
                <label for="billing_address_num" class="form-label">Numero civico</label>
                <input type="text" class="form-control" id="billing_address_num" name="billing_address_num"
                    value="<?php echo esc_attr($args['billing_address_num']); ?>" placeholder="Inserisci il tuo numero civico">
                <label id="billing_address_num_error" class="form-label text-danger small" hidden>Campo
                    obbligatorio</label>
            </div>
            <div class="col-md-3">
                <label for="billing_postcode" class="form-label">CAP</label>
                <input type="text" class="form-control" id="billing_postcode" name="billing_postcode"
                    value="<?php echo esc_attr($args['billing_postcode']); ?>" placeholder="Inserisci il tuo CAP">
                <label id="billing_postcode_error" class="form-label text-danger small" hidden>Campo
                    obbligatorio</label>
            </div>
        </div>
        <div class="mb-3 justify-content-center">
            <div class="row">
                <div class="col-md-6">
                    <label for="billing_city" class="form-label">Città</label>
                    <input type="text" class="form-control" id="billing_city" name="billing_city"
                        value="<?php echo esc_attr($args['billing_city']); ?>" placeholder="Inserisci la tua città">
                    <label id="billing_city_error" class="form-label text-danger small" hidden>Campo
                        obbligatorio</label>

                </div>
                <div class="col-md-6">
                    <label for="billing_country" class="form-label">Nazione</label>
                    <!-- <input type="text" class="form-control" id="billing_country" name="billing_country" value="<?php /* echo esc_attr($billing_country); */ ?>" placeholder="Inserisci la tua nazione"> -->
                    <select class="form-control country_to_state country_select" id="billing_country"
                        name="billing_country" data-placeholder="Seleziona la tua nazione">
                        <option value=""><?php esc_html_e('Select a country / region&hellip;', 'woocommerce'); ?>
                        </option>
                        <?php
                                        foreach (WC()->countries->get_allowed_countries() as $key => $value) {
                                            echo '<option value="' . esc_attr($key) . '"' . selected($args['billing_country'], $key, false) . '>' . esc_html($value) . '</option>';
                                        }
                                        ?>
                    </select>
                    <label id="billing_country_error" class="form-label text-danger small" hidden>Campo
                        obbligatorio</label>
                </div>
            </div>
        </div>
            <div class="mb-3">
                <label for="codice_fiscale" class="form-label">Codice Fiscale o P. IVA</label>
                <input type="text" class="form-control" id="codice_fiscale" name="codice_fiscale"
                    value="<?php echo esc_attr($args['codice_fiscale']); ?>" placeholder="Inserisci il tuo codice fiscale">
                <label id="codice_fiscale_error" class="form-label text-danger small" hidden>Campo obbligatorio</label>
            </div>
        </div>
        <div class="mb-3">
            <label for="billing_phone" class="form-label">Telefono</label>
            <input type="text" class="form-control" id="billing_phone" name="billing_phone"
                value="<?php echo esc_attr($args['billing_phone']); ?>" placeholder="Inserisci il tuo numero di telefono">
            <label id="billing_phone_error" class="form-label text-danger small" hidden>Campo obbligatorio</label>
        </div>
        
        <div class="d-flex justify-content-between">
            <button id="save-billing-info" class="btn btn-primary d-flex align-items-center justify-content-center"
                onclick="saveBillingInfo('<?php echo admin_url('admin-ajax.php'); ?>')">
                <div id="icon-loading-edit-billing-data" class="btn-loader mx-2" hidden>
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                <span>Salva</span>
            </button>
            <button id="cancel-billing-info" class="btn btn-secondary d-flex align-items-center justify-content-center"
                onclick="cancelBillingInfo()">
                <span>Annulla</span>
            </button>
        </div>
        
    </div>
</div>