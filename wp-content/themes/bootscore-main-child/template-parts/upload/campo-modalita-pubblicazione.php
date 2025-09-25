<?php global $post_id; 
$prezzi_punti = get_prezzi_punti_pro(); // Funzione per recuperare i prezzi in punti pro
?>
<div class="form-group">
    <label class="bold-text">Modalità di pubblicazione:</label>
    <div class="toggle-switch align-items-center">
        <?php

        // Imposta il valore corrente del campo (Modalità di pubblicazione) basandoti sul post_id
        if ($post_id) {
            $terms = wp_get_post_terms($post_id, 'modalita_pubblicazione', array('fields' => 'all'));
            $current_modalita = !empty($terms) ? $terms[0]->slug : '';
        } else {
            $current_modalita = 'vendi';
        }
        ?>
        <input type="radio" id="Vendi" name="modalita" value="vendi" <?php echo ($current_modalita === 'vendi') ? 'checked' : ''; ?>>
        <label for="Vendi" class="toggle-option">
            <i class="icon-sell"></i> Vendi
        </label>

        <input type="radio" id="Condividi" name="modalita" value="condividi" <?php echo ($current_modalita === 'condividi') ? 'checked' : ''; ?>>
        <label for="Condividi" class="toggle-option">
            <i class="icon-share"></i> Condividi
        </label>

        <div class="toggle-bg"></div>
    </div>
    <i class="fas fa-info-circle fa-lg" tooltip="Se scegli di <b>vendere</b> il tuo documento, potrai scegliere il prezzo in punti pro e guadagnare da ciascuna vendita. <br> Se invece scegli di <b>condividere</b> il tuo documento, sarà acquistabile per 20 punti blu."></i>

</div>

<div class="form-group" id="campo_prezzo_guadagno" style="<?php echo ($current_modalita === 'condividi') ? 'display:none;' : 'display:block;'; ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="prezzo" class="bold-text">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/search/Icone_Minedocs_punti_rossi.svg"
                    class="icon-punti-pro" style="width: 25px; height: 25px; margin-right: 8px; margin-top: -2px;">
                Scegli il prezzo:
            </label>
            <?php
            // Imposta il valore corrente del campo (prezzo) basandoti sul post_id
            if ($post_id && $current_modalita === 'vendi') {
                $current_prezzo = get_post_meta($post_id, '_costo_in_punti_pro', true);
            } else {
                $current_prezzo = '';
            }
            ?>
            <select id="prezzo" class="form-select custom-input" <?php echo ($current_modalita === 'condividi') ? '' : 'required'; ?>>
                <option value="" disabled <?php echo ($current_prezzo === '') ? 'selected' : ''; ?>>Seleziona il prezzo in Punti Pro...</option>
                <?php foreach ($prezzi_punti as $prezzo): ?>
                    <option value="<?php echo $prezzo; ?>" <?php echo ($current_prezzo == $prezzo) ? 'selected' : ''; ?>>
                        <?php echo $prezzo; ?> - Punti Pro
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="guadagno" class="bold-text">
                    Guadagno stimato per ciascuna vendita:
                </label>
                <input type="text" id="guadagno" class="form-control custom-input" readonly>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="modalita"]').forEach((input) => {
    input.addEventListener('change', function() {
        if (this.id === 'Vendi') {
            document.getElementById('campo_prezzo_guadagno').style.display = 'flex';
            document.getElementById('prezzo').required = true;
        } else if (this.id === 'Condividi') {
            document.getElementById('campo_prezzo_guadagno').style.display = 'none';
            document.getElementById('prezzo').required = false;
            document.getElementById('prezzo').value = '';
            document.getElementById('guadagno').value = '';
        }
        check_fields(); // Call the function to check fields
    });
});

// Inizializza il tooltip
jQuery(document).ready(function() {
    jQuery('[data-toggle="tooltip"]').tooltip({ html: true }); // Abilita il tooltip con HTML
});
</script>
