<?php

$current_page = $args['current_page'];

$numero_richieste_fattura = get_number_of_orders_with_invoice_request();

?>

<nav id="fixed-sidebar" class="d-md-block bg-light sidebar-profilo overflow-auto" style="max-height: 100vh;">
    <div class="position-sticky">
        <div class="text-center mb-2">
            <a class="upload-btn-table" href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>">Carica un documento</a>    
        </div>
        <small class="text-muted d-block text-center mb-4">Amplia la tua raccolta</small>

        <h6 class="sidebar-heading">Esplora</h6>
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a href="<?php echo $current_page == 'profilo-utente' ? '#section-profilo' : PROFILO_UTENTE_PAGE; ?>" 
                   id="link-profilo" 
                   class="nav-link <?php echo $current_page == 'profilo-utente' ? 'active' : '' ?>" 
                   <?php if($current_page == 'profilo-utente') { ?> onclick="goToSection(event, '#section-profilo')" <?php } ?>>
                    <i class="fas fa-user"></i> Il mio profilo
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $current_page == 'documenti-caricati' ? '#' : site_url().'/profilo-utente-documenti-caricati'; ?>"
                   id="link-documenti" 
                   class="nav-link <?php echo $current_page == 'documenti-caricati' ? 'active' : '' ?>">
                    <i class="fas fa-upload"></i> Documenti caricati
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $current_page == 'documenti-acquistati' ? '#' : site_url().'/profilo-utente-documenti-acquistati'; ?>"
                   id="link-acquistati" 
                   class="nav-link <?php echo $current_page == 'documenti-acquistati' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Documenti acquistati
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $current_page == 'guadagni' ? '#' : site_url().'/profilo-utente-guadagni'; ?>" 
                   id="link-guadagni" 
                   class="nav-link <?php echo $current_page == 'guadagni' ? 'active' : '' ?>">
                    <i class="fas fa-coins"></i> I miei guadagni
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $current_page == 'movimenti' ? '#' : site_url().'/profilo-utente-movimenti'; ?>" 
                   id="link-movimenti" 
                   class="nav-link <?php echo $current_page == 'movimenti' ? 'active' : '' ?>">
                    <i class="fas fa-exchange-alt"></i> I miei movimenti
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $current_page == 'vendite' ? '#' : site_url().'/profilo-utente-vendite'; ?>" 
                   id="link-vendite" 
                   class="nav-link <?php echo $current_page == 'vendite' ? 'active' : '' ?>">
                    <i class="fas fa-comment-dollar"></i> Le mie vendite
                    <?php if($numero_richieste_fattura > 0) { ?>
                        <span class="badge badge-pill badge-primary"><?php echo $numero_richieste_fattura; ?></span>
                    <?php } ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $current_page == 'generazioni-ai' ? '#' : PROFILO_UTENTE_GENERAZIONI_AI; ?>" 
                   id="link-generazioni-ai" 
                   class="nav-link <?php echo $current_page == 'generazioni-ai' ? 'active' : '' ?>">
                    <i class="fas fa-robot"></i> Le mie generazioni AI
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo site_url(); ?>/compra-pacchetti-punti" id="link-ricarica" class="nav-link">
                    <i class="fas fa-dollar-sign"></i> Ricarica punti
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo $current_page == 'impostazioni' ? '#' : site_url().'/profilo-utente-impostazioni'; ?>" 
                   id="link-impostazioni" 
                   class="nav-link <?php echo $current_page == 'impostazioni' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Impostazioni
                </a>
            </li>
        </ul>
        <?php 
            get_template_part('template-parts/profilo-utente/logout-button');
        ?>
    </div>
</nav>
