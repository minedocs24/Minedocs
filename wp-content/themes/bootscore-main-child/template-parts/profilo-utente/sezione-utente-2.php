<style>


</style>


<?php


$user_id = get_current_user_id();
$user = get_user_by("id", $user_id);
$user_information = getUserInformationJSON($user_id);
$user_name = get_user_meta($user->ID, 'first_name', true) . " " . get_user_meta($user->ID, 'last_name', true);
$userInformationArray = json_decode($user_information, true);
$icon_url = get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_scuola.svg' /* '/assets/img/search/istituto.svg' */;
$nome_istituto = get_user_istituto($user_id);

// Punti
$sistema_blu = get_sistema_punti('blu');
$sistema_pro = get_sistema_punti('pro');
$punti_blu = $sistema_blu->ottieni_totale_punti($user_id);
$punti_pro = $sistema_pro->ottieni_totale_punti($user_id);
$abbonamento_attivo = is_abbonamento_attivo($user_id);
$scadenza_abbonamento = get_user_meta($user_id, 'scadenza_abbonamento', true);
if($scadenza_abbonamento) {
    $scadenza_abbonamento = date('d/m/Y', strtotime($scadenza_abbonamento));
}

// Documenti
$current_user = wp_get_current_user();
$customer_orders = wc_get_orders(array(
    'customer_id' => $current_user->ID,
    'status' => 'completed',
    'limit' => -1,
));
$product_ids = array();
foreach ($customer_orders as $order) {
    foreach ($order->get_items() as $item) {
        $product_ids[] = $item->get_product_id();
    }
}

$documenti = array();
if (!empty($product_ids)) {
    $args = array(
        'post_type' => 'product',
        'post__in' => $product_ids,
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'tipo_prodotto',
                'field' => 'slug',
                'terms' => 'documento',
                'include_children' => true,
            ),
        ),
    );
    $documenti = get_posts($args);
}

// Documenti suggeriti
$corsi_dell_utente = get_user_meta($current_user->ID, 'nome_corso_di_laurea', true);
$corsi_dell_utente = array_map('intval', (array) $corsi_dell_utente);

$documenti_suggeriti = array();
if (!empty($product_ids)) {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post__not_in' => $product_ids,
        'orderby' => 'rand',
        'post_status' => 'publish',
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'tipo_prodotto',
                'field' => 'slug',
                'terms' => 'documento',
                'include_children' => true,
            ),
            array(
                'taxonomy' => 'nome_corso_di_laurea',
                'field' => 'term_id',
                'terms' => $corsi_dell_utente,
                'include_children' => true,
            )
        ),
        'author__not_in' => array($current_user->ID)
    );
    $documenti_suggeriti = get_posts($args);
}
?>

<div class="profile-dashboard">

<!-- Header Profilo -->
<div class="profile-header">
    <div class="profile-main">
        <div class="profile-left">
            <div class="profile-avatar-container">
                <img src="<?php echo get_user_avatar_url($user->ID) ?>" alt="Immagine profilo" class="profile-avatar">
            </div>
            <div class="profile-info">
                <h1 class="profile-name"><?php echo $user_name; ?></h1>
                <div class="profile-institute">
                    <img src="<?php echo esc_url($icon_url); ?>" alt="Icona Istituto" class="icon-instituto-profilo-utente">
                    <span class="institute-name-profilo-utente"><?php echo $nome_istituto; ?></span>
                </div>
            </div>
        </div>

        <div class="profile-stats" style="text-align: right;">
            <div class="stat-card">
                <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_review.svg' /* '/assets/img/search/stella.svg' */; ?>" alt="Icona Recensioni" class="stat-icon">
                <div class="stat-value"><?php echo isset($userInformationArray['reviews_on_user_documents_avg']) ? $userInformationArray['reviews_on_user_documents_avg'] : '0'; ?></div>
                <div class="stat-label"><?php echo isset($userInformationArray['reviews_on_user_documents']) ? $userInformationArray['reviews_on_user_documents'] : '0'; ?> recensioni</div>
            </div>
            <div class="stat-card">
                <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_documenti.svg'/* '/assets/img/home/books.webp' */; ?>" alt="Icona Upload" class="stat-icon">
                <div class="stat-value"><?php echo isset($userInformationArray['documents_count']) ? $userInformationArray['documents_count'] : '0'; ?></div>
                <div class="stat-label">Upload</div>
            </div>
            <div class="stat-card">
                <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_download.svg'/* '/assets/img/search/download.svg' */; ?>" alt="Icona Download" class="stat-icon">
                <div class="stat-value"><?php echo isset($userInformationArray['documents_downloaded_count']) ? $userInformationArray['documents_downloaded_count'] : '0'; ?></div>
                <div class="stat-label">Download</div>
            </div>
        </div>
    </div>
</div>




    <!-- Punti -->
    <div class="points-section">
        <h2 class="documents-title">I miei punti</h2>
        <div class="points-grid">
            <div class="points-card">
                <h5 class="mb-3">Punti Blu <?php echo $sistema_blu->print_icon(); ?></h5>
                <div class="points-circle punti-blu">
                    <strong><?php echo $punti_blu; ?></strong>
                </div>
            </div>
            <div class="points-card">
                <h5 class="mb-3">Punti Pro <?php echo $sistema_pro->print_icon(); ?></h5>
                <?php if($abbonamento_attivo) { ?>
                    <div class="points-circle punti-pro">
                        <strong><?php echo $punti_pro; ?></strong>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted d-block mb-2">Scadenza: <?php echo $scadenza_abbonamento; ?></small>
                        <button class="btn btn-outline-danger btn-sm" onclick="window.location.href = '<?php echo PACCHETTI_PUNTI_PAGE; ?>'">Ho bisogno di punti Pro!</button>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info mt-3 text-center">
                        <i class="bi bi-lightning-fill text-warning fs-5"></i>
                        <span class="ms-2">Non hai un abbonamento Pro attivo. 
                            <div class="text-center">
                                <button onclick="window.location.href = '<?php echo PIANI_PRO_PAGE; ?>'" class="btn btn-warning btn-sm">Attivalo ora!</button>
                            </div>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-outline-secondary btn-sm" onclick="showPointsModal()">Qual è la differenza?</button>
        </div>
    </div>

    <!-- I miei documenti -->
    
    <?php get_template_part('/template-parts/profilo-utente/sezione-miei-documenti', null, array(
        'documenti' => $documenti,
        'sezione' => 'miei-documenti',
        'classe-bordo' => 'mini-documento-bordo-verde',
        'classe-button' => 'btn-outline-secondary'
    )); ?>

    <!-- Documenti suggeriti -->
    <?php get_template_part('/template-parts/profilo-utente/sezione-potrebbe-interessarti', null, array(
        'documenti' => $documenti_suggeriti,
        'sezione' => 'documenti-suggeriti',
        'classe-bordo' => 'mini-documento-bordo-verde',
        'classe-button' => 'btn-outline-secondary'
    )); ?>


    <!-- Pulsante Upload -->
    <!--<div class="text-center mt-4">
        <a href="<?php //echo UPLOAD_PAGE; ?>" class="btn btn-primary upload-btn">Carica un documento</a>
        <p class="text-muted d-block text-center mt-2" style="font-size: 0.9rem;">Amplia la tua raccolta</p>
    </div>
    -->
</div>
