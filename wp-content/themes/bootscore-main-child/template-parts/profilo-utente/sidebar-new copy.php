<?php

$current_page = $args['current_page'];

?>


<nav id="fixed-sidebar" class="d-md-block bg-light sidebar-profilo">
    <div class="position-sticky">
    <div class="text-center mb-2">
        <a href="<?php echo UPLOAD_PAGE; ?>" class="btn btn-primary upload-btn-table" style="margin-right: 0px;">Carica un documento</a>
    </div>
    <small class="text-muted d-block text-center mb-4" style="font-size: 0.9rem;">Amplia la tua raccolta</small>

    <h6 class="sidebar-heading">Esplora</h6>
    <ul class="nav flex-column mb-3">
    <?php


    ?>

    <li class="nav-item">
            <a href="<?php echo $current_page == 'profilo-utente' ? '#section-profilo' : site_url().'/profilo-utente';  ?>" 
            id="link-profilo" 
            class="nav-link <?php echo $current_page == 'profilo-utente' ? 'active'  : '' ?> " 
            <?php if($current_page == 'profilo-utente') { ?> onclick="goToSection(event, '#section-profilo')" <?php } ?>>
                <i class="fas fa-user"></i> Il mio profilo
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo $current_page == 'profilo-utente' ? '#sezione-miei-documenti' : site_url().'/profilo-utente';  ?>" 
            id="link-studio" 
            class="nav-link "  
            <?php if($current_page == 'profilo-utente') { ?> onclick="goToSection(event, '#sezione-miei-documenti')" <?php } ?>>
                <i class="fas fa-book-open"></i> Il tuo studio
            </a>
        </li>
        <li class="nav-item">

            <a href="<?php echo $current_page == 'documenti-caricati' ? '#' : site_url().'/profilo-utente-documenti-caricati' ;  ?>"
            id="link-documenti" 
            class="nav-link <?php echo $current_page == 'documenti-caricati' ? 'active' : '' ?> " >
            <i class="fas fa-upload"></i> Documenti caricati
        </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo $current_page == 'documenti-acquistati' ? '#' : site_url().'/profilo-utente-documenti-acquistati' ;  ?>"
            id="link-acquistati" 
            class="nav-link <?php echo $current_page == 'documenti-acquistati' ? 'active' : '' ?> " >
            <i class="fas fa-shopping-cart"></i> Documenti acquistati
        </a>
        </li>
        <li class="nav-item">
            
            <a href="<?php echo $current_page == 'guadagni' ? '#' : site_url( ) . '/profilo-utente-guadagni' ?>" 
            id="link-guadagni" 
            class="nav-link <?php echo $current_page == 'guadagni' ? 'active' : ''  ?> " >
            <i class="fas fa-coins"></i> I miei guadagni
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo $current_page == 'movimenti' ? '#' : site_url( ) . '/profilo-utente-movimenti' ?>" 
            id="link-movimenti" 
            class="nav-link <?php echo $current_page == 'movimenti' ? 'active' : ''  ?> " >
            <i class="fas fa-exchange-alt"></i> I miei movimenti
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo site_url( ); ?>/compra-pacchetti-punti" id="link-ricarica" class="nav-link"><i class="fas fa-dollar-sign"></i> Ricarica punti</a>
        </li>
        <li class="nav-item">
            <a href="<?php echo $current_page == 'impostazioni' ? '#' : site_url( ) .'/profilo-utente-impostazioni'; ?>" 
            id="link-impostazioni" 
            class="nav-link <?php echo $current_page == 'impostazioni' ? 'active'  : '' ?> " >
            <i class="fas fa-cog"></i> Impostazioni
        </a>

        </li>
    </ul>
    <!-- <h6 class="sidebar-heading">Raccolta</h6>
    <ul class="nav flex-column mb-4">
        <li class="nav-item">
            <a href="#" class="nav-link"><i class="fas fa-folder"></i> Il mio piano di studio</a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link"><i class="fas fa-folder"></i> I miei documenti</a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" hidden><i class="fas fa-folder"></i> Quiz di esercitazione</a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" hidden><i class="fas fa-folder"></i> I miei libri</a>
        </li>
    </ul> -->
    <?php 
        get_template_part('template-parts/profilo-utente/logout-button');
    ?>
    </div>
</nav>

<style>

.sidebar-profilo {
    height: 100vh !important;
    background-color: #FFFFFF !important;
    padding-top: 20px;
    /* border-right: 1px solid #dee2e6; */
    overflow-y: auto; /* Aggiungi questa riga */
    overflow-x: hidden; /* Aggiungi questa riga per evitare lo scorrimento orizzontale */
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

.sidebar-profilo .btn-primary {
    font-size: 0.9rem;
    padding: 10px;
    border-radius: 20px;
}

.sidebar-profilo small {
    color: #6c757d;
}

.sidebar-heading {
    font-size: 0.85rem;
    font-weight: bold;
    color: #6c757d;
    margin-top: 20px;
    margin-bottom: 10px;
    padding-left: 15px;
}

.sidebar-profilo .nav-link {
    font-size: 0.9rem;
    color: #333;
    padding: 8px 15px;
    transition: background 0.2s, color 0.2s;
    border-radius: 5px;
}

.sidebar-profilo .nav-link i {
    margin-right: 8px;
}

.sidebar-profilo .nav-link:hover,
.sidebar-profilo .nav-link.active {
    background-color: #e9f5ff;
    color: #007bff;
}

</style>

