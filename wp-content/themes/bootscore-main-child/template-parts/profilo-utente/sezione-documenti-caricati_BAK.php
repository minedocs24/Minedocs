<?php

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: devi essere loggato per visualizzare i tuoi file.';
    return;
}

// Ottieni l'ID dell'utente loggato
$user_id = get_current_user_id();

// Query per recuperare i post creati dall'utente loggato
$args = array(
    'post_type' => 'product', // Tipo di post
    'post_status' => array('publish', 'draft'), // Recupera i post pubblicati o in bozza
    'author' => $user_id, // Filtra per l'ID dell'utente loggato
    'posts_per_page' => -1, // Recupera tutti i post

    // Filtra i post per il tipo di prodotto "documento"
    'tax_query' => array(
        array(
            'taxonomy' => 'tipo_prodotto',
            'field' => 'slug',
            'terms' => 'documento'
        )
        ),
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => 'stato_prodotto',
            'value' => 'pubblicato',
            'compare' => '='
        ),
        array(
            'key' => 'stato_prodotto',
            'compare' => 'NOT EXISTS'
        )
    )
);

// Recupera i post
$query = new WP_Query($args);

$files = array();
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();

        $post_id = get_the_ID(); // Ottieni l'ID del post
        $anni_accademici = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'anno_accademico', array('fields' => 'all')));
        $tipi_istituti = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'tipo_istituto', array('fields' => 'all')));
        $nomi_istituti = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'nome_istituto', array('fields' => 'all')));
        $nomi_corsi = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'nome_corso', array('fields' => 'all')));
        $nomi_corsi_laurea = array_map(function($term) {return $term->name;}, wp_get_post_terms($post_id, 'nome_corso_di_laurea', array('fields' => 'all')));
        


        // Recupera i metadati personalizzati associati al post
        $files[] = array(
            'id' => $post_id, // ID del post
            'hid' => get_product_hash_id($post_id), // ID hash del prodotto
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
            'stato_approvazione' => get_post_meta($post_id, META_KEY_STATO_APPROVAZIONE_PRODOTTO, true), // Stato di approvazione,
            'stato_prodotto' => get_stato_prodotto($post_id), // Stato del prodotto
            'status' => get_post_status($post_id) // Stato del post
        );
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
                <a href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>" class="upload-btn-table">Carica file</a>
            </div>
            <div class="card-body">
                <div class="menu-card">
                    <a href="#" class="menu-item active" id="filter-all">Tutti i file</a>
                    <a href="#" class="menu-item" id="filter-pro">File a pagamento</a>
                    <a href="#" class="menu-item" id="filter-blu">File gratuiti</a>
                </div>
                <div class="search-box">
                    <input type="text" id="search-input" placeholder="Cerca un file">
                </div>

                <div class="table-responsive">
                    <table class="table table-profilo align-middle" id="file-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Azioni</th>
                                
                                <th>Nome</th>                                
                                <th>Descrizione</th>
                                
                                <th>Università</th>
                                <th>Corso di laurea</th>
                                <th>Materia</th>
                                <th>Anno accademico</th>
                                <th>Stato</th>
                                <th>Punti Pro</th>
                                <th>Punti Blu</th>
                                <th>Data caricamento</th>
                            </tr>
                        </thead>
                        <tbody id="file-table-body">
                            <?php foreach ($files as $file): ?>
                            <tr data-post-id="<?php echo esc_attr($file['hid']); ?>" data-costo-pro="<?php echo esc_attr($file['costo_in_punti_pro']); ?>">
                                <td class="text-center">
                                    <a href="#" 
                                       class="btn-actions btn-edit" 
                                       data-id="<?php echo esc_attr($file['hid']); ?>" 
                                       data-status="<?php echo esc_attr($file['stato_approvazione']); ?>" 
                                       title="Modifica">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/matita.png" alt="Modifica" width="16" height="16" />
                                    </a>
                                    <?php // if ($file['status'] == 'publish' || current_user_can('edit_post', $file['id'])) { ?>
                                        <a href="<?php echo esc_url($file['document_link']); ?>" target="_blank" class="btn-actions btn-link-table" title="Visualizza">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/eye.png" alt="Apri" width="16" height="16" />
                                        </a>
                                    <?php // } ?>
                                    <button class="btn-actions delete-btn" data-id="<?php echo esc_attr($file['hid']); ?>" title="Elimina">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/trash.png" alt="Elimina" width="16" height="16" />
                                    </button>
                                </td>

                                <td class="data-cell" title="<?php echo esc_attr($file['name']); ?>">
                                    <?php echo wp_trim_words(esc_html($file['name']), 10, '...'); ?>
                                </td>
                                <td class="data-cell" title="<?php echo esc_attr($file['description']); ?>">
                                    <?php echo wp_trim_words(esc_html($file['description']), 10, '...'); ?>
                                </td>

                                <td><?php echo esc_html($file['nome_istituto']); ?></td>
                                <td><?php echo esc_html($file['nome_corso_di_laurea']); ?></td>
                                <td><?php echo esc_html($file['nome_corso']); ?></td>
                                <td><?php echo esc_html($file['anno_accademico']); ?></td>
                                <td class="data-cell">
                                    <?php if($file['status']=='publish' && $file['stato_approvazione'] == 'approvato'){ ?>
                                        <span class="badge bg-success">Approvato e pubblicato</span>
                                    <?php } elseif($file['status']=='draft' && $file['stato_approvazione'] == 'in_approvazione'){ ?>
                                        <span class="badge bg-secondary">In revisione</span>
                                    <?php } elseif($file['status']=='draft' && $file['stato_approvazione'] == 'non_approvato'){ ?>
                                        <span class="badge bg-danger">Non approvato</span>
                                    <?php } elseif($file['status']=='draft' && $file['stato_prodotto'] == 'eliminato_utente'){ ?>
                                        <span class="badge bg-danger">Eliminato</span>
                                    <?php } elseif($file['status']=='draft' && $file['stato_prodotto'] == 'eliminato_admin'){ ?>
                                        <span class="badge bg-danger">Eliminato</span>
                                    <?php } elseif($file['status']=='draft' && $file['stato_prodotto'] == 'nascosto_aggiornamento'){ ?>
                                        <span class="badge bg-danger">Nascosto per aggiornamento</span>
                                    <?php } else { ?>
                                        <span class="badge bg-warning">Contatta un admin!</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo esc_html($file['costo_in_punti_pro']); ?></td>
                                <td><?php echo esc_html($file['costo_in_punti_blu']); ?></td>
                                <td><?php echo esc_html($file['upload_date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-controls">
                    <img id="prev-page" class="table-button-prev" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Precedente" width="16" height="16" />
                    <span id="page-info"></span>
                    <img id="next-page" class="table-button-next" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Precedente" width="16" height="16" />
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal per confermare l'eliminazione -->
<div class="modal fade" id="deleteFileModal" tabindex="-1" aria-labelledby="deleteFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFileModalLabel">Conferma Eliminazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Sei sicuro di voler eliminare questo file?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Elimina</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal per avviso modifica -->
<?php 
    get_template_part('modals/confirm_edit_document');
    get_template_part('modals/prohibit_edit_document');