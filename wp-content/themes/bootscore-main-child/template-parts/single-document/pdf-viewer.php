<?php

$pdf_path = $args['pdf_path'];

?>
<div class="pdf-container">
                <!-- Popup documento Passa a Pro -->
                <div id="popup-pro" class="popup-overlay">
                    <div class="popup-content">
                    <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/documento/popup/razzo.svg" alt="Icona razzo" class="rocket-icon">
                        <h2>Piano <span class="pro-text">Pro</span></h2>
                        <p>Non dare limiti alla tua conoscenza, impara con i PuntiPro</p>
                        <!-- Pulsante "Passa a Pro" -->
                        <a href="<?php echo get_permalink( get_page_by_path( 'pacchetti-premium')->ID) ?>" id="passa-pro" class="popup-button btn btn-primary mt-auto btn-custom ">
                        <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/documento/popup/doc_logo.svg" style="width: 20px; height: 20px; margin-right: 8px;">    
                        Passa a Pro</a>
                        
                    </div>
                </div>


                <!-- Colonna centrale con il lettore PDF -->
            
                <div class="card shadow-lg border-0">
                    <div class="card-body">
                        <!-- Lettore PDF -->

                        <!--<embed src= "/wp1/wp-content/uploads/2024/09/Sistemi-Biometrici-SI.pdf#zoom=100&scrollbar=0&toolbar=0&navpanes=0" style="pointer-events: none;" width= "100%" height= "800px">
                        -->
                        <object class="pdf-viewer"
                            data="<?php echo $pdf_path; ?>#zoom=80&scrollbar=0&toolbar=0&navpanes=0"
                            style="pointer-events: none;" type="application/pdf" width="100%" height="1000px">

                        </object>
                        <!--<iframe
                                src="https://docs.google.com/viewer?url=https://10.147.20.4/wp1/wp-content/uploads/2024/09/Sistemi-Biometrici-SI.pdf&embedded=true"
                                width="100%" height="1000px" style="border:none;" class="pdf-viewer"></iframe>-->
                    </div>
                </div>
            </div>