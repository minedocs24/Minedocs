<?php
/**
 * Template Name: Profilo Pubblico Utente
 * 
 * Pagina pubblica del profilo utente visitabile da chiunque
 */

// Verifica se è stato passato un ID utente
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$user_id) {
    wp_redirect(home_url());
    exit;
}

// Ottieni i dati dell'utente
$user = get_user_by('ID', $user_id);

if (!$user) {
    wp_redirect(home_url());
    exit;
}

// Ottieni i metadati dell'utente
$user_meta = get_user_meta($user_id);

// Calcola le statistiche dell'utente
$user_documents = get_posts(array(
    'post_type' => 'documento',
    'author' => $user_id,
    'post_status' => 'publish',
    'numberposts' => -1
));

$user_reviews = get_comments(array(
    'user_id' => $user_id,
    'status' => 'approve',
    'number' => -1
));

// Calcola la media delle recensioni
$total_rating = 0;
$review_count = 0;

foreach ($user_reviews as $review) {
    $rating = get_comment_meta($review->comment_ID, 'rating', true);
    if ($rating) {
        $total_rating += intval($rating);
        $review_count++;
    }
}

$average_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;

// Ottieni l'istituto dell'utente
$institute = isset($user_meta['institute'][0]) ? $user_meta['institute'][0] : '';
$course = isset($user_meta['course'][0]) ? $user_meta['course'][0] : '';

get_header();
?>

<div class="container-fluid minedocs-content">
    <div class="row">
        <!-- Sidebar Desktop -->
        <div class="col-lg-3 d-none d-lg-block">
            <div id="sidebarDesktop-profilo-utente" class="sidebar-profilo">
                <!-- Sidebar content will be populated by JavaScript -->
            </div>
        </div>

        <!-- Contenuto Principale -->
        <div class="col-lg-9 col-12">
            <div id="main-profile-section">
                
                <!-- Header del Profilo -->
                <div class="profile-header">
                    <div class="profile-main">
                        <div class="profile-left">
                            <div class="profile-avatar-container">
                                <?php 
                                $avatar_url = get_avatar_url($user_id, array('size' => 100));
                                if ($avatar_url) {
                                    echo '<img src="' . esc_url($avatar_url) . '" alt="Avatar di ' . esc_attr($user->display_name) . '" class="profile-avatar">';
                                } else {
                                    echo '<div class="profile-avatar-placeholder">' . substr($user->display_name, 0, 1) . '</div>';
                                }
                                ?>
                            </div>
                            <div class="profile-info">
                                <h1 class="profile-name"><?php echo esc_html($user->display_name); ?></h1>
                                <?php if ($institute || $course): ?>
                                <div class="profile-institute">
                                    <?php if ($institute): ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icons/institute-icon.svg" alt="Istituto" class="icon-instituto-profilo-utente">
                                        <span class="institute-name-profilo-utente"><?php echo esc_html($institute); ?></span>
                                    <?php endif; ?>
                                    <?php if ($course): ?>
                                        <span class="course-name-profilo-utente">• <?php echo esc_html($course); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Statistiche del Profilo -->
                        <div class="profile-stats">
                            <div class="stat-card">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icons/upload-icon.svg" alt="Documenti" class="stat-icon">
                                <div class="stat-value"><?php echo count($user_documents); ?></div>
                                <div class="stat-label">Documenti</div>
                            </div>
                            
                            <div class="stat-card">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icons/reviews-icon.svg" alt="Recensioni" class="stat-icon">
                                <div class="stat-value"><?php echo count($user_reviews); ?></div>
                                <div class="stat-label">Recensioni</div>
                            </div>
                            
                            <div class="stat-card">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icons/star-icon.svg" alt="Valutazione" class="stat-icon">
                                <div class="stat-value"><?php echo $average_rating; ?></div>
                                <div class="stat-label">Media Voti</div>
                            </div>
                            
                            <div class="stat-card">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icons/download-icon.svg" alt="Download" class="stat-icon">
                                <div class="stat-value"><?php echo isset($user_meta['total_downloads'][0]) ? intval($user_meta['total_downloads'][0]) : 0; ?></div>
                                <div class="stat-label">Download</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sezione Valutazione Media -->
                <?php if ($average_rating > 0): ?>
                <div class="rating-summary-section">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h3 class="card-title">Valutazione Media</h3>
                                    <div class="rating-display">
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $average_rating): ?>
                                                    <i class="fas fa-star text-warning"></i>
                                                <?php elseif ($i - $average_rating < 1): ?>
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-muted"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="rating-text">
                                            <span class="rating-value"><?php echo $average_rating; ?></span>
                                            <span class="rating-max">/ 5</span>
                                            <span class="rating-count">(<?php echo $review_count; ?> recensioni)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="rating-distribution">
                                        <?php
                                        $rating_counts = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
                                        foreach ($user_reviews as $review) {
                                            $rating = get_comment_meta($review->comment_ID, 'rating', true);
                                            if ($rating && isset($rating_counts[$rating])) {
                                                $rating_counts[$rating]++;
                                            }
                                        }
                                        ?>
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <div class="rating-bar">
                                                <span class="rating-label"><?php echo $i; ?> stelle</span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo $review_count > 0 ? ($rating_counts[$i] / $review_count) * 100 : 0; ?>%"></div>
                                                </div>
                                                <span class="rating-count-bar"><?php echo $rating_counts[$i]; ?></span>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sezione Documenti Recenti -->
                <div class="recent-documents-section">
                    <div class="section-header">
                        <h2>Documenti Recenti</h2>
                        <p>Gli ultimi documenti caricati da <?php echo esc_html($user->display_name); ?></p>
                    </div>
                    
                    <?php
                    $recent_documents = get_posts(array(
                        'post_type' => 'documento',
                        'author' => $user_id,
                        'post_status' => 'publish',
                        'numberposts' => 6,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ));
                    
                    if ($recent_documents): ?>
                    <div class="row">
                        <?php foreach ($recent_documents as $document): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card document-card h-100">
                                    <div class="position-relative">
                                        <?php if (has_post_thumbnail($document->ID)): ?>
                                            <?php echo get_the_post_thumbnail($document->ID, 'medium', array('class' => 'card-img-top')); ?>
                                        <?php else: ?>
                                            <div class="card-img-top document-placeholder">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="overlay">
                                            <a href="<?php echo get_permalink($document->ID); ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> Visualizza
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo esc_html($document->post_title); ?></h5>
                                        <p class="card-text text-muted">
                                            <small>
                                                <i class="fas fa-calendar"></i> <?php echo get_the_date('', $document->ID); ?>
                                                <br>
                                                <i class="fas fa-download"></i> <?php echo get_post_meta($document->ID, 'download_count', true) ?: 0; ?> download
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nessun documento caricato ancora.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sezione Recensioni Recenti -->
                <?php if ($user_reviews): ?>
                <div class="recent-reviews-section">
                    <div class="section-header">
                        <h2>Recensioni Recenti</h2>
                        <p>Le ultime recensioni scritte da <?php echo esc_html($user->display_name); ?></p>
                    </div>
                    
                    <?php
                    $recent_reviews = array_slice($user_reviews, 0, 3);
                    ?>
                    <div class="row">
                        <?php foreach ($recent_reviews as $review): ?>
                            <?php
                            $document = get_post($review->comment_post_ID);
                            $rating = get_comment_meta($review->comment_ID, 'rating', true);
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card review-card h-100">
                                    <div class="card-body">
                                        <div class="review-header">
                                            <div class="review-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $rating): ?>
                                                        <i class="fas fa-star text-warning"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star text-muted"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> <?php echo get_comment_date('', $review->comment_ID); ?>
                                            </small>
                                        </div>
                                        <h6 class="review-document">
                                            <a href="<?php echo get_permalink($document->ID); ?>">
                                                <?php echo esc_html($document->post_title); ?>
                                            </a>
                                        </h6>
                                        <p class="review-text"><?php echo wp_trim_words($review->comment_content, 20); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sezione Informazioni Aggiuntive -->
                <div class="additional-info-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card info-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle text-primary"></i>
                                        Informazioni Utente
                                    </h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Membro dal:</strong> <?php echo get_the_date('', $user->user_registered); ?></li>
                                        <?php if ($institute): ?>
                                            <li><strong>Istituto:</strong> <?php echo esc_html($institute); ?></li>
                                        <?php endif; ?>
                                        <?php if ($course): ?>
                                            <li><strong>Corso:</strong> <?php echo esc_html($course); ?></li>
                                        <?php endif; ?>
                                        <li><strong>Documenti caricati:</strong> <?php echo count($user_documents); ?></li>
                                        <li><strong>Recensioni scritte:</strong> <?php echo count($user_reviews); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card info-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line text-success"></i>
                                        Statistiche
                                    </h5>
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo isset($user_meta['total_views'][0]) ? intval($user_meta['total_views'][0]) : 0; ?></div>
                                            <div class="stat-label">Visualizzazioni</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo isset($user_meta['total_likes'][0]) ? intval($user_meta['total_likes'][0]) : 0; ?></div>
                                            <div class="stat-label">Mi piace</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo isset($user_meta['total_shares'][0]) ? intval($user_meta['total_shares'][0]) : 0; ?></div>
                                            <div class="stat-label">Condivisioni</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sezione Call to Action -->
                <div class="cta-section">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body text-center">
                            <h3>Vuoi vedere tutti i documenti di <?php echo esc_html($user->display_name); ?>?</h3>
                            <p class="lead">Esplora la collezione completa di documenti e materiali di studio.</p>
                            <a href="<?php echo home_url('/search/?author=' . $user_id); ?>" class="btn btn-light btn-lg">
                                <i class="fas fa-search"></i> Cerca Documenti
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Sidebar Mobile -->
<div id="sidebarMobile-profilo-utente" class="d-lg-none">
    <!-- Sidebar content will be populated by JavaScript -->
</div>

<!-- Overlay per mobile -->
<div id="overlay-profilo-utente" class="d-lg-none"></div>

<!-- Bottom Navigation -->
<nav class="bottom-nav d-lg-none">
    <a href="<?php echo home_url(); ?>" class="bottom-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="<?php echo home_url('/search'); ?>" class="bottom-nav-item">
        <i class="fas fa-search"></i>
        <span>Cerca</span>
    </a>
    <a href="<?php echo home_url('/upload'); ?>" class="bottom-nav-item">
        <i class="fas fa-upload"></i>
        <span>Carica</span>
    </a>
    <a href="<?php echo home_url('/profile'); ?>" class="bottom-nav-item active">
        <i class="fas fa-user"></i>
        <span>Profilo</span>
    </a>
</nav>

<style>
/* Stili specifici per la pagina pubblica del profilo */

.profile-avatar-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    border: 4px solid var(--primary-light);
}

.course-name-profilo-utente {
    color: var(--gray-600);
    font-size: 0.9rem;
}

.rating-summary-section {
    margin-bottom: 2rem;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.rating-stars {
    font-size: 1.5rem;
}

.rating-text {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
}

.rating-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.rating-max {
    font-size: 1.25rem;
    color: var(--gray-600);
}

.rating-count {
    font-size: 0.9rem;
    color: var(--gray-500);
}

.rating-distribution {
    margin-top: 1rem;
}

.rating-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.rating-label {
    min-width: 60px;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.rating-bar .progress {
    flex: 1;
    height: 8px;
    border-radius: 4px;
}

.rating-count-bar {
    min-width: 30px;
    font-size: 0.875rem;
    color: var(--gray-600);
    text-align: right;
}

.section-header {
    margin-bottom: 2rem;
    text-align: center;
}

.section-header h2 {
    font-size: 2rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.section-header p {
    color: var(--gray-600);
    font-size: 1.1rem;
}

.document-placeholder {
    height: 200px;
    background-color: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-400);
    font-size: 3rem;
}

.review-card {
    border: 1px solid var(--gray-200);
    transition: var(--transition);
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--box-shadow);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.review-rating {
    font-size: 0.875rem;
}

.review-document {
    margin-bottom: 0.75rem;
}

.review-document a {
    color: var(--primary);
    text-decoration: none;
}

.review-document a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.review-text {
    color: var(--gray-700);
    font-size: 0.9rem;
    line-height: 1.5;
}

.info-card {
    height: 100%;
    border: 1px solid var(--gray-200);
    transition: var(--transition);
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--box-shadow);
}

.info-card .card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
}

.info-card ul li {
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--gray-100);
}

.info-card ul li:last-child {
    border-bottom: none;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background-color: var(--gray-50);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.stat-item:hover {
    background-color: var(--primary-light);
    color: white;
}

.stat-item .stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.25rem;
}

.stat-item:hover .stat-number {
    color: white;
}

.stat-item .stat-label {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.stat-item:hover .stat-label {
    color: rgba(255, 255, 255, 0.9);
}

.cta-section {
    margin-top: 3rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .profile-stats {
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 1rem;
    }
    
    .stat-card {
        flex: 1 1 calc(50% - 1rem);
        min-width: 120px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .profile-main {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .profile-left {
        flex-direction: column;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .profile-stats {
        width: 100%;
        justify-content: space-around;
    }
    
    .stat-card {
        flex: 1 1 calc(25% - 0.5rem);
        min-width: 80px;
    }
    
    .rating-display {
        flex-direction: column;
        text-align: center;
    }
    
    .section-header h2 {
        font-size: 1.5rem;
    }
    
    .section-header p {
        font-size: 1rem;
    }
}
</style>

<?php get_footer(); ?>
