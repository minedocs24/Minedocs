<?php
$lingue = getLanguagesArray();
$currentLingua = $args['lingua'];
?>
<select name="lingua" id="lingua_select" class="form-select custom-select">
    <option value=""><?php _e('Seleziona una lingua', 'textdomain'); ?></option>
    <?php foreach ($lingue as $codice => $nome) : ?>
    <option value="<?php echo esc_attr($codice); ?>" <?php selected($currentLingua, $codice); ?>>
        <?php echo esc_html($nome); ?>
    </option>
    <?php endforeach; ?>
</select>