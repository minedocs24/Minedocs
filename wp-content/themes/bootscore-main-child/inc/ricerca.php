<?php
// Add this code to your functions.php file or include it in your ricerca.php file

add_action('wp_ajax_nopriv_search_documents', 'search_documents');
add_action('wp_ajax_search_documents', 'search_documents');

function search_documents() {
    // Check for nonce security
//    check_ajax_referer('search_products_nonce', 'nonce');

    $keyword = sanitize_text_field($_POST['search']);
    $institute = sanitize_text_field($_POST['institute']);
    $subject = sanitize_text_field($_POST['subject']);
    $type = sanitize_text_field($_POST['type']);
    $institute_type = sanitize_text_field($_POST['institute_type']);
    $study_course = sanitize_text_field($_POST['study_course']);
    $orderby = sanitize_text_field($_POST['orderby']);
    $academic_year = sanitize_text_field($_POST['academic_year']);
    $hide_purchased = sanitize_text_field($_POST['hide_purchased']);
    $max = isset($_POST['max']) ? intval($_POST['max']) : -1;

    if ($keyword !== '*' && !valida_stringa($keyword, 0, 100, '/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ-]+(?<!-|\s)$/')) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    //if (!valida_stringa($institute, 0, 100, '/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ-]+(?<!-|\s)$/')) {
    if (isset($institute) && !empty($institute) && !is_numeric($institute)) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }
    
    if (isset($subject) && !empty($subject) && !is_numeric($subject)) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    
    if (isset($type) && !empty($type) && !valida_stringa($type, 0, 100, '/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ-]+(?<!-|\s)$/')) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    if (isset($institute_type) && !empty($institute_type) && !is_numeric($institute_type)) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    if (isset($study_course) && !empty($study_course) && !is_numeric($study_course)) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    if (isset($academic_year) && !empty($academic_year) && !is_numeric($academic_year)) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    if (isset($orderby) && $orderby != '' && !in_array($orderby, ['date_desc', 'date_asc', 'title_asc', 'title_desc', 'reviews_desc', 'reviews_asc', 'downloads_desc', 'downloads_asc'])) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    if (isset($hide_purchased) && $hide_purchased != '' && !in_array($hide_purchased, ['true', 'false', true, false])) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }

    if (isset($max) && $max != -1 && !is_numeric($max)) {
        wp_send_json_error('Parametri di ricerca non validi.'); 
        wp_die();
    }


    

    error_log("Hide purchased: " . $hide_purchased);

    $current_user_id = get_current_user_id();

    $args = array();
    $args['post_type'] = 'product';
    $args['post_status'] = 'publish';
    $args['posts_per_page'] = $max;

    
    $args['meta_query'] = array(
        
        array(
            'key' => META_KEY_STATO_APPROVAZIONE_PRODOTTO,
            'value' => 'approvato',
            'compare' => '='
        ),
    );

    /*
    if (strlen($keyword) < 3 && $keyword !== '*') {
        wp_send_json_error('Inserisci almeno 3 caratteri.');
        wp_die();
    }*/

    if ($type == 'documento') {
        $type = get_term_by('slug', 'documento', 'tipo_prodotto')->term_id;
    }

    $tax_query = array('relation' => 'AND');

    if (!empty($institute)) {
        $tax_query[] = array(
            'taxonomy' => 'nome_istituto',
            'field'    => 'term_id',
            'terms'    => $institute,
        );
    }

    if (!empty($subject)) {
        $tax_query[] = array(
            'taxonomy' => 'nome_corso',
            'field'    => 'term_id',
            'terms'    => $subject,
        );
    }

    if (!empty($type)) {
        $tax_query[] = array(
            'taxonomy' => 'tipo_prodotto',
            'field'    => 'term_id',
            'terms'    => $type,
        );
    }

    if (!empty($study_course)) {
        $tax_query[] = array(
            'taxonomy' => 'nome_corso_di_laurea',
            'field'    => 'term_id',
            'terms'    => $study_course,
        );
    }

    if (!empty($academic_year)) {
        $tax_query[] = array(
            'taxonomy' => 'anno_accademico',
            'field'    => 'term_id',
            'terms'    => $academic_year,
        );
    }


    if ($keyword !== '*' && !empty($keyword)) {
        $args['s'] = $keyword;

        $termIds = get_terms([
            'name__like' => $keyword,
            'fields' => 'ids',
            'taxonomy' => ['nome_istituto', 'nome_corso', 'tipo_prodotto', 'nome_corso_di_laurea', 'anno_accademico'],
            'hide_empty' => true,
        ]);
    
        // Se sono stati trovati termini, aggiungili alla tax_query
        if (!empty($termIds)) {
            $tax_query[] = [
                'relation' => 'OR',
                [
                    'taxonomy' => 'nome_istituto',
                    'field'    => 'id',
                    'terms'    => $termIds,
                ],
                [
                    'taxonomy' => 'nome_corso',
                    'field'    => 'id',
                    'terms'    => $termIds,
                ],
                [
                    'taxonomy' => 'tipo_prodotto',
                    'field'    => 'id',
                    'terms'    => $termIds,
                ],
                [
                    'taxonomy' => 'nome_corso_di_laurea',
                    'field'    => 'id',
                    'terms'    => $termIds,
                ],
                [
                    'taxonomy' => 'anno_accademico',
                    'field'    => 'id',
                    'terms'    => $termIds,
                ],
            ];
        }
    } else {
        $args['posts_per_page'] = 25;
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }
    $args['posts_per_page'] = 25;

    $args['tax_query'] = $tax_query;

    error_log(print_r($args, true));

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $documents = array();
        while ($query->have_posts()) {
            $query->the_post();

            ob_start();
            get_template_part('template-parts/search/content', 'search');
            $html = ob_get_clean();

            ob_start();
            get_template_part('template-parts/search/mini-content', 'search');
            $html_mini = ob_get_clean();

            $info_recensioni = get_product_review_info(get_the_ID());
            $media_recensioni = $info_recensioni['average_rating'];
            $n_recensioni = $info_recensioni['total_reviews'];

            $documents[] = array(
                'post_id' => get_the_ID(),
                'title' => get_the_title(),
                'description' => get_the_excerpt(),
                'link' => get_permalink(),
                'num_downloads' => get_product_purchase_count(get_the_ID()),
                'date' => get_the_date('Y-m-d'),
                'reviews' => wc_get_rating_html(get_the_ID()),
                'media_recensioni' => $media_recensioni,
                'n_recensioni' => $n_recensioni,
                'html' => $html,
                'html_mini' => $html_mini,
                'image' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                'anno_accademico' => get_the_terms(get_the_ID(), 'anno_accademico')[0]->name,
                'tipo_prodotto' => get_the_terms(get_the_ID(), 'tipo_prodotto')[0]->name,
                'nome_corso_di_laurea' => get_the_terms(get_the_ID(), 'nome_corso_di_laurea')[0]->name,
                'nome_corso' => get_the_terms(get_the_ID(), 'nome_corso')[0]->name,
                'nome_istituto' => get_the_terms(get_the_ID(), 'nome_istituto')[0]->name,
                'tipo_istituto' => get_the_terms(get_the_ID(), 'tipo_istituto')[0]->name
            );
        }

        if ($hide_purchased == 'true') {
            error_log("Hiding purchased documents");
            $removed_documents = array();
            $documents = array_filter($documents, function($p) use (&$removed_documents) {
                error_log("Checking if user has purchased product " . $p['title']);
                error_log(print_r($p, true));
                if (user_has_purchased_product(get_current_user_id(), $p['post_id'])) {
                    $removed_documents[] = $p['title'];
                    return false;
                }
                return true;
            });
            error_log("Removed documents: " . implode(', ', $removed_documents));
        }

        if (!empty($orderby)) {
            usort($documents, function($a, $b) use ($orderby) {
                switch ($orderby) {
                    case 'date_desc':
                        return strtotime($b['date']) - strtotime($a['date']);
                    case 'date_asc':
                        return strtotime($a['date']) - strtotime($b['date']);
                    case 'title_asc':
                        return strcmp($a['title'], $b['title']);
                    case 'title_desc':
                        return strcmp($b['title'], $a['title']);
                    case 'reviews_desc':
                        return $b['media_recensioni'] - $a['media_recensioni'];
                    case 'reviews_asc':
                        return $a['media_recensioni'] - $b['media_recensioni'];
                    case 'downloads_desc':
                        return $b['num_downloads'] - $a['num_downloads'];
                    case 'downloads_asc':
                        return $a['num_downloads'] - $b['num_downloads'];
                    default:
                        return 0;
                }
            });
        }

        $documents = array_map(function($doc) {
            return array(
                
                'title' => $doc['title'],
                'link' => $doc['link'],
                'html' => $doc['html'],
                'html_mini' => $doc['html_mini'],
            );
        }, $documents);

        wp_send_json_success($documents);
    } else {
        wp_send_json_error('Nessun documento trovato');
    }

    wp_die();
}

// Enqueue the script and localize the ajax URL
function enqueue_search_script() {
    wp_enqueue_script('search-products', get_stylesheet_directory_uri() . '/js/search-products.js', array('jquery'), null, true);
    wp_localize_script('search-products', 'env_ricerca', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('search_products_nonce'),
        'left_arrow' => get_stylesheet_directory_uri() . "/assets/img/user/sezione-documenti-caricati/leftArrow.png",
        'right_arrow' => get_stylesheet_directory_uri() . "/assets/img/user/sezione-documenti-caricati/rightArrow.png",
        'logo' => get_stylesheet_directory_uri() . "/assets/img/logo/MineDocs_Logo.png"
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_search_script');

function get_lista_tipo_istituto() {
    
    $terms = get_terms(array(
        'taxonomy' => 'tipo_istituto',
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
        )
    ));

    if (!empty($terms) && !is_wp_error($terms)) {
        $istituti = array();
        foreach ($terms as $term) {
            $istituti[] = array(
                'name' => $term->name,
                'slug' => $term->slug,
                'id' => $term->term_id,
            );
        }
        return $istituti;
    } else {
        return array();
    }
}

function get_lista_istituto() {
    
    $terms = get_terms(array(
        'taxonomy' => 'nome_istituto',
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
        )
    ));

    if (!empty($terms) && !is_wp_error($terms)) {
        $istituti = array();
        foreach ($terms as $term) {
            $istituti[] = array(
                'name' => $term->name,
                'slug' => $term->slug,
                'id' => $term->term_id,
            );
        }
        return $istituti;
    } else {
        return array();
    }
}

function get_lista_materia() {
    
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
        )
    ));

    if (!empty($terms) && !is_wp_error($terms)) {
        $materie = array();
        foreach ($terms as $term) {
            $materie[] = array(
                'name' => $term->name,
                'slug' => $term->slug,
                'id' => $term->term_id,
            );
        }
        return $materie;
    } else {
        return array();
    }
}

function get_lista_corso_di_studi() {
    
    $terms = get_terms(array(
        'taxonomy' => 'nome_corso_di_laurea',
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
        )
    ));

    if (!empty($terms) && !is_wp_error($terms)) {
        $corsi = array();
        foreach ($terms as $term) {
            $corsi[] = array(
                'name' => $term->name,
                'slug' => $term->slug,
                'id' => $term->term_id,
            );
        }
        return $corsi;
    } else {
        return array();
    }
}

function get_lista_anni_accademici($only_previous) {
    
    $terms = get_terms(array(
        'taxonomy' => 'anno_accademico',
        'hide_empty' => false,
    ));

    if (!empty($terms) && !is_wp_error($terms)) {
        $anni = array();
        $current_year = date('Y');
        foreach ($terms as $term) {
            $years = explode('/', $term->name);
            if (!$only_previous || ($only_previous && $years[0] <= $current_year)) {
                $anni[] = array(
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'id' => $term->term_id,
                );
            }
        }
        return $anni;
    } else {
        return array();
    }
}

function get_lista_tipo_documento() {
    
    $parent_term = get_term_by('slug', 'documento', 'tipo_prodotto');
    
    if ($parent_term) {
        $terms = get_terms(array(
            'taxonomy' => 'tipo_prodotto',
            'hide_empty' => false,
            'parent' => $parent_term->term_id,
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            $documenti = array();
            foreach ($terms as $term) {
                $documenti[] = array(
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'id' => $term->term_id,
                );
            }
            return $documenti;
        } else {
            return array();
        }
    } else {
        return array();
    }
}
