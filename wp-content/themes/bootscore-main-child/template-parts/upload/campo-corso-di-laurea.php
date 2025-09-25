<?php
global $post_id;
$show_title = isset($args['show_title']) ? $args['show_title'] : true;

// Recupera il parametro `post_id` dall'URL
//$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;

// Imposta il valore corrente del campo (corso di laurea) basandoti sul post_id
if ($post_id) {
    $terms = wp_get_post_terms($post_id, 'nome_corso_di_laurea', array('fields' => 'slugs'));
    $current_corso = !empty($terms) ? $terms[0] : '';
} elseif (isset($args['corso-di-laurea'])) {
    $term = get_term($args['corso-di-laurea'][0], 'nome_corso_di_laurea');
    $current_corso = !is_wp_error($term) ? $term->slug : '';
} else {
    $current_corso = '';
}
?>

<div class="form-group">
    <?php if ($show_title) : ?>
        <label for="corso" class="bold-text">
            <i class="icon-course"></i> Corso di laurea
        </label>
    <?php endif; ?>

    <?php
    // Recupera tutti i termini della tassonomia 'nome_corso_di_laurea'
    $terms = get_terms(array(
        'taxonomy' => 'nome_corso_di_laurea',
        'hide_empty' => false, // Mostra anche i termini senza post associati
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

    if (!empty($terms) && !is_wp_error($terms)) :
    ?>
        <select id="nome_corso_di_laurea_select" class="form-select" <?php if (isset($args['onchange'])) echo 'onchange="' . $args['onchange'] . '"'; ?>>
            <option value="">Seleziona un corso</option>
            <?php foreach ($terms as $term) : ?>
                <option value="<?php echo esc_attr($term->slug); ?>"
                    <?php echo ($current_corso === $term->slug) ? 'selected' : ''; ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div id="invalid-corso-di-laurea-msg" class="alert alert-danger mt-2 d-none">
        Il campo corso di laurea non può contenere caratteri speciali.
        </div>
    <?php endif; ?>

    <script>
        jQuery(document).ready(function($) {
            $('#nome_corso_di_laurea_select').select2({
                placeholder: 'Cerca o seleziona un corso di Laurea',
                allowClear: true,
                tags: true
            });

            // Permetti di aggiungere nuove opzioni dinamicamente
            $('#nome_corso_di_laurea_select').on('change', function(e) {
                var value = $(this).val();
                if (!$(this).find('option[value="' + value + '"]').length) {
                    var newOption = new Option(value, value, true, true);
                    $(this).append(newOption).trigger('change');
                }
            });

            $('.select2-container--default .select2-selection--single').addClass('form-select');
        });
    </script>
</div>

