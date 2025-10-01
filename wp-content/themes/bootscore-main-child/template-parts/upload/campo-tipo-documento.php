<?php
global $post_id; 
// Recupera il parametro `post_id` dall'URL
//$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;

// Imposta il valore corrente del campo (tipo di documento) basandoti sul post_id
if ($post_id) {
    $terms = wp_get_post_terms($post_id, 'tipo_prodotto', array('fields' => 'all'));
    $tipo_prodotto = array_map(function($term) {
        return $term->name; // Usa lo slug invece del nome
    }, $terms);
    $current_tipo_documento = !empty($tipo_prodotto) ? $tipo_prodotto[1] : '';
} else {
    $current_tipo_documento = '';
}

// Recupera tutti i termini della tassonomia 'tipo_prodotto' che siano figli del termine 'documento'
$parent_term = get_term_by('slug', 'documento', 'tipo_prodotto');
$terms = get_terms(array(
    'taxonomy' => 'tipo_prodotto',
    'hide_empty' => false,
    'parent' => $parent_term ? $parent_term->term_id : 0, // ID del termine 'documento'
    'orderby' => 'name',
    'order' => 'ASC'
));

if (!empty($terms) && !is_wp_error($terms)) : ?>
    <div class="form-group">
        <label for="documento" class="bold-text">
            <i class="icon-document"></i> Tipo di documento
        </label>
        <select id="documento" class="form-select" name="documento" onchange="check_fields()">
            <option value="">-- Seleziona un tipo di documento --</option>
            <?php foreach ($terms as $term) : ?>
                <option value="<?php echo esc_attr($term->slug); ?>"
                    <?php echo ($current_tipo_documento === $term->name) ? 'selected' : ''; ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif;