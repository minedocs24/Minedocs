<!-- Popup Pro -->
<div id="popup-pro" class="popup-overlay">
    <div class="popup-content zoom">
        <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/documento/popup/razzo.svg" alt="Icona razzo" class="rocket-icon">
        <h2>Piano <span class="pro">Pro</span></h2>
        <p>Diventa uno studente Pro e accedi a risorse didattiche esclusive e a molto altro!</p>
        <a href="<?php echo get_permalink( get_page_by_path( 'pacchetti-premium')->ID) ?>" id="passa-pro" class="popup-button btn btn-primary mt-auto btn-custom ">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/documento/popup/doc_logo.svg" style="width: 20px; height: 20px; margin-right: 8px;">    
            Passa a Pro
        </a>
    </div>
</div>