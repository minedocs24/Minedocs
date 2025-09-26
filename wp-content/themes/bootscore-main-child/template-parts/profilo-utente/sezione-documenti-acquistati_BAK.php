<?php

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: devi essere loggato per visualizzare i tuoi file.';
    return;
}

// Ottieni l'ID dell'utente loggato
$user_id = get_current_user_id();

$current_user = wp_get_current_user();
$customer_orders = wc_get_orders(array(
    'customer_id' => $current_user->ID,
    'status' => 'completed',
    'limit' => -1,
));
$product_ids = array();
$mapping_product_id_to_order_id = array();
foreach ($customer_orders as $order) {
    foreach ($order->get_items() as $item) {
        $product_ids[] = $item->get_product_id();
        $mapping_product_id_to_order_id[$item->get_product_id()] = $order->get_id();
    }
}

$documenti = array();
if (!empty($product_ids)) {
// Query per recuperare i post di tipo "product" con ID presenti nell'array $product_ids
$args = array(
    'post_type' => 'product', // Tipo di post
    'post_status' => array('publish'), // Recupera i post pubblicati o in bozza
    'post__in' => $product_ids, // Recupera solo i post con ID presenti nell'array $product_ids
    'posts_per_page' => -1, // Recupera tutti i post

    // Filtra i post per il tipo di prodotto "documento"
    'tax_query' => array(
        array(
            'taxonomy' => 'tipo_prodotto',
            'field' => 'slug',
            'terms' => 'documento',
            'include_children' => true,
        )
    )
);

}

// Recupera i post
$query = new WP_Query($args);

$files = array();
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();

        $post_id = get_the_ID(); // Ottieni l'ID del post
        $order_id = $mapping_product_id_to_order_id[$post_id];
        $anni_accademici = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'anno_accademico', array('fields' => 'all')));
        $tipi_istituti = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'tipo_istituto', array('fields' => 'all')));
        $nomi_istituti = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'nome_istituto', array('fields' => 'all')));
        $nomi_corsi = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'nome_corso', array('fields' => 'all')));
        $nomi_corsi_laurea = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'nome_corso_di_laurea', array('fields' => 'all')));
        
        $recensione_utente = get_user_review_for_product($post_id, $user_id);
        // Recupera i metadati personalizzati associati al post
        $files[] = array(
            'id' => $post_id, // ID del post
            'name' => get_the_title(), // Titolo del post
            'upload_date' => get_the_date(), // Data di pubblicazione
            'description' => get_the_excerpt(), // Estratto del post
            'anno_accademico' => implode(', ', $anni_accademici), // Anno accademico
            'tipo_istituto' => implode(', ', $tipi_istituti), // Tipo di istituto
            'nome_istituto' => implode(', ', $nomi_istituti), // Nome dell'istituto
            'nome_corso' => implode(', ', $nomi_corsi), // Nome del corso
            'nome_corso_di_laurea' => implode(', ', $nomi_corsi_laurea), // Nome del corso di laurea
            'costo_in_punti_pro' => get_post_meta($post_id, '_costo_in_punti_pro', true), // Prezzo
            'costo_in_punti_blu' => get_post_meta($post_id, '_costo_in_punti_blu', true), // Prezzo
            'document_link' => get_permalink($post_id), // Link al documento
            'stato_approvazione' => get_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true), // Stato di approvazione
            'recensione' => get_post_meta($post_id, '_wc_average_rating', true),
            'order_id' => $order_id,
            'author_id' => $post->post_author,
            'order_hid' => get_order_hash($order_id)
            
        );

        if($recensione_utente){
            $files[count($files) - 1]['recensione_utente_testo'] = $recensione_utente['review_text'];
            $files[count($files) - 1]['recensione_utente_voto'] = $recensione_utente['rating'];
        } else {
            $files[count($files) - 1]['recensione_utente_testo'] = null;
            $files[count($files) - 1]['recensione_utente_voto'] = null;
        }
    }
}

// Ripristina i dati globali del post
wp_reset_postdata();
?>

<div id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class="container my-5">
        <div class="table-card">
            <div class="table-card-header d-flex justify-content-between align-items-center">
                <h4 class="table-card-header-text">I tuoi file</h4>
                <a href="<?php echo RICERCA_PAGE ?>" class="upload-btn-table">Cerca un file</a>
            </div>
            <div class="card-body">
                <div class="menu-card">
                    <a href="#" class="menu-item active" id="filter-all">Tutti i file</a>
                    <a href="#" class="menu-item" id="filter-pro">File a pagamento</a>
                    <a href="#" class="menu-item" id="filter-blu">File gratuiti</a>
                </div>
                <div class="row">

                    <div class="search-box col-12 col-md-6">
                        <input type="text" id="search-input" placeholder="Cerca un file">
                    </div>
                    <div class="filter-dropdown col-12 col-md-6 search-box ">
                        <select id="filter-dropdown">
                            <option value="tutti">Tutti</option>
                            <?php
                        $grouped_files = array();
                        foreach ($files as $file) {
                            $key = $file['nome_istituto'] . ' - ' . $file['nome_corso_di_laurea'];
                            if (!isset($grouped_files[$key])) {
                                $grouped_files[$key] = array();
                            }
                            $grouped_files[$key][] = $file;
                        }

                        foreach ($grouped_files as $key => $group) {
                            echo '<option value="' . esc_attr($key) . '">' . esc_html($key) . '</option>';
                        }
                        ?>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <div id="grouped-tables">
                        <table class="table table-profilo align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Azioni</th>
                                    <th>Nome</th>
                                    <th>Descrizione</th>
                                    <th>Materia</th>
                                    <th>Anno accademico</th>
                                    <th>Recensione</th>
                                    <th>Data caricamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($grouped_files as $key => $group): ?>
                                <tr class="group-header" data-group="<?php echo esc_attr($key); ?>">
                                    <td colspan="7"><?php echo esc_html($key); ?></td>
                                </tr>
                                    <?php 
                                    foreach ($group as $file): ?>
                                    <tr data-key-gruppo="<?php echo esc_attr($key)?>"
                                        data-post-id="<?php echo esc_attr($file['id']); ?>"
                                        data-costo-pro="<?php echo esc_attr($file['costo_in_punti_pro']); ?>">
                                        <td class="text-center">
                                            <a href="<?php echo esc_url($file['document_link']); ?>" target="_blank"
                                                class="btn-actions btn-link-table" title="Visualizza">
                                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/eye.png"
                                                    alt="Apri" width="16" height="16" />
                                            </a>

                                            <?php if($file['costo_in_punti_pro']>0) mostra_pulsante_richiesta_fattura($file['order_hid'], $file['author_id']); ?>
                                        </td>
                                        <td class="data-cell" title="<?php echo esc_attr($file['name']); ?>">
                                            <?php echo wp_trim_words(esc_html($file['name']), 10, '...'); ?>
                                        </td>
                                        <td class="data-cell" title="<?php echo esc_attr($file['description']); ?>">
                                            <?php echo wp_trim_words(esc_html($file['description']), 10, '...'); ?>
                                        </td>
                                        <td><?php echo esc_html($file['nome_corso']); ?></td>
                                        <td><?php echo esc_html($file['anno_accademico']); ?></td>
                                        <td>
                                            <div id="rating-cell-post-<?php echo esc_attr($file['id']); ?>" class="rating">
                                                <?php if ($file['recensione_utente_voto']){ ?>
                                                <div class="rating-stars">
                                                    <?php echo '⭐ ' . $file['recensione_utente_voto']; ?>
                                                </div>
                                                <?php } else { ?>
                                                <button class="btn-actions btn-mini-scrivi-recensione" data-post-id="<?php echo esc_attr($file['id']); ?>" title="Recensisci" data-title="<?php echo esc_attr($file['name']); ?>">
                                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/review-svgrepo-com.svg"
                                                        alt="Apri" width="16" height="16" />
                                                </button>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td><?php echo esc_html($file['upload_date']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pagination-controls">
                    <img id="prev-page" class="table-button-prev"
                        src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png"
                        alt="Precedente" width="16" height="16" />
                    <span id="page-info"></span>
                    <img id="next-page" class="table-button-next"
                        src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png"
                        alt="Precedente" width="16" height="16" />
                </div>
            </div>
        </div>
    </div>
</div>


<style>
.group-header {

    font-weight: bold;
}

.group-header+tr {
    border-top: 2px solid #dee2e6;

}

.group-header td {
    padding: 0rem;
    background-color: #F7F7F7 !important;
    line-height: 2.5 !important;
}

.group-header td[colspan] {
    text-align: center;
}

.group-header td[colspan]::before {
    content: attr(data-group);
}

/* Contenitore del sistema di rating */
.rating-box {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    align-items: center;
    font-size: 2rem; /* Dimensione delle stelle */
    gap: 0.5rem; /* Spazio tra le stelle */
}

/* Nascondi gli input radio */
.rating-box input[type="radio"] {
    display: none;
}

/* Stile base delle stelle */
.rating-box label {
    color: #ccc; /* Colore stelle inattive */
    cursor: pointer;
    transition: color 0.3s;
}

/* Cambia il colore delle stelle al passaggio del mouse */
.rating-box label:hover,
.rating-box label:hover ~ label {
    color: #ffcc00; /* Colore stelle al passaggio del mouse */
}

/* Cambia il colore delle stelle selezionate */
.rating-box input[type="radio"]:checked ~ label {
    color: #ffcc00; /* Colore stelle selezionate */
}


</style>