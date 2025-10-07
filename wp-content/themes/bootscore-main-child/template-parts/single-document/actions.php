<?php
//$post = $args['post'];
//$product = $args['product'];
//$product_info = $args['product_info'];
$costo = $args['costo'];
$gia_acquistato = $args['gia_acquistato'];
$stato_approvazione = $args['stato_approvazione'];
?>

<div class="actions mt-4 mb-2 row">
    <div>
        <div class="d-flex flex-wrap justify-content-start align-items-center">

            <div class="flex-grow-0 me-1">
            <?php $post_hid = get_product_hash_id($post->ID); ?>
                <button id="downloadButton" data-product-id="<?php echo $post_hid; ?>"
                    class="product_type_simple btn btn-primary btn-lg mt-auto bottone-personalizzato bottone-personalizzato-blu px-4 ">

                    <p class="fw-bold mb-0">
                    <div id="icon-loading-download" class="btn-loader mx-2" hidden>
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                    <img id="img-cloud-download"
                        src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/file/cloud_download_white.svg"
                        style="width: 30px; height: 30px; margin-right: 8px;">
                    Scarica

                    <!-- <?php echo $post->post_author == get_current_user_id() ? "il tuo documento" : "il documento"; ?> -->
                    
                    <?php if((!FREE_FOR_ALREADY_PURCHASED || !$gia_acquistato) && $post->post_author != get_current_user_id()) {

                        ?>
                    per <?php echo " ".$costo['text']; ?>
                    <!--<img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/search/<?php echo $costo['icon']; ?>" class="icon-feature">-->
                    <?php } ?>
                    </p>
                </button>
                <?php if($gia_acquistato) { ?>
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/common/green-tick-check.svg" alt="Già acquistato" style="width: 40px; height: 40px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Hai già acquistato questo documento">
                <?php } ?>

            </div>
        </div>
    </div>
    <div>

        <div class="d-flex flex-wrap align-items-center">
            <?php if ($post->post_author == get_current_user_id()) { ?>
                <?php $post_hid = get_product_hash_id($post->ID); ?>
                <div class="flex-grow-0 me-1">
                    <a id="editButton" href="#" data-id="<?php echo $post_hid; ?>" data-status="<?php echo esc_attr($stato_approvazione); ?>" 
                        class="btn-actions btn-edit" title="Modifica">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/matita.png"
                            alt="Modifica" style="width: 15px; height: 15px;" />
                    </a>
                </div>

                <div class="flex-grow-0 me-1">
                    <button id="deleteButton"
                        class="btn-actions delete-btn"
                        onclick="showDeleteModal('<?php echo $post_hid; ?>')" title="Elimina">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/trash.png"
                            alt="Elimina" style="width: 15px; height: 15px;" />
                    </button>
                </div>

                <?php } ?>
            
                <!-- Pulsante AI prominente -->
            <?php if ($gia_acquistato || $post->post_author == get_current_user_id()): ?>
            <div class="flex-grow-0 me-3">
                <button id="aiButton" class="btn btn-primary btn-ai-prominent" title="Studia con AI" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-robot me-2"></i>
                    <span class="d-none d-md-inline">Studia con AI</span>
                </button>
                <ul class="dropdown-menu ai-dropdown-menu" aria-labelledby="aiButton">
                    <li><a class="dropdown-item" href="#" data-ai-action="riassunto" data-product-hid="<?php echo $post_hid; ?>">
                        <i class="fas fa-file-alt me-2"></i>Riassunto
                    </a></li>
                    <li><a class="dropdown-item" href="#" data-ai-action="mappa" data-product-hid="<?php echo $post_hid; ?>">
                        <i class="fas fa-project-diagram me-2"></i>Mappa concettuale
                    </a></li>
                    <li><a class="dropdown-item" href="#" data-ai-action="quiz" data-product-hid="<?php echo $post_hid; ?>">
                        <i class="fas fa-question-circle me-2"></i>Quiz
                    </a></li>
                    <li><a class="dropdown-item dropdown-item-disabled" href="#" data-ai-action="evidenza" data-product-hid="<?php echo $post_hid; ?>" onclick="return false;">
                        <i class="fas fa-highlighter me-2"></i>Evidenzia
                        <span class="badge bg-secondary ms-2">Coming Soon</span>
                    </a></li>
                    <li><a class="dropdown-item dropdown-item-disabled" href="#" data-ai-action="interroga" data-product-hid="<?php echo $post_hid; ?>" onclick="return false;">
                        <i class="fas fa-comments me-2"></i>Interroga il documento
                        <span class="badge bg-secondary ms-2">Coming Soon</span>
                    </a></li>
                </ul>
            </div>
            <?php else: ?>
            <div class="flex-grow-0 me-3">
                <button id="aiButton" class="btn btn-secondary btn-ai-disabled" title="Studia con AI - Acquista prima il documento" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="fas fa-robot me-2"></i>
                    <span class="d-none d-md-inline">Studia con AI</span>
                    <span class="badge bg-warning ms-2">Acquista prima</span>
                </button>
            </div>
            <?php endif; ?>

            <div class="flex-grow-0 me-1">
                <button id="shareButton" class="btn-actions btn-link-table" title="Condividi">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/file/share.png"
                        alt="Condividi" style="width: 15px; height: 15px;" />
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Popup per la condivisione dei file -->
<div id="sharePopup" class="share-popup" style="display: none;">
    <div class="popup-content">
        <span id="closePopup" class="close-popup">&times;</span>
        <h3>Condividi su:</h3>
        <div class="social-buttons d-flex align-items-center justify-content-center">
            <a href="#" id="facebookShare" class="social-btn facebook">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/file/facebook.png" alt="Facebook"
                    class="social-icon-facebook">
            </a>
            <a href="#" id="whatsappWebShare" class="social-btn whatsapp">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/file/whatsapp.webp" alt="WhatsApp"
                    class="social-icon-whatsapp">
            </a>
        </div>
    </div>
</div>

<!-- Modal per avviso modifica -->
<?php get_template_part('modals/confirm_edit_document');
get_template_part('modals/prohibit_edit_document');
?>

<!-- Stile per il tasto condividi e il popup di condivisione -->
<style>
.bottone-personalizzato {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    width: auto;
    min-height: 50px !important;
    max-width: 100%;
    margin-top: 5px !important;
    font-weight: 700;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Aggiunta ombra leggera */
}

.bottone-personalizzato-blu {
    background-color: #007bff;
}

.bottone-personalizzato:hover {
    background-color: #0056b3 !important;
}

.bottone-personalizzato-rosso {
    background-color: rgb(220, 53, 69) !important;
    border: 1px solid rgb(220, 53, 69) !important;
}



.bottone-personalizzato-verde {
    background-color: rgb(0, 255, 0) !important;
    border: 1px solid rgb(0, 255, 0) !important;
}




.actions {
    display: flex;
    align-items: center;
    /* Centra verticalmente */
    justify-content: start;
    /* Allinea a sinistra */
    gap: 15px;
    /* Spazio tra i pulsanti */
}

.back-link {
    display: inline-block;
    margin-top: 8px;
    align-items: center;
    /* Allinea verticalmente */
    text-decoration: none;
    font-size: 18px;
    color: rgb(0, 0, 0);
    cursor: pointer;
    position: relative;
    /* Necessario per il posizionamento di ::after */
    overflow: hidden;
}

.back-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    /* Posiziona il punto di partenza al centro */
    width: 0;
    /* Inizia con larghezza zero */
    height: 1px;
    /* Altezza della linea */
    background-color: rgb(0, 0, 0);
    /* Colore della linea */
    transition: all 0.2s ease;
    /* Transizione per l'animazione */
    transform: translateX(-50%);
    /* Centra la linea */
}

.back-link:hover::after {
    width: 100%;
    /* Espande la linea fino ai bordi del link */
}

/* Stile del popup */
.share-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.popup-content {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    width: 300px;
    text-align: center;
    position: relative;
}

.close-popup {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 18px;
    cursor: pointer;
}

.social-buttons {
    display: flex;
    /* Allinea i pulsanti orizzontalmente */
    gap: 15px;
    /* Spazio tra i pulsanti */
    margin-top: 20px;
}

.social-btn {
    display: flex;
    /* Usa Flexbox per allineare l'icona */
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    /* Spaziatura interna */
    border-radius: 10px;
    /* Angoli arrotondati */
    cursor: pointer;
    /* Cambia il cursore in puntatore */
    transition: background-color 0.3s ease;
    /* Transizione per l'hover */
}

.social-btn img.social-icon-whatsapp {
    width: 20px;
    height: 20px;
}

.social-btn img.social-icon-facebook {
    width: 12px;
    height: 20px;
}

.social-btn.facebook {
    background-color: #3b5998;
    /* Colore di Facebook */
}

.social-btn.facebook:hover {
    background-color: #2d4373;
    /* Colore hover di Facebook */
}

.social-btn.whatsapp {
    background-color: #25d366;
    /* Colore di WhatsApp */
}

.social-btn.whatsapp:hover {
    background-color: #1eb152;
    /* Colore hover di WhatsApp */
}

.share-popup .popup-content {
    text-align: center;
    /* Centra il contenuto */
}

/* Stili per il pulsante AI prominente */
.btn-ai-prominent {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 25px;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 14px;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.btn-ai-prominent:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    color: white;
}

.btn-ai-prominent:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-ai-prominent i {
    font-size: 16px;
    animation: aiGlow 2s ease-in-out infinite alternate;
}

/* Effetto di attenzione per il pulsante AI */
.btn-ai-prominent::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57);
    border-radius: 27px;
    z-index: -1;
    animation: borderGlow 3s linear infinite;
    opacity: 0.7;
}

@keyframes borderGlow {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Effetto glow per l'icona AI */
@keyframes aiGlow {
    0% { text-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
    100% { text-shadow: 0 0 15px rgba(255, 255, 255, 0.8), 0 0 25px rgba(255, 255, 255, 0.4); }
}

/* Stili per il pulsante AI originale (mantenuto per compatibilità) */
.btn-ai {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-ai:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Stili per il pulsante AI disabilitato */
.btn-ai-disabled {
    background: #6c757d !important;
    border: none;
    border-radius: 25px;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 14px;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
    position: relative;
    overflow: hidden;
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-ai-disabled:hover {
    background: #5a6268 !important;
    transform: none;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
    color: white;
}

.btn-ai-disabled:active {
    transform: none;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
}

.btn-ai-disabled i {
    font-size: 16px;
    animation: none;
}

.btn-ai-disabled::before {
    display: none;
}

.ai-dropdown-menu {
    min-width: 220px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border: none;
    padding: 8px 0;
    margin-top: 8px;
}

.ai-dropdown-menu .dropdown-item {
    padding: 12px 20px;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    font-weight: 500;
}

.ai-dropdown-menu .dropdown-item:hover {
    background-color: #f8f9fa;
    border-left-color: #667eea;
    transform: translateX(5px);
}

.ai-dropdown-menu .dropdown-item i {
    width: 18px;
    text-align: center;
    font-size: 14px;
}

/* Stili per le voci disabilitate */
.ai-dropdown-menu .dropdown-item-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    color: #6c757d !important;
}

.ai-dropdown-menu .dropdown-item-disabled:hover {
    background-color: transparent !important;
    border-left-color: transparent !important;
    transform: none !important;
}

.ai-dropdown-menu .dropdown-item-disabled .badge {
    font-size: 0.7em;
    padding: 2px 6px;
}

/* Animazione per il pulsante AI */
@keyframes aiPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-ai:hover i {
    animation: aiPulse 1s infinite;
}
</style>


<!-- Script per il click sul tasto di condivisione -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const shareData = {
        title: "Minedocs",
        text: "Ciao! 😊 Questo documento potrebbe tornarti utile:",
        url: window.location.href,
    };

    const btn = document.getElementById("shareButton");
    //const resultPara = document.querySelector(".result");

    // Share must be triggered by "user activation"
    btn.addEventListener("click", async () => {
        try {
            await navigator.share(shareData);
            //resultPara.textContent = "MDN shared successfully";
        } catch (err) {
            //resultPara.textContent = `Error: ${err}`;
        }
    });
});

/*const shareButton = document.getElementById('shareButton');
const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent); // Rilevamento dispositivo mobile
const url = window.location.href; // URL della pagina corrente
const text = "Ciao! 😊 Questo documento potrebbe tornarti utile:";

shareButton.addEventListener('click', () => {
    if (isMobile) {
        // Condivisione tramite API Web Share su mobile
        navigator.share({
            title: "Condivisione documento",
            text: text,
            url: url,
        })
        .then(() => console.log("Condivisione completata."))
        .catch((error) => console.error("Errore nella condivisione:", error));
    } else {
        // Mostra popup per desktop
        document.getElementById('sharePopup').style.display = 'flex';
    }
});

// Chiudi il popup
document.getElementById('closePopup').addEventListener('click', () => {
    document.getElementById('sharePopup').style.display = 'none';
});

// Azioni per i pulsanti dei social (Desktop)
document.getElementById('facebookShare').href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
document.getElementById('whatsappWebShare').href = `https://web.whatsapp.com/send?text=${encodeURIComponent(text)}%20${encodeURIComponent(url)}`;

// Chiudi il popup cliccando fuori
window.addEventListener('click', (event) => {
    const popup = document.getElementById('sharePopup');
    if (event.target === popup) {
        popup.style.display = 'none';
    }
});*/


</script>

<!-- Script per il pulsante AI -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Lista delle azioni disponibili
    const availableActions = ['riassunto', 'mappa', 'quiz'];
    
    // Controlla se l'utente ha acquistato il documento
    const aiButton = document.getElementById('aiButton');
    const isDisabled = aiButton && aiButton.classList.contains('btn-ai-disabled');
    
    // Gestione click sui pulsanti AI
    document.querySelectorAll('[data-ai-action]').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Se il pulsante AI è disabilitato, mostra messaggio di acquisto
            if (isDisabled) {
                if (typeof showCustomAlert !== 'undefined') {
                    showCustomAlert("Acquista il documento", "Devi prima acquistare questo documento per utilizzare le funzioni AI!", 'bg-warning btn-warning');
                } else {
                    alert("Devi prima acquistare questo documento per utilizzare le funzioni AI!");
                }
                return;
            }
            
            const action = this.getAttribute('data-ai-action');
            const productHid = this.getAttribute('data-product-hid');
            
            // Controlla se l'azione è disponibile
            if (!availableActions.includes(action)) {
                // Mostra messaggio per funzionalità non disponibile
                if (typeof showCustomAlert !== 'undefined') {
                    showCustomAlert("Funzionalità in sviluppo", "Questa funzionalità sarà disponibile presto!", 'bg-info btn-info');
                } else {
                    alert("Questa funzionalità sarà disponibile presto!");
                }
                return;
            }
            
            // Reindirizza alla pagina studia-con-ai con i parametri
            const url = new URL('<?php echo home_url('/studia-con-ai/'); ?>', window.location.origin);
            url.searchParams.set('document_id', productHid);
            url.searchParams.set('action', action);
            
            window.location.href = url.toString();
        });
    });
    
    // Gestione click sul pulsante AI disabilitato
    if (isDisabled && aiButton) {
        aiButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof showCustomAlert !== 'undefined') {
                showCustomAlert("Acquista il documento", "Devi prima acquistare questo documento per utilizzare le funzioni AI!", 'bg-warning btn-warning');
            } else {
                alert("Devi prima acquistare questo documento per utilizzare le funzioni AI!");
            }
        });
    }
});
</script>