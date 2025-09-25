<?php global $post_id; ?>

<div class="form-group">
    <?php
    // Recupera il parametro `post_id` dall'URL
    //$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;

    // Imposta il valore corrente del campo (istituto) basandoti sul post_id
    if ($post_id) {
        $terms = wp_get_post_terms($post_id, 'nome_istituto', array('fields' => 'slugs'));
        $current_istituto = !empty($terms) ? $terms[0] : '';
    } elseif (isset($args['istituto'])) {
        // $current_istituto = $args['istituto'];
        $term = get_term($args['istituto'][0], 'nome_istituto');
        $current_istituto = !is_wp_error($term) ? $term->slug : '';
    } else {
        $current_istituto = '';
    }

    // Recupera tutti i termini della tassonomia 'nome_istituto'
    $terms = get_terms(array(
        'taxonomy' => 'nome_istituto',
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

    // Verifica che ci siano termini validi
    if (!empty($terms) && !is_wp_error($terms)) : ?>
        <select id="nome_istituto_select" class="form-select" <?php if (isset($args['onchange'])) echo 'onchange="' . $args['onchange'] . '"'; ?>>
            <?php if (empty($current_istituto)) : ?>
                <option value="">Seleziona la scuola/università</option>
            <?php endif; ?>
            <?php foreach ($terms as $term) : ?>
                <option value="<?php echo esc_attr($term->slug); ?>" 
                    <?php echo ($current_istituto === $term->slug) ? 'selected' : ''; ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div id="invalid-universita-msg" class="alert alert-danger mt-2 d-none">
            Il campo univerisità non può contenere caratteri speciali.
        </div>
    <?php endif; ?>

    <script>
    jQuery(document).ready(function($) {
        $('#nome_istituto_select').select2({
            placeholder: 'Cerca o seleziona una scuola o università',
            allowClear: true,
            tags: true
        });
        $('.select2-container--default .select2-selection--single').addClass('form-select');
    });
    </script>
</div>

