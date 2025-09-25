<?php

get_header();
?>

<main id="main" class="site-main">


    <section class="container py-5 position-relative">
        <div class="row align-items-center">
        <!-- Inserisci il banner HomePage Banner.png -->
        <!-- <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/home/BannerHomepage.png" class="img-fluid mb-4" style="max-width: 100%;"> -->   
                    
        <!-- Testo a sinistra -->
            <div class="col-lg-6 text-center text-lg-start">

                <div>
                    <img src="https://minedocs.it/wp-content/themes/bootscore-main-child/assets/img/logo/logo-solo-libro.svg"
                        class="img-fluid mb-4" style="max-width: 250px;">
                </div>
                <h1 class="display-4 fw-bold text-dark mb-3">Studia smart, sogna in grande!</h1>
                <p class="lead text-muted mb-4">
                    Appunti, riassunti, quiz e strumenti per aiutarti nel tuo percorso accademico. Tutto in un unico posto.
                </p>
                <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
                    <a href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>" class="btn btn-primary btn-lg px-4 shadow-sm">
                        Carica un documento
                    </a>
                    <a href="<?php echo DIVENTA_VENDITORE; ?>" class="btn btn-outline-secondary btn-lg px-4 shadow-sm">
                        Diventa venditore
                    </a>
                </div>

                <div class="search-section-wrapper py-2 position-relative">
                    <?php get_template_part('template-parts/home/search-bar-2'); ?>


                    <!-- Freccia decorativa -->
                    <div style="position: absolute; top: 20%; left: -130px; transform: rotate(10deg); width: 120px; height: auto;">
                        <svg viewBox="0 0 91 91" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                            <style>
                                .st0 {
                                    fill: var(--primary);
                                }
                            </style>
                            <g>
                                <path class="st0" d="M81.9,10.2c-14,3.4-27.9,1.2-41.7-2c-3.1-0.7-5.5,4-2.3,5.4c6.6,3,13.1,5.2,19.8,6.4
                            c-11.1,5.2-21,12.7-25.5,23.9c-18.7,0.7-29.9,15-28,33.7c0.3,2.7,4.4,2.9,4.3,0c-0.1-10,6.6-21.6,16.4-24.3c1.5-0.4,3.2-0.7,5-0.9
                            c-1,8,0.9,15.8,6.7,21.9c5.7,6,13.5,5.5,19.9,0.3c6.7-5.6,5.9-13.8,1.3-20.6c-3.7-5.5-9-8.1-14.8-9.3c1.9-2.8,4.2-5.3,7.1-7.3
                            c6.2-4.4,13.4-7.6,20.7-10.2c-3.4,7.2-6,14.9-5.8,21.8c0.2,6.5,9.6,6.4,10,0c0.7-11,6.3-22.9,12.1-32.1
                            C89.4,13.5,85.7,9.3,81.9,10.2z M53.2,61.6c1.7,5.4-3.7,12.2-9.8,8.9c-5.7-3.1-5.4-12.5-4-18c1.8,0.2,3.6,0.6,5.1,1.1
                            C48.1,54.9,52,57.8,53.2,61.6z" />
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Immagine a destra (visibile solo su schermi grandi) -->
            <div class="col-lg-6 text-center d-none d-lg-block">
                <svg viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg">
                    <!-- Documento blu inclinato a sinistra -->
                    <g transform="rotate(-6 100 100)">
                        <rect x="40" y="20" width="160" height="100" rx="10" fill="url(#gradPrimary)" filter="url(#shadow)" />
                        <rect x="60" y="40" width="120" height="8" rx="4" fill="var(--white)" fill-opacity="0.5" />
                        <rect x="60" y="55" width="80" height="8" rx="4" fill="var(--white)" fill-opacity="0.5" />
                        <rect x="60" y="70" width="100" height="8" rx="4" fill="var(--white)" fill-opacity="0.5" />
                    </g>

                    <!-- Documento bianco inclinato a destra -->
                    <g transform="rotate(4 200 200)">
                        <rect x="160" y="120" width="220" height="130" rx="10" fill="var(--white)" filter="url(#shadow)" />
                        <rect x="180" y="140" width="180" height="10" rx="5" fill="var(--gray-200)" />
                        <rect x="180" y="160" width="135" height="10" rx="5" fill="var(--gray-200)" />
                        <rect x="180" y="180" width="165" height="10" rx="5" fill="var(--gray-200)" />
                        <rect x="180" y="200" width="110" height="10" rx="5" fill="var(--gray-200)" />
                    </g>

                    <!-- Documento viola a destra inclinato -->
                    <g transform="rotate(10 480 100)">
                        <rect x="410" y="40" width="160" height="100" rx="10" fill="url(#gradSecondary)" filter="url(#shadow)" />
                        <rect x="430" y="60" width="120" height="8" rx="4" fill="var(--white)" fill-opacity="0.5" />
                        <rect x="430" y="75" width="80" height="8" rx="4" fill="var(--white)" fill-opacity="0.5" />
                        <rect x="430" y="90" width="100" height="8" rx="4" fill="var(--white)" fill-opacity="0.5" />
                    </g>

                    <!-- Documento bianco in basso -->
                    <g transform="rotate(-7 480 260)">
                        <rect x="410" y="220" width="190" height="100" rx="10" fill="var(--white)" filter="url(#shadow)" />
                        <rect x="430" y="240" width="150" height="10" rx="5" fill="var(--gray-200)" />
                        <rect x="430" y="260" width="130" height="10" rx="5" fill="var(--gray-200)" />
                        <rect x="430" y="280" width="110" height="10" rx="5" fill="var(--gray-200)" />
                    </g>

                    <!-- Gradiente blu -->
                    <defs>
                        <linearGradient id="gradPrimary" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="var(--primary)" />
                            <stop offset="100%" stop-color="var(--primary-light)" />
                        </linearGradient>

                        <!-- Gradiente secondario -->
                        <linearGradient id="gradSecondary" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="var(--secondary)" />
                            <stop offset="100%" stop-color="var(--secondary-light)" />
                        </linearGradient>

                        <!-- Ombra -->
                        <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                            <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="rgba(0,0,0,0.2)" />
                        </filter>
                    </defs>
                </svg>
            </div>
        </div>
    </section>






    <!-- Iscriviti -->


    <?php if (!is_user_logged_in()) : ?>
    <section class="py-5 overflow-hidden position-relative bg-gradient">
        <div class="container position-relative z-1">
            <div class="rounded-4 bg-primary bg-gradient p-4 p-md-5 shadow-lg position-relative text-white">

                <!-- Background circles -->
                <div class="position-absolute top-0 end-0 translate-middle bg-white bg-opacity-10 rounded-circle" style="width: 200px; height: 200px; top: -100px; right: -100px;"></div>
                <div class="position-absolute bottom-0 start-0 translate-middle bg-white bg-opacity-10 rounded-circle" style="width: 200px; height: 200px; bottom: -100px; left: -100px;"></div>

                <div class="row align-items-center g-5">
                    <!-- Text -->
                    <div class="col-lg-6">
                        <h2 class="display-5 fw-bold mb-4 text-white">
                            Inizia oggi il tuo percorso verso il successo accademico
                        </h2>
                        <p class="mb-4 lead text-white">
                            Unisciti a migliaia di studenti che stanno già utilizzando MineDocs per migliorare il loro rendimento universitario.
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <a href="<?php echo LOGIN_PAGE; ?>" class="btn btn-light text-primary btn-lg px-4">Registrati - È gratis</a>
                            <a href="<?php echo RICERCA_PAGE; ?>" class="btn btn-outline-light btn-lg px-4">Esplora le risorse</a>
                        </div>
                    </div>

                    <!-- Decorative bubbles -->
                    <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center">
                        <div class="position-relative" style="width: 320px; height: 320px;">
                            <div class="position-absolute top-25 start-0 bg-white bg-opacity-10 rounded-3 text-center d-flex align-items-center justify-content-center p-3 shadow"
                                style="width: 100%; height: 60px; transform: rotate(-6deg);">
                                <span class="text-white">Più di 1000 studenti si sono uniti</span>
                            </div>
                            <div class="position-absolute top-50 start-25 bg-white bg-opacity-10 rounded-3 text-center d-flex align-items-center justify-content-center p-3 shadow"
                                style="width: 100%; height: 60px; transform: rotate(4deg);">
                                <span class="text-white">Decine di documenti aggiunti ogni giorno</span>
                            </div>
                            <div class="position-absolute bottom-0 start-0 bg-white bg-opacity-10 rounded-3 text-center d-flex align-items-center justify-content-center p-3 shadow"
                                style="width: 100%; height: 60px; transform: rotate(-3deg);">
                                <span class="text-white">Accesso a risorse esclusive</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php else : ?>
    <!-- Passa a Pro -->
    <section id="passa-a-pro" class="my-5">
        <div class="container">
            <div class="text-center  bg-secondary p-5 rounded-4">
                <h2 class="fw-bold text-white mb-3">MineDocs può rivoluzionare il tuo percorso accademico!</h2>
                <p class="lead text-white mb-4">Con il piano Pro, puoi accedere a tutte le funzionalità di MineDocs e migliorare il tuo studio.</p>
                <a href="<?php echo PIANI_PRO_PAGE; ?>" class="btn btn-pro btn-lg shadow">Scopri i benefici di <span class="pro">PRO</span></a>
            </div>
    </section>

<?php endif; ?>



    <!-- Sezione CTA Multiple -->
    <section id="home-cta-multiple" class="cta-section py-5">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-12 mb-4">
                    <h2 class="display-4 title-section">Cosa vuoi fare oggi?</h2>
                    <p class="lead">Scegli una delle azioni per iniziare il tuo percorso di apprendimento.</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card course-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--primary);">
                                <i class="bi bi-robot"></i>
                            </span>
                            <h3 class="card-title mb-2">Studia con l'AI</h3>
                            <span class="mb-3" style="width: 128px; height:128px; display:inline-block;">
                                <svg width="128" height="128" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Robot che studia">
                                    <rect x="10" y="18" width="44" height="28" rx="10" fill="var(--primary)" stroke="var(--primary-dark)" stroke-width="2" />
                                    <rect x="18" y="26" width="28" height="16" rx="6" fill="var(--white)" stroke="var(--primary-dark)" stroke-width="1.5" />
                                    <circle cx="24" cy="34" r="2.5" fill="var(--primary)" />
                                    <circle cx="40" cy="34" r="2.5" fill="var(--primary)" />
                                    <rect x="28" y="38" width="8" height="2" rx="1" fill="var(--primary-light)" />
                                    <rect x="28" y="12" width="8" height="8" rx="4" fill="var(--secondary)" stroke="var(--primary-dark)" stroke-width="1.5" />
                                    <rect x="22" y="48" width="20" height="4" rx="2" fill="var(--secondary-light)" />
                                    <rect x="16" y="52" width="32" height="4" rx="2" fill="var(--gray-200)" />
                                    <rect x="12" y="56" width="40" height="3" rx="1.5" fill="var(--gray-300)" />
                                    <rect x="46" y="22" width="6" height="2" rx="1" fill="var(--accent)" />
                                    <rect x="12" y="22" width="6" height="2" rx="1" fill="var(--accent)" />
                                    <!-- Libro aperto -->
                                    <path d="M20 44 Q24 42 32 44 Q40 42 44 44 L44 48 Q40 46 32 48 Q24 46 20 48 Z" fill="var(--white)" stroke="var(--primary-dark)" stroke-width="1" />
                                    <line x1="32" y1="44" x2="32" y2="48" stroke="var(--primary)" stroke-width="1" />
                                </svg>
                            </span>

                            <p class="card-text mb-4">Sfrutta l'intelligenza artificiale per approfondire e comprendere meglio i tuoi argomenti di studio.</p>
                            <a href="<?php echo STUDIA_CON_AI_PAGE; ?>" class="btn btn-primary btn-lg mt-auto w-100">Inizia ora</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card course-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--secondary);">
                                <i class="bi bi-journal-bookmark"></i>
                            </span>
                            <h3 class="card-title mb-2">Sfoglia i documenti</h3>
                            <span class="mb-3" style="width: 128px; height:128px; display:inline-block;">
                                <svg width="128" height="128" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Icona foglio di appunti">
                                    <!-- Foglio di appunti -->
                                    <rect x="14" y="10" width="36" height="44" rx="4" fill="var(--white)" stroke="var(--secondary)" stroke-width="2" />
                                    <!-- Linee del foglio -->
                                    <rect x="20" y="20" width="24" height="2" rx="1" fill="var(--gray-300)" />
                                    <rect x="20" y="26" width="18" height="2" rx="1" fill="var(--gray-200)" />
                                    <rect x="20" y="32" width="20" height="2" rx="1" fill="var(--gray-200)" />
                                    <rect x="20" y="38" width="16" height="2" rx="1" fill="var(--gray-200)" />
                                    <rect x="20" y="44" width="22" height="2" rx="1" fill="var(--gray-200)" />
                                    <!-- Fori laterali del foglio -->
                                    <circle cx="17" cy="18" r="1.2" fill="var(--accent)" />
                                    <circle cx="17" cy="28" r="1.2" fill="var(--accent)" />
                                    <circle cx="17" cy="38" r="1.2" fill="var(--accent)" />
                                    <circle cx="17" cy="48" r="1.2" fill="var(--accent)" />
                                </svg>
                            </span>
                            <p class="card-text mb-4">Consulta appunti, libri e risorse utili per il tuo percorso scolastico o universitario.</p>
                            <a href="<?php echo RICERCA_PAGE; ?>" class="btn btn-outline-secondary btn-lg mt-auto w-100">Sfoglia ora</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card course-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--primary);">
                                <i class="bi bi-mortarboard"></i>
                            </span>
                            <h3 class="card-title mb-2">Segui i corsi</h3>
                            <span class="mb-3" style="width: 128px; height:128px; display:inline-block;">
                                <svg width="128" height="128" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Icona videocorso">
                                    <!-- Schermo -->
                                    <rect x="10" y="16" width="44" height="28" rx="4" fill="var(--white)" stroke="var(--primary)" stroke-width="2" />
                                    <!-- Bordo inferiore (base del monitor) -->
                                    <rect x="24" y="46" width="16" height="4" rx="2" fill="var(--gray-200)" />
                                    <!-- Pulsante play -->
                                    <circle cx="32" cy="30" r="9" fill="var(--primary-light)" />
                                    <polygon points="30,26 38,30 30,34" fill="var(--white)" />
                                    <!-- Ombra sotto il monitor -->
                                    <ellipse cx="32" cy="54" rx="14" ry="3" fill="var(--gray-300)" opacity="0.5" />
                                </svg>
                            </span>
                            <p class="card-text mb-4">Partecipa ai corsi online e migliora le tue competenze con lezioni strutturate.</p>
                            <a href="/coming-soon-courses" class="btn btn-secondary btn-lg mt-auto w-100">Scopri i corsi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>






    <!-- I nostri servizi -->
    <!--
    <section id="our-services" class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 title-section">I nostri servizi</h2>
                    <p class="lead">Scopri le funzionalità che offre MineDocs per aiutarti nel tuo percorso di studio.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--primary);">
                                <i class="bi bi-book"></i>
                            </span>
                            <h3 class="card-title mb-2">Risorse didattiche</h3>
                            <p class="card-text mb-4">Trova milioni di documenti universitari, libri e materiali didattici.</p>
                            <a href="/risorse" class="btn btn-primary btn-lg mt-auto w-100">Esplora le risorse</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--secondary);">
                                <i class="bi bi-clock"></i>
                            </span>
                            <h3 class="card-title mb-2">Tempo risparmiato</h3>
                            <p class="card-text mb-4">Salta le lunghe lezioni e concentrati sulle lezioni più importanti.</p>
                            <a href="/tempo-risparmiato" class="btn btn-secondary btn-lg mt-auto w-100">Scopri come</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--accent);">
                                <i class="bi bi-lightbulb"></i>
                            </span>
                            <h3 class="card-title mb-2">Accesso a risorse esclusive</h3>
                            <p class="card-text mb-4">Scopri le risorse più rare e utili per il tuo percorso di studio.</p>
                            <a href="/risorse-esclusive" class="btn btn-warning btn-lg mt-auto w-100">Esplora le risorse</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    -->





    <!-- Call to Action Section -->
    <!--<section id="home-banner1" class="cta-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <?php //get_template_part('template-parts/home/banner-calltoaction'); 
                    ?>
                </div>
                <div class="col-lg-12">
                    <?php //get_template_part('template-parts/home/banner-informazioni'); 
                    ?>
                </div>
            </div>
        </div>
    </section>-->

    <!-- Traguardi -->
    <section id="home-traguardi" class="home-traguardi-section position-relative text-white py-5">
        <div class="parallax-background"></div>
        <div class="container position-relative" style="z-index: 2;">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 title-section">I nostri traguardi</h2>

                </div>
            </div>
            <div class="row justify-content-center text-center">
                <div class="col-md-4 mb-4">
                    <div class="card stat-card h-100 bg-gray-700 border-0 text-white">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3 animated-counter text-pantone-arancione" data-target="10000" style="font-size:4rem; font-weight: bold;">0</span>
                            <h3 class="card-title mb-2">Utenti attivi</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card stat-card h-100 bg-gray-700 border-0 text-white">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3 animated-counter" data-target="1500" style="font-size:4rem; color: var(--success); font-weight: bold;">0</span>
                            <h3 class="card-title mb-2">Documenti</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card stat-card h-100 bg-gray-700 border-0 text-white">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3 animated-counter" data-target="1000" style="font-size:4rem; color: var(--warning); font-weight: bold;">0</span>
                            <h3 class="card-title mb-2">Recensioni</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const counters = document.querySelectorAll('.animated-counter');
        const section = document.getElementById('home-traguardi');
        let started = false;

        const updateCount = (counter) => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = Math.floor(Math.random() * (target / 100)) + 1; // Incremento randomico

            if (count < target) {
                counter.innerText = Math.floor(count + increment);
                setTimeout(() => updateCount(counter), 10); // Aumenta il tempo per un effetto più lento
            } else {
                counter.innerText = target + '+'; // Aggiunge "oltre" al termine del counter
            }
        };

        const handleScroll = () => {
            const sectionTop = section.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;

            if (!started && sectionTop < windowHeight) {
                counters.forEach(counter => {
                    counter.innerText = '0'; // Inizializza il contatore a zero
                    updateCount(counter);
                });
                started = true;
                window.removeEventListener('scroll', handleScroll); // Rimuove l'evento dopo l'animazione
            }
        };

        window.addEventListener('scroll', handleScroll);
    </script>


    <!-- Sezione Informazioni -->
    <!--
    <section id="home-informazioni" class="info-section py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-12 mb-4">
                    <h2 class="display-4 title-section">Scopri di più</h2>
                    <p class="lead">Esplora le nostre offerte e scopri come possiamo aiutarti.</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--primary);">
                                <i class="bi bi-info-circle"></i>
                            </span>
                            <h3 class="card-title mb-2">Informazioni</h3>
                            <p class="card-text mb-4">Scopri le ultime notizie e gli aggiornamenti sulla nostra piattaforma.</p>
                            <a href="/informazioni" class="btn btn-primary btn-lg mt-auto w-100">Leggi di più</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--secondary);">
                                <i class="bi bi-question-circle"></i>
                            </span>
                            <h3 class="card-title mb-2">FAQ</h3>
                            <p class="card-text mb-4">Trovate le risposte alle domande più frequenti sulla nostra piattaforma.</p>
                            <a href="/faq" class="btn btn-secondary btn-lg mt-auto w-100">Visita la FAQ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card info-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <span class="mb-3" style="font-size:2.5rem; color: var(--accent);">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <h3 class="card-title mb-2">Contatti</h3>
                            <p class="card-text mb-4">Contattaci per qualsiasi domanda o richiesta.</p>
                            <a href="/contatti" class="btn btn-warning btn-lg mt-auto w-100">Contattaci</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    -->
    <!-- Testimonials Section -->
    <section id="dicono-di-noi" class="testimonials-section py-5 bg-light">
        <div class="container">
            <?php get_template_part('template-parts/home/dicono-di-noi'); ?>
            <?php get_template_part('template-parts/home/slider-recensioni'); ?>
        </div>
    </section>
</main>

<?php
get_footer();
