<?php
$nazioni = getNationArray(); 
$currentNazione = $args['nazione'];
?>
<select name="nazione" id="nazione_select" class="form-select custom-select">
    <?php foreach ($nazioni as $codice => $nome) : ?>
        <option value="<?php echo esc_attr($codice); ?>" <?php selected($currentNazione, $codice); ?>>
            <?php echo esc_html($nome); ?>
        </option>
    <?php endforeach; ?>
</select>