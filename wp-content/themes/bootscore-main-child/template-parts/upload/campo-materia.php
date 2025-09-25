<?php global $post_id; ?>
<div class="form-group">
    <label for="materia" class="bold-text">
        <i class="icon-subject"></i> Materia
    </label>
    <?php
    // Recupera il parametro `post_id` dall'URL
    //$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;

    // Imposta il valore corrente del campo (nome_corso) basandoti sul post_id
    if ($post_id) {
        $terms = wp_get_post_terms($post_id, 'nome_corso', array('fields' => 'slugs'));
        $current_nome_corso = !empty($terms) ? $terms[0] : '';
    } else {
        $current_nome_corso = '';
    }

    // Recupera tutti i termini della tassonomia 'nome_corso'
    $terms = get_terms(array(
        'taxonomy' => 'nome_corso',
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key' => 'status',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'compare' => '!=',
                'value' => 'draft',
                'key' => 'status',
            ),
            'relation' => 'OR',
        ),
        'orderby' => 'name',
        'order' => 'ASC'
    ));

    if (!empty($terms) && !is_wp_error($terms)) : ?>
    <select id="nome_corso_select" class="form-select" onchange="check_fields()">
        <option value="">Seleziona un corso</option>
        <?php foreach ($terms as $term) : ?>
        <option value="<?php echo esc_attr($term->slug); ?>" <?php echo ($current_nome_corso === $term->slug) ? 'selected' : ''; ?>>
            <?php echo esc_html($term->name); ?>
        </option>
        <?php endforeach; ?>
    </select>
    <div id="invalid-corso-msg" class="alert alert-danger mt-2 d-none">
        Il campo materia non può contenere caratteri speciali.
    </div>
    <?php endif; ?>

    <script>
    jQuery(document).ready(function($) {
        $('#nome_corso_select').select2({
            placeholder: 'Cerca o seleziona un corso',
            allowClear: true,
            tags: true
        });
        $('#nome_corso_select').on('change', function(e) {
        var value = $(this).val();
        if (!$(this).find('option[value="' + value + '"]').length) {
            // Aggiungi il nuovo valore come opzione
            var newOption = new Option(value, value, true, true);
            $(this).append(newOption).trigger('change');
        }
        });

        $('.select2-container--default .select2-selection--single').addClass('form-select');
    });

    
    </script>
</div>
