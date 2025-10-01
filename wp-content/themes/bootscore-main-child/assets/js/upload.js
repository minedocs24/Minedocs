let isFileUploaded = false;
let isCheckboxChecked = false;

function handleDrop(event, callback) {
    event.preventDefault();
    const fileInput = document.getElementById('fileInput');
    fileInput.files = event.dataTransfer.files;
    handleFile(event.dataTransfer.files[0], callback);
}

function handleFile(file, callback) {
    //console.log(file);
    if (!file) {
        //console.log('Nessun file selezionato');   
        
        return;
    }
    const box_choose_file = document.getElementById('uploadSection');
    // const msgSuccess = document.getElementById('msg-success-upload');
    //const msgError = document.getElementById('msg-error-upload');
    //const msgAttesa = document.getElementById('msg-file-analysis');
    //get element by class
    //const msgAttesa = document.querySelector('.msg-file-analysis');
    
    //con jQuery prendi tutti gli oggetti con la classe msg-file-analysis
    const msgAttesa = jQuery('.msg-file-analysis');
    const msgError = jQuery('.msg-error-upload');
    const msgSuccess = jQuery('.msg-success-upload');
    
    msgError.addClass('d-none');
    
    msgSuccess.addClass('d-none');
    console.log(msgSuccess);
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileName').classList.remove('d-none');
    document.getElementById('progressBar').classList.remove('d-none');
    const progressBar = document.querySelector('.progress-bar');

    box_choose_file.style.display = 'none';

    check_fields();

    const formData = new FormData();
    formData.append('file', file);
    formData.append('action', 'upload_and_control_file_ajax');
    formData.append('nonce', env_upload.nonce_file_upload);

    // imposta il massimo timeout a 5 minuti
    const xhr = new XMLHttpRequest();
    xhr.open('POST', callback, true);

    xhr.timeout = 60000;

    //console.log("Inizio caricamento...");
    progressBar.classList.add('progress-bar-striped', 'progress-bar-animated');

    xhr.ontimeout = function() {
        //console.log('La richiesta è scaduta');
        msgError.removeClass('d-none');
        if (callback) callback(new Error('La richiesta è scaduta'));
    };

    xhr.upload.addEventListener('progress', function(e) {
        const percent = (e.loaded / e.total) * 100;
        progressBar.style.width = percent + '%';
        progressBar.innerHTML = '<strong>' + percent.toFixed(2) + '%</strong>';
        if (percent == 100) {
            msgAttesa.css('display', 'block');
            isFileUploaded = true;
            checkEnableButton();
        }
    });

    xhr.onload = function() {
        msgAttesa.css('display', 'none');
        
        if (xhr.status === 200) {
            //console.log(xhr.responseText);
            const response = JSON.parse(xhr.responseText);
            console.log(response);
            if (response.success) {
                //console.log("Caricamento completato");

                // Inserisci la risposta in un cookie
                document.cookie = "responseUpload=" + JSON.stringify(response['data']['file_id']);
                document.cookie = "n_pages=" + JSON.stringify(response['data']['n_pages']);
                document.cookie = "preview=" + JSON.stringify(response['data']['preview_file_id']);
                
                //console.log(document.cookie);

                //-----------CARICAMENTO ANTEPRIMA----------------

                const canvas = document.getElementById('pdfCanvas');

                var file = document.getElementById('fileInput').files[0];
                
                file.arrayBuffer().then(function(pdfData) {

                    const pdf = pdfjsLib.getDocument({data: pdfData});
                    pdf.promise.then(function(pdf) {
                        pdf.getPage(1).then(function(page) {
                            const scale = 1.5;
                            const viewport = page.getViewport({scale: scale});
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            page.render(renderContext).promise.then(function() {
                                canvas.toBlob(function(blob) {
                                    const webpUrl = URL.createObjectURL(blob);
                                    //console.log('Generated WebP URL:', webpUrl);
                                    // You can use the webpUrl as needed
                                }, 'image/webp');
                            });
                        });
                    });
                });

                //-----------FINE CARICAMENTO ANTEPRIMA----------------
                
                // Comunicare alla pagina Descrivi che il caricamento è avvenuto con successo e che si può procedere
                check_fields();

                // msgSuccess.css({'display': 'block', 'font-weight': 'bold', 'color': '#28a745'});
                msgSuccess.removeClass('d-none');
                msgSuccess.text("✅ File caricato con successo, puoi procedere!");

                  // Call next step function when upload is complete

                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
            } else {
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
                box_choose_file.style.display = 'block';
                msgError.removeClass('d-none');
                if (response.data.message.includes("Unprocessable document")) {
                    msgError.text("Errore durante il caricamento: Assicurati che il tuo file non sia protetto con una password. Non possiamo sapere cosa ci sia al suo interno! In alternativa, ti invitiamo a riprovare");
                } else {
                    msgError.text("Errore durante il caricamento: " + response.data.message);
                }
                //msgError.textContent = "Errore durante il caricamento: " + response.data.message;
            }
        } else if (xhr.status === 413) {
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            box_choose_file.style.display = 'block';
            msgError.removeClass('d-none');
            msgError.text("Errore durante il caricamento: File troppo grande. Il file non deve superare i 10MB");
            //msgError.textContent = "Errore durante il caricamento: File troppo grande. Il file non deve superare i 20MB";
        }
        
        else {
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            box_choose_file.style.display = 'block';
            msgError.removeClass('d-none');
            msgError.text("Errore durante il caricamento. Assicurati che il tuo file rispetti i requisiti richiesti e che non sia protetto da password");
        }
    };
    
    xhr.onerror = function() {
        showCustomAlert('Errore', 'Errore di rete durante il caricamento del file', 'bg-danger btn-danger');
    }

    try {
     xhr.send(formData);
    } catch (error) {
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        box_choose_file.style.display = 'block';
        msgError.removeClass('d-none');
        msgError.text("Errore durante il caricamento. Assicurati che il tuo file rispetti i requisiti richiesti e che non sia protetto da password");
    }

}

function toggleProceedButton() {
    var checkBox = document.getElementById('termsConditionsCheck');
    if (checkBox.checked) {
        isCheckboxChecked = checkBox.checked;
    } else {
        isCheckboxChecked = false;
    }
    checkEnableButton();
}

function checkEnableButton() {
    if (isFileUploaded && isCheckboxChecked) {
        enable_btn_go_to_section2();
    } else {
        disable_btn_go_to_section2();
    }
}

function enable_btn_go_to_section2() {
    jQuery('#btn-go-to-section2').prop('disabled', false);
    jQuery('#btn-go-to-section2').removeClass('button-custom-disabled');
}

function disable_btn_go_to_section2() {
    jQuery('#btn-go-to-section2').prop('disabled', true);
    jQuery('#btn-go-to-section2').addClass('button-custom-disabled');
}

function enable_btn_go_to_section3() {

    jQuery('#btn-go-to-section3').prop('disabled', false);
    jQuery('#btn-go-to-section3').removeClass('button-custom-disabled');

}

function disable_btn_go_to_section3() {
    jQuery('#btn-go-to-section3').prop('disabled', true);
    jQuery('#btn-go-to-section3').addClass('button-custom-disabled');
}

// Mostra la sezione 1 (upload del file) se viene cliccato il pulsante "Carica nuovo file" in caso di modifica
function show_section1() {

    // Nasconde i pulsanti di scelta con una transizione
    const buttonSection = document.getElementById('buttonSection');
    buttonSection.classList.add('slide-out');
    
    // Mostra il template con una transizione
    const template = document.getElementById('uploadTemplate');
    template.style.display = 'block';
    
    // Imposta un leggero delay per avviare la transizione
    setTimeout(function() {
        buttonSection.style.display = 'none'; // Nasconde completamente i pulsanti una volta fuori scena
        template.classList.add('visible');
    }, 300); // Timeout per il tempo dell'animazione di uscita dei pulsanti
}

function go_to_section2() {

    pill1Section = jQuery('#pill1');
    pill2Section = jQuery('#pill2');
    pill1Tab = jQuery('#pill1-tab');
    pill2Tab = jQuery('#pill2-tab');

    pill1Section.removeClass('active').removeClass('show');
    pill2Section.addClass('active').addClass('show');
    pill1Tab.removeClass('active');
    pill2Tab.addClass('active');
    
}

function go_to_section3() {
    
    pill2Section = jQuery('#pill2');
    pill3Section = jQuery('#pill3');
    pill2Tab = jQuery('#pill2-tab');
    pill3Tab = jQuery('#pill3-tab');

    pill2Section.removeClass('active').removeClass('show');
    pill3Section.addClass('active').addClass('show');
    pill2Tab.removeClass('active');
    pill3Tab.addClass('active');
}


function check_fields() {
    $campoUniversita = jQuery('#nome_istituto_select');
    $campoCorso = jQuery('#nome_corso_select');
    $campoCorsoDiLaurea = jQuery('#nome_corso_di_laurea_select');
    $campoTitolo = jQuery('#titolo');
    $campoDescrizione = jQuery('#descrizione');
    $campoDocumento = jQuery('#documento');
    $annoAccademico = jQuery('#anno');
    $modalita = jQuery('input[name="modalita"]:checked').val();
    $prezzo = jQuery('#prezzo');
    var responseUpload = getCookie('responseUpload');
    var uploadChoice = getCookie('uploadChoice');
    jQuery('#btn-go-to-section3').prop('tooltip');
    $invalidTitle = !/^[a-zA-Z0-9\s\-()àèéìòùÀÈÉÌÒÙ']+$/.test($campoTitolo.val());
    $invalidDescription = !/^[a-zA-Z0-9\s.,;:!?\'"()\-àèéìòùÀÈÉÌÒÙ]+$/.test($campoDescrizione.val());
    $invalidUniversita = !/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ\-]+(?<!\-|\s)$/.test($campoUniversita.val());
    $invalidCorso = !/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ\-]+(?<!\-|\s)$/.test($campoCorso.val());
    $invalidCorsoDiLaurea = !/^(?!.*--)[a-zA-Z0-9\sàèéìòùÀÈÉÌÒÙ\-]+(?<!\-|\s)$/.test($campoCorsoDiLaurea.val());
    $invalidTitleField = jQuery('#invalid-title-msg');
    $invalidDescriptionField = jQuery('#invalid-description-msg');
    $invalidUniversitaField = jQuery('#invalid-universita-msg');
    $invalidCorsoField = jQuery('#invalid-corso-msg');
    $invalidCorsoDiLaureaField = jQuery('#invalid-corso-di-laurea-msg');

    // console.log("Modalità:", $modalita);
    // console.log("Prezzo:", $prezzo.val());
    
    if (uploadChoice === 'modify') {
        // Controlla se ci sono modifiche rispetto ai valori iniziali        
        let hasChanges = false;

        for (let field in initialFieldValues) {
            let currentValue = field === 'modalita'
                ? jQuery(`input[name="modalita"]:checked`).val()
                : jQuery(`#${field}`).val();

            if (currentValue !== initialFieldValues[field]) {
                hasChanges = true;
                break;
            }
        }

        if (hasChanges) {
            // Check su tutti i campi 
            if ( $campoUniversita.val() == '' || $campoCorso.val() == '' || $campoCorsoDiLaurea.val() == '' || 
            $campoTitolo.val() == '' || $campoDescrizione.val() == '' || $campoDocumento.val() == '' || $annoAccademico.val() == '' || 
            $modalita == undefined || ($modalita == 'vendi' && $prezzo.val() == null) || $invalidTitle || $invalidDescription || 
            $invalidUniversita || $invalidCorso || $invalidCorsoDiLaurea) {

            var message = "Per favore compila i seguenti campi obbligatori: ";
            if ($campoUniversita.val() == '') message += "Scuola o Università, ";
            if ($campoCorso.val() == '') message += "Materia, ";
            if ($campoCorsoDiLaurea.val() == '') message += "Corso di Laurea, ";
            if ($campoTitolo.val() == '') message += "Titolo, ";
            if ($campoDescrizione.val() == '') message += "Descrizione, ";
            if ($campoDocumento.val() == '') message += "Tipo di Documento, ";
            if ($annoAccademico.val() == '') message += "Anno Accademico, ";
            if ($modalita == undefined) message += "Modalità di pubblicazione, ";
            if ($modalita == 'vendi' && $prezzo.val() == null) message += "Prezzo, ";
            if ($campoTitolo.val() !== '' && $invalidTitle) {
                message += "Titolo non valido, ";
                $invalidTitleField.removeClass('d-none');
            } else{
                $invalidTitleField.addClass('d-none');
            }
            if ($campoDescrizione.val() !== '' && $invalidDescription) {
                message += "Descrizione non valida, ";
                $invalidDescriptionField.removeClass('d-none');
            } else {
                $invalidDescriptionField.addClass('d-none');
            }
            if ($campoUniversita.val() !== '' && $invalidUniversita) {
                message += "Nome Università non valido, ";
                $invalidUniversitaField.removeClass('d-none');
                $invalidUniversitaField.addClass('d-block');
            } else {
                $invalidUniversitaField.addClass('d-none');
            }
            if ($campoCorso.val() !== '' && $invalidCorso) {
                message += "Nome Corso non valido, ";
                $invalidCorsoField.removeClass('d-none');
                $invalidCorsoField.addClass('d-block');
            } else {
                $invalidCorsoField.addClass('d-none');
            }
            if ($campoCorsoDiLaurea.val() !== '' && $invalidCorsoDiLaurea) {
                message += "Nome Corso di Laurea non valido, ";
                $invalidCorsoDiLaureaField.removeClass('d-none');
                $invalidCorsoDiLaureaField.addClass('d-block');
            } else {
                $invalidCorsoDiLaureaField.addClass('d-none');
            }

            // Rimuove l'ultima virgola e aggiunge un punto finale
            message = message.replace(/, $/, '.');
            

            // Mostra il messaggio all'utente
            updateTooltip(message);

            disable_btn_go_to_section3();

            } else {
                updateTooltip("");
                enable_btn_go_to_section3();
            }
        } else {
            disable_btn_go_to_section3();
        }
        return; // Esci dalla funzione per evitare ulteriori controlli

    }
    else if ( $campoUniversita.val() == '' || $campoCorso.val() == '' || responseUpload == null || $campoCorsoDiLaurea.val() == '' || 
            $campoTitolo.val() == '' || $campoDescrizione.val() == '' || $campoDocumento.val() == '' || $annoAccademico.val() == '' || 
            $modalita == undefined || ($modalita == 'vendi' && $prezzo.val() == null) || $invalidTitle || $invalidDescription || 
            $invalidUniversita || $invalidCorso || $invalidCorsoDiLaurea) {

        var message = "Per favore compila i seguenti campi obbligatori: ";
        if ($campoUniversita.val() == '') message += "Scuola o Università, ";
        if ($campoCorso.val() == '') message += "Materia, ";
        if ($campoCorsoDiLaurea.val() == '') message += "Corso di Laurea, ";
        if ($campoTitolo.val() == '') message += "Titolo, ";
        if ($campoDescrizione.val() == '') message += "Descrizione, ";
        if ($campoDocumento.val() == '') message += "Tipo di Documento, ";
        if (responseUpload == null) message += "File da caricare, ";
        if ($annoAccademico.val() == '') message += "Anno Accademico, ";
        if ($modalita == undefined) message += "Modalità di pubblicazione, ";
        if ($modalita == 'vendi' && $prezzo.val() == null) message += "Prezzo, ";
        if ($campoTitolo.val() !== '' && $invalidTitle) {
            message += "Titolo non valido, ";
            $invalidTitleField.removeClass('d-none');
        } else{
            $invalidTitleField.addClass('d-none');
        }
        if ($campoDescrizione.val() !== '' && $invalidDescription) {
            message += "Descrizione non valida, ";
            $invalidDescriptionField.removeClass('d-none');
        } else {
            $invalidDescriptionField.addClass('d-none');
        }
        if ($campoUniversita.val() !== '' && $invalidUniversita) {
            message += "Nome Università non valido, ";
            $invalidUniversitaField.removeClass('d-none');
            $invalidUniversitaField.addClass('d-block');
        } else {
            $invalidUniversitaField.addClass('d-none');
        }
        if ($campoCorso.val() !== '' && $invalidCorso) {
            message += "Nome Corso non valido, ";
            $invalidCorsoField.removeClass('d-none');
            $invalidCorsoField.addClass('d-block');
        } else {
            $invalidCorsoField.addClass('d-none');
        }
        if ($campoCorsoDiLaurea.val() !== '' && $invalidCorsoDiLaurea) {
            message += "Nome Corso di Laurea non valido, ";
            $invalidCorsoDiLaureaField.removeClass('d-none');
            $invalidCorsoDiLaureaField.addClass('d-block');
        } else {
            $invalidCorsoDiLaureaField.addClass('d-none');
        }

        // Rimuove l'ultima virgola e aggiunge un punto finale
        message = message.replace(/, $/, '.');
        

        // Mostra il messaggio all'utente
        updateTooltip(message);

        disable_btn_go_to_section3();

    } else {
        updateTooltip("");
        enable_btn_go_to_section3();
    }
}

function updateTooltip(message) {

    const btnConferma = document.getElementById('btn-go-to-section3');
    if (message == "") {
        btnConferma.removeAttribute('tooltip');
    }
    else {
        btnConferma.setAttribute('tooltip', message);
    }
}


function go_to_homepage() {
    window.location.href = "/wp1/";
}

//la funzione restart deve mostrare un popup che richiede all'utente se è sicuro di voler ricominciare da capo e se conferma deve ricaricare la pagina
function restart() {
    // Crea il modal
    const modalHtml = `
        <div class="modal fade" id="restartModal" tabindex="-1" aria-labelledby="restartModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="restartModalLabel">Ricominciare da capo?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Vuoi ricominciare da capo caricando un nuovo documento o tornare alla homepage?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Torna alla Homepage</button>
                        <button type="button" class="btn btn-primary" id="restartButton">Ricominciare da capo</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Aggiungi il modal al body
    jQuery('body').append(modalHtml);

    // Mostra il modal
    const restartModal = new bootstrap.Modal(document.getElementById('restartModal'));
    restartModal.show();

    // Gestisci il click sul pulsante "Ricominciare da capo"
    jQuery('#restartButton').on('click', function() {
        location.reload();
    });

    // Gestisci il click sul pulsante "Torna alla Homepage"
    jQuery('.btn-secondary').on('click', function() {
        window.location.href = env_upload.home_url;
    });
}

// Funzione per impostare la scelta nel cookie dell'upload del file aggiornato
function setUploadChoice(choice) {
    document.cookie = `uploadChoice=${choice}; path=/; max-age=3600`; // Valido per 1 ora
}

// Funzione per recuperare la scelta dai cookie dell'upload del file aggiornato
function getUploadChoice() {
    const matches = document.cookie.match(/(?:^|; )uploadChoice=([^;]*)/);
    return matches ? decodeURIComponent(matches[1]) : null;
}

function submitForm(postId, callback) {
    // Disabilita il pulsante di submit
    jQuery('#btn-go-to-section3').prop('disabled', true);
    jQuery('#btn-go-to-section3').addClass('button-custom-disabled');

    // Collect form data
    var titolo = document.getElementById('titolo').value;
    var descrizione = document.getElementById('descrizione').value;
    var universita = document.getElementById('nome_istituto_select').value;
    var corsoDiLaurea = document.getElementById('nome_corso_di_laurea_select').value;
    var materia = document.getElementById('nome_corso_select').value;
    var annoAccademico = document.getElementById('anno').value;
    var tipoDocumento = document.getElementById('documento').value;
    var modalita = document.querySelector('input[name="modalita"]:checked').value;
    var prezzo = document.getElementById('prezzo').value;
    // Recupera il cookie responseUpload
    var responseUpload = getCookie('responseUpload');
    var n_pages = getCookie('n_pages');
    var preview = getCookie('preview');
    var uploadChoice = postId !== null ? getCookie('uploadChoice') : 'upload';
    var thumbnail = document.getElementById('pdfCanvas').toDataURL('image/webp', 1.0);

    // Assegna la funzione ajax corretta in base alla scelta dell'utente
    var nonce = "";
    if (postId !== null) {
        if (uploadChoice === 'uploadNewVersion') {
            ajaxAction = 'upload_document';
            nonce = env_upload.nonce_upload;
        } else if (uploadChoice === 'modify') {
            ajaxAction = 'update_document';
            nonce = env_upload.nonce_update;
        } else {
            console.error('Valore non valido per uploadChoice:', uploadChoice);
        }
    } else {
        ajaxAction = 'upload_document';
        nonce = env_upload.nonce_upload;
    }

    const msgSubmitting = document.getElementById('msg-submitting');
    msgSubmitting.classList.remove('d-none');
    msgSubmitting.classList.add('d-block');
    

    // Elimina i cookie impostando una data nel passato
    deleteCookie('responseUpload');
    deleteCookie('n_pages');
    deleteCookie('preview');
    deleteCookie('uploadChoice');

    // Fai una chiamata ajax per inviare i dati al server
    jQuery.ajax({
        url: callback,
        type: 'POST',
        data: {
            action: ajaxAction,
            post_id: postId,
            uploadChoice: uploadChoice,
            titolo: titolo,
            descrizione: descrizione,
            universita: universita,
            corsoDiLaurea: corsoDiLaurea,
            materia: materia,
            annoAccademico: annoAccademico,
            tipoDocumento: tipoDocumento,
            modalita: modalita,
            prezzo: prezzo,
            file_id: responseUpload,
            n_pages: n_pages,
            preview_file_id: preview,
            thumbnail: thumbnail,
            nonce: nonce
        },
        success: function(response) {
            console.log(response);
            if (response.success) {
                // Se la richiesta è andata a buon fine Vai alla sezione 3
                go_to_section3();

                msgSubmitting.style.display = 'none';
            }
            else {
                // Se la richiesta non è andata a buon fine mostra un messaggio di errore
                msgSubmitting.style.display = 'none';
                jQuery('#btn-go-to-section3').prop('disabled', false);
                jQuery('#btn-go-to-section3').removeClass('button-custom-disabled');
                showCustomAlert('Errore', 'Errore durante l\'aggiornamento del file: ' + response.data.message, 'bg-danger btn-danger');
            }
        },
        error: function(response) {
            //console.log(response);
            msgSubmitting.style.display = 'none';
            // Riabilita il pulsante di submit
            jQuery('#btn-go-to-section3').prop('disabled', false);
            jQuery('#btn-go-to-section3').removeClass('button-custom-disabled');
        }
    });
}

// Funzione che mostra il popup con le info riguardanti le modalità di pubblicazione del documento
function showInfoPopup(event) {
    const popup = document.getElementById('popup-info');
    popup.style.display = 'block';
    popup.style.left = event.pageX + 'px';
    popup.style.top = event.pageY + 'px';
}

// Funzione che nasconde il popup con le info riguardanti le modalità di pubblicazione del documento
function hideInfoPopup() {
    const popup = document.getElementById('popup-info');
    popup.style.display = 'none';
}

function deleteCookie(name) {
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
}

// Salva i valori iniziali dei campi
let initialFieldValues = {};

jQuery(document).ready(function() {
    initialFieldValues = {
        titolo: jQuery('#titolo').val(),
        descrizione: jQuery('#descrizione').val(),
        nome_istituto_select: jQuery('#nome_istituto_select').val(),
        nome_corso_di_laurea_select: jQuery('#nome_corso_di_laurea_select').val(),
        nome_corso_select: jQuery('#nome_corso_select').val(),
        anno: jQuery('#anno').val(),
        documento: jQuery('#documento').val(),
        modalita: jQuery('input[name="modalita"]:checked').val(),
        prezzo: jQuery('#prezzo').val(),
    };

    jQuery('#prezzo').on('change', function() {
        var prezzo = jQuery(this).val();
        // Controlla se il prezzo è un numero valido


        jQuery('#guadagno').val('Calcolo in corso...').addClass('placeholder bg-gray-500');
        jQuery.ajax({
            url: env_upload.ajax_url,
            type: 'POST',
            data: {
                action: 'conversione_punti_in_denaro',
                punti_pro: prezzo
            },
            success: function(response) {
                var formattedPrice = parseFloat(response.data.guadagno_utente).toFixed(2);
                var min = (formattedPrice * 0.85).toFixed(2);
                var max = (formattedPrice * 1.1).toFixed(2);
                jQuery('#guadagno').val('Tra ' + min + ' e ' + max + ' €').removeClass('placeholder bg-gray-500');
                check_fields();
            },
            error: function() {
                jQuery('#guadagno').val('Errore nella conversione').removeClass('placeholder bg-gray-500');
            },
        });
    });

});
