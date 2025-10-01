<?php

/**
 * Template Name: Diventa Venditore
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;
$class_padding_admin = current_user_can('administrator') ? 'pt-5' : '';

get_header();
?>



<div id="content" class="container-fluid <?php echo $class_padding_admin; ?>">

<section class="container py-5 position-relative">
<div class="row align-items-center">

    <!-- Testo a sinistra -->
    <div class="col-lg-8 text-center text-lg-start">

<div>
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo/logo-solo-libro.svg"
        class="img-fluid mb-4" style="max-width: 250px;">
</div>
<h1 class="display-4 fw-bold text-dark mb-3">Diventa un Venditore su MineDocs!</h1>
<p class="lead text-muted mb-4">
    Unisciti a noi e trasforma le tue conoscenze in guadagni! Con MineDocs, puoi condividere i tuoi appunti, riassunti e materiali didattici con studenti di tutto il mondo, guadagnando ad ogni download. Non perdere l'opportunità di far crescere la tua carriera accademica e di aiutare altri nel loro percorso di studio.
</p>
<div class="d-flex gap-3 justify-content-center justify-content-lg-start">
    <a href="<?php echo LOGIN_PAGE; ?>" class="btn btn-secondary btn-lg px-4 shadow-sm">
        Inizia ora
    </a>
</div>


</div>
<!-- Immagine a destra (visibile solo su schermi grandi) -->
<div class="col-lg-4 text-center d-none d-lg-block">

<svg viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg">
  <!-- Documento principale - Più grande e al centro -->
  <g transform="rotate(-3 400 200)">
    <rect x="280" y="100" width="240" height="180" rx="16" fill="url(#gradPrimary)" filter="url(#shadow)" />
    <rect x="305" y="130" width="190" height="16" rx="8" fill="white" fill-opacity="0.5" />
    <rect x="305" y="160" width="160" height="14" rx="7" fill="white" fill-opacity="0.5" />
    <rect x="305" y="185" width="180" height="14" rx="7" fill="white" fill-opacity="0.5" />
  </g>

  <!-- Banconota più piccola e discreta -->
  <g transform="rotate(1 400 360)">
    <rect x="320" y="370" width="180" height="60" rx="12" fill="url(#gradCash)" filter="url(#shadow)" />
    <rect x="325" y="375" width="170" height="50" rx="10" fill="none" stroke="white" stroke-opacity="0.2" stroke-width="1.5" />
    <circle cx="355" cy="400" r="18" fill="rgba(255,255,255,0.15)" />
    <text x="347" y="406" font-size="18" font-weight="bold" fill="white" font-family="sans-serif">€</text>
    <rect x="390" y="390" width="90" height="8" rx="4" fill="white" fill-opacity="0.25" />
    <rect x="390" y="404" width="70" height="8" rx="4" fill="white" fill-opacity="0.25" />
  </g>

  <!-- Monete ingrandite e ben distribuite -->
  <g>
    <!-- Grande -->
    <circle cx="540" cy="300" r="22" fill="url(#gradGold)" stroke="#e0a800" stroke-width="2" />
    <text x="528" y="308" font-size="16" font-weight="bold" fill="#fff" font-family="sans-serif">€</text>

    <!-- Media -->
    <circle cx="580" cy="340" r="18" fill="url(#gradGold)" stroke="#e0a800" stroke-width="1.8" />
    <text x="568" y="348" font-size="14" font-weight="bold" fill="#fff" font-family="sans-serif">€</text>

    <!-- Piccola -->
    <circle cx="550" cy="380" r="14" fill="url(#gradGold)" stroke="#e0a800" stroke-width="1.5" />
    <text x="542" y="386" font-size="12" font-weight="bold" fill="#fff" font-family="sans-serif">€</text>

    <!-- Molto piccole -->
    <circle cx="500" cy="390" r="10" fill="url(#gradGold)" stroke="#e0a800" stroke-width="1" />
    <circle cx="580" cy="400" r="10" fill="url(#gradGold)" stroke="#e0a800" stroke-width="1" />
  </g>

  <!-- Definizioni -->
  <defs>
    <linearGradient id="gradPrimary" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="var(--primary)" />
      <stop offset="100%" stop-color="var(--primary-light)" />
    </linearGradient>

    <linearGradient id="gradGold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#f8d347" />
      <stop offset="100%" stop-color="#f1b100" />
    </linearGradient>

    <linearGradient id="gradCash" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#7ed6a5" />
      <stop offset="100%" stop-color="#4ec08a" />
    </linearGradient>

    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="rgba(0,0,0,0.2)" />
    </filter>
  </defs>
</svg>







</div>
</div>
</section>



    <div class="divisore-sezioni"></div>

    <!-- Sezione Vantaggi -->
    <!-- <section class="container my-5">
        <h2 class="text-center mb-4">Perché Diventare un Venditore?</h2>
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card vantaggi-card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-2">
                            <?php /* get_template_part('template-parts/commons/icon', null, array('icon_name' => 'price', 'size'=>64)); */ ?>
                        </div>
                        <h4 class="vantaggi-card-title">Stabilisci il tuo Prezzo</h4>
                        <p class="vantaggi-card-text">Hai il controllo totale sul valore dei tuoi documenti e guadagni per ogni download.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card vantaggi-card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-2">
                            <?php /* get_template_part('template-parts/commons/icon', null, array('icon_name' => 'community', 'size'=>64)); */ ?>
                        </div>
                        <h4 class="vantaggi-card-title">Raggiungi una Community Globale</h4>
                        <p class="vantaggi-card-text">Collegati con studenti di tutto il mondo in cerca di risorse di alta qualità.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card vantaggi-card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-2">
                            <?php /* get_template_part('template-parts/commons/icon', null, array('icon_name' => 'money-pig', 'size'=>64)); */ ?>
                        </div>
                        <h4 class="vantaggi-card-title">Prelievi Facili e Veloci</h4>
                        <p class="vantaggi-card-text">Ritira i tuoi guadagni in modo semplice tramite PayPal.</p>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <div class="divisore-sezioni"></div>



        
    </style>

    <section class="container py-5 bg-light">
        <div class="text-center mb-5">
            <div class="d-inline-block px-3 py-1 rounded-pill bg-primary text-white bg-opacity-10 small mb-3">
                Come funziona
            </div>
            <h2 class="display-5 fw-bold mb-3">4 semplici passi per iniziare</h2>
            <p class="text-muted mx-auto diventare-venditore-section">
                Dal momento in cui ti registri, potrai vendere i tuoi documenti in pochi semplici passaggi su MineDocs.
            </p>
        </div>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm position-relative h-100">
                    <div class="card-body p-4" style="background-color: var(--secondary-green);">
                        <div class="diventare-venditore-step-badge">1</div>
                        <div class="diventare-venditore-icon">
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg> -->
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/Icone_Minedocs_carica documenti.svg" alt="Carica i tuoi documenti" width="35" height="35" />
                        </div>
                        <h5 class="card-title text-center">Carica i tuoi documenti</h5>
                        <p class="card-text text-center text-muted">Seleziona i tuoi migliori appunti e caricali sulla nostra piattaforma.</p>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm position-relative h-100">
                    <div class="card-body p-4" style="background-color: var(--complement-violet);">
                        <div class="diventare-venditore-step-badge">2</div>
                        <div class="diventare-venditore-icon">
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg> -->
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/Icone_Minedocs_imposta prezzo.svg" alt="Imposta il prezzo" width="35" height="35" />
                        </div>
                        <h5 class="card-title text-center">Imposta il Prezzo</h5>
                        <p class="card-text text-center text-muted">Decidi il prezzo di vendita per ogni documento che carichi.</p>
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm position-relative h-100">
                    <div class="card-body p-4" style="background-color: var(--complement-orange);">
                        <div class="diventare-venditore-step-badge">3</div>
                        <div class="diventare-venditore-icon">
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-2 16H8V4h4v14zm-4-4h4v4H8v-4zm6 0h4v4h-4v-4z"></path>
                            </svg> -->
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/Icone_Minedocs_guadagna.svg" alt="Guadagna ad ogni Download" width="35" height="35" />
                        </div>
                        <h5 class="card-title text-center">Guadagna ad ogni Download</h5>
                        <p class="card-text text-center text-muted">Guadagni ogni volta che un utente scarica il tuo documento.</p>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm position-relative h-100">
                    <div class="card-body p-4" style="background-color: var(--secondary-blue);">
                        <div class="diventare-venditore-step-badge">4</div>
                        <div class="diventare-venditore-icon">
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-2 16H8V4h4v14zm-4-4h4v4H8v-4zm6 0h4v4h-4v-4z"></path>
                            </svg> -->
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/Icone_Minedocs_prelievi_semplici.svg" alt="Prelievi Semplici" width="35" height="35" />
                        </div>
                        <h5 class="card-title text-center">Prelievi Semplici</h5>
                        <p class="card-text text-center text-muted">Ritira i tuoi guadagni facilmente tramite PayPal.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <div class="divisore-sezioni"></div>

    <!-- Sezione Testimonianze -->
    <section class="container my-5">
        <div class="text-center mb-5">
    <div class="d-inline-block px-3 py-1 rounded-pill bg-primary text-white bg-opacity-10 small mb-3">
                Testimonianze
            </div>
        <h2 class="text-center mb-4">Cosa ne pensano gli altri venditori</h2>
        <p class="text-center mb-4">Scopri le storie di successo dei nostri venditori e come hanno trasformato i loro studi in un business.</p>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="diventa-venditore-testimonianze-testimonial d-flex align-items-center diventa-venditore-testimonianze-testimonial-item">
                    <img src="venditore1.jpg" alt="Alessandro R." class="diventa-venditore-testimonianze-testimonial-image me-3">
                    <div class="diventa-venditore-testimonianze-testimonial-content">
                        <p class="diventa-venditore-testimonianze-testimonial-name"><strong>Alessandro R.</strong></p>
                        <p class="diventa-venditore-testimonianze-testimonial-university">Economia e Management, Università di Bologna</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="diventa-venditore-testimonianze-testimonial-rating mb-0">★ ★ ★ ★ ★</p>
                            <p class="diventa-venditore-testimonianze-testimonial-earnings mb-0"><strong>500€/mese</strong></p>
                        </div>
                        <p class="diventa-venditore-testimonianze-testimonial-quote">"Vendere i miei appunti su MineDocs mi ha permesso di guadagnare oltre 500€ al mese. La piattaforma è facile da usare e i pagamenti sono sempre puntuali!"</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="diventa-venditore-testimonianze-testimonial d-flex align-items-center diventa-venditore-testimonianze-testimonial-item">
                    <img src="venditore2.jpg" alt="Francesca M." class="diventa-venditore-testimonianze-testimonial-image me-3">
                    <div class="diventa-venditore-testimonianze-testimonial-content">
                        <p class="diventa-venditore-testimonianze-testimonial-name"><strong>Francesca M.</strong></p>
                        <p class="diventa-venditore-testimonianze-testimonial-university">Ingegneria Informatica, Politecnico di Milano</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="diventa-venditore-testimonianze-testimonial-rating mb-0">★ ★ ★ ★ ★</p>
                            <p class="diventa-venditore-testimonianze-testimonial-earnings mb-0"><strong>750€/mese</strong></p>
                        </div>
                        <p class="diventa-venditore-testimonianze-testimonial-quote">"Fantastico! Ho iniziato vendendo i miei riassunti di programmazione e ora ho una community di oltre 200 studenti che acquistano regolarmente i miei contenuti."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Sezione Call to Action -->
    <section class="my-5">
        <div class="container text-center bg-primary text-white p-5 rounded">
        <h2 class="text-white">Pronto a iniziare?</h2>
        <p class="lead text-white">Unisciti a migliaia di studenti che hanno già iniziato a guadagnare con MineDocs. La registrazione è gratuita e puoi iniziare a vendere subito.</p>
        <a href="<?php echo LOGIN_PAGE; ?>" class="btn btn-light btn-lg">Crea un account ed inizia a vendere!</a>
        </div>
    </section>

</div>
<?php

get_footer();
?>
