<?php global $post_id; ?>

<div class="form-group">
    <label for="anno" class="bold-text">
        <i class="icon-calendar"></i> Anno Accademico
    </label>
    <?php
    // Recupera il parametro `post_id` dall'URL
    //$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;

    // Imposta il valore corrente del campo (anno accademico) basandoti sul post_id
    if ($post_id) {
        $terms = wp_get_post_terms($post_id, 'anno_accademico', array('fields' => 'names'));
        $current_anno = !empty($terms) ? $terms[0] : '';
    } else {
        $current_anno = '';
    }

    // Recupera tutti i termini della tassonomia 'anno_accademico'
    $terms = get_terms(array(
        'taxonomy' => 'anno_accademico',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'DESC',
    ));
    ?>
    <select id="anno" class="form-select" onchange="check_fields()">
        <option value="">-- Seleziona un anno --</option>
        <?php foreach ($terms as $term) : 
            if (intval(substr($term->name, 0, 4)) > date('Y')) {
                continue;
            }
            ?>
            <option value="<?php echo esc_attr($term->name); ?>"
                <?php echo ($current_anno === $term->name) ? 'selected' : ''; ?>>
                <?php echo esc_html($term->name); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
