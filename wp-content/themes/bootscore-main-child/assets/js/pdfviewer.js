let scale = 3
//let displayScale = 1.750; 
let displayScale = 0.75* window.innerWidth / 1000;
let isDragging = false;
let startX, startY;
/*let pdfDoc = null;

initPDFViewer = ($) => {
    $("#pdfViewerDiv").html("")
    pdfjsLib.getDocument(pdfPath).promise.then(_pdfDoc => {
        //console.log(pdfDoc)
        if (pdfDoc === null) {
            if(_pdfDoc === null){
                //console.log("Errore nel caricamento del PDF")
                return;
            } else {
                pdfDoc = _pdfDoc
            }
        }
        let pages = pdfDoc._pdfInfo.numPages
        for (let i = 1; i <= Math.min(pages,10); i++) {
            pdfDoc.getPage(i).then(page => {
                //console.log(page)
                let pdfCanvas = document.createElement("canvas");
                let context = pdfCanvas.getContext("2d");

                let pageScale = scale // (i === 3) ? 0.5 : scale;

                let pageViewPort = page.getViewport({ scale: pageScale })
                //console.log(pageViewPort)
                pdfCanvas.width = pageViewPort.width
                pdfCanvas.height = pageViewPort.height
                // Imposta la dimensione visibile tramite CSS (mantiene le dimensioni originali)
                pdfCanvas.style.width = (pageViewPort.width / scale * displayScale) + "px";
                pdfCanvas.style.height = (pageViewPort.height / scale * displayScale) + "px";
                pdfCanvas.style.cursor = 'grab'; // Cambia il cursore per il trascinamento

                $("#pdfViewerDiv").append(pdfCanvas)
                page.render({
                    canvasContext: context,
                    viewport: pageViewPort
                }).promise.then(() => {
                    // Aggiungi il testo "VERSIONE GRATUITA" al centro della pagina

                    // Sbiadisci la pagina 3
                    if (i === 3 || i===5 || i===6 || i===8 || i===9) {
                        context.save();
                        context.font = "bold 30px Arial";
                        context.fillStyle = "rgba(0, 0, 0, 0.5)"; // Colore rosso con trasparenza
                        context.textAlign = "center";
                        context.translate(0, 0); // Resetta la trasformazione

                        // Ciclo per riempire la pagina con la parola
                        let text = " Minedocs® - Versione gratuita ";
                        let stepX = 350; // Distanza tra una parola e l'altra sull'asse X
                        let stepY = 100;  // Distanza tra una parola e l'altra sull'asse Y
                        for (let y = 0; y < pdfCanvas.height; y += stepY) {
                            for (let x = 0; x < pdfCanvas.width; x += stepX) {
                                context.save();
                                context.translate(x + stepX / 2, y + stepY / 2); // Posiziona il testo
                                context.rotate(-Math.PI / 8); // Ruota leggermente il testo
                                context.fillText(text, 0, 0); // Disegna il testo
                                context.restore();
                            }
                        }
                        context.restore();
                    }


                    context.save();
                    context.font = "bold 140px Arial";
                    context.fillStyle = "rgba(255, 0, 0, 0.8)"; // Colore rosso con trasparenza
                    context.textAlign = "center";
                    context.translate(pdfCanvas.width / 2, pdfCanvas.height / 2); // Sposta l'origine al centro del canvas
                    context.rotate(-Math.PI / 8); // Ruota di 30 gradi in senso orario
                    context.fillText("VERSIONE GRATUITA", 0, 0); // Disegna il testo
                    context.restore();

                });
            }).catch(pageErr => {
                //console.log(pageErr)
            })
        }
    }).catch(pdfErr => {
        //console.log(pdfErr)
    })
}*/

let pdfDoc = null;

initPDFViewer = ($) => {
    $("#pdfViewerDiv").html("");

    // Mostra lo sfondo grigio, la rotella che gira e il messaggio di caricamento
    $("#loadingOverlay").show();

    //console.logog("inizio caricamento pdf");

    // Se il PDF è già stato caricato, non ricaricarlo
    if (pdfDoc !== null) {
        renderPDFPages($, pdfDoc);
        $("#loadingOverlay").hide(); // Nascondi il messaggio di caricamento
        return;
    }

    pdfjsLib.getDocument(pdfPath).promise.then(_pdfDoc => {
        if (_pdfDoc === null) {
            //console.log("Errore nel caricamento del PDF");
            $("#loadingOverlay").hide(); // Nascondi il messaggio di caricamento
            return;
        }

        pdfDoc = _pdfDoc; // Assegna il PDF alla variabile globale

        renderPDFPages($, pdfDoc); // Chiama la funzione che renderizza le pagine
        //console.logog("fine caricamento pdf");
        $("#loadingOverlay").hide(); // Nascondi il messaggio di caricamento
    }).catch(pdfErr => {
        //console.logog(pdfErr);
        //console.logog("fine caricamento pdf");
        $("#loadingOverlay").hide(); // Nascondi il messaggio di caricamento
    });
};

// Funzione per renderizzare le pagine del PDF
function renderPDFPages_OLD($, pdfDoc) {
    let pages = pdfDoc._pdfInfo.numPages;

    for (let i = 1; i <= Math.min(pages, 10); i++) {
        pdfDoc.getPage(i).then(page => {
            let pdfCanvas = document.createElement("canvas");
            let context = pdfCanvas.getContext("2d");

            let pageScale = scale;
            let pageViewPort = page.getViewport({ scale: pageScale });

            pdfCanvas.width = pageViewPort.width;
            pdfCanvas.height = pageViewPort.height;

            pdfCanvas.style.width = (pageViewPort.width / scale * displayScale) + "px";
            pdfCanvas.style.height = (pageViewPort.height / scale * displayScale) + "px";
            pdfCanvas.style.cursor = 'grab'; // Cambia il cursore per il trascinamento

            $("#pdfViewerDiv").append(pdfCanvas);

            page.render({
                canvasContext: context,
                viewport: pageViewPort
            }).promise.then(() => {
                // Aggiungi il testo "VERSIONE GRATUITA" al centro della pagina
                if (i === 3 || i === 5 || i === 6 || i === 8 || i === 9) {
                    context.save();
                    context.font = "bold 30px Arial";
                    context.fillStyle = "rgba(0, 0, 0, 0.5)";
                    context.textAlign = "center";
                    context.translate(0, 0);

                    let text = " Minedocs® - Versione gratuita ";
                    let stepX = 350;
                    let stepY = 100;

                    for (let y = 0; y < pdfCanvas.height; y += stepY) {
                        for (let x = 0; x < pdfCanvas.width; x += stepX) {
                            context.save();
                            context.translate(x + stepX / 2, y + stepY / 2);
                            context.rotate(-Math.PI / 8);
                            context.fillText(text, 0, 0);
                            context.restore();
                        }
                    }
                    context.restore();
                }

                context.save();
                context.font = "bold 140px Arial";
                context.fillStyle = "rgba(255, 0, 0, 0.8)";
                context.textAlign = "center";
                context.translate(pdfCanvas.width / 2, pdfCanvas.height / 2);
                context.rotate(-Math.PI / 8);
                context.fillText("VERSIONE GRATUITA", 0, 0);
                context.restore();
            });
        }).catch(pageErr => {
            //console.logog(pageErr);
        });
    }
}


function renderPDFPages($, pdfDoc) {
    let tipoDocumento = document.getElementById("tipo_documento").value;
    let pages = pdfDoc._pdfInfo.numPages;

    for (let i = 1; i <= Math.min(pages, 10); i++) {
        pdfDoc.getPage(i).then(page => {
            let pdfCanvas = document.createElement("canvas");
            let context = pdfCanvas.getContext("2d");

            let pageScale = scale;
            let pageViewPort = page.getViewport({ scale: pageScale });

            pdfCanvas.width = pageViewPort.width;
            pdfCanvas.height = pageViewPort.height;

            pdfCanvas.style.width = (pageViewPort.width / scale * displayScale) + "px";
            pdfCanvas.style.height = (pageViewPort.height / scale * displayScale) + "px";
            pdfCanvas.style.cursor = 'grab'; // Cambia il cursore per il trascinamento

            // Allinea il canvas orizzontalmente al centro del div
            pdfCanvas.style.display = 'block';
            pdfCanvas.style.margin = '0 auto';

            $("#pdfViewerDiv").append(pdfCanvas);

            page.render({
                canvasContext: context,
                viewport: pageViewPort
            }).promise.then(() => {
                // Aggiungi il testo "VERSIONE GRATUITA" al centro della pagina
                if (i === 3 || i === 5 || i === 6 || i === 8 || i === 9) {
                    context.save();
                    context.font = "bold 30px Arial";
                    context.fillStyle = "rgba(0, 0, 0, 0.5)";
                    context.textAlign = "center";
                    context.translate(0, 0);

                    let text = " Minedocs® - Versione gratuita ";
                    let stepX = 350;
                    let stepY = 100;

                    for (let y = 0; y < pdfCanvas.height; y += stepY) {
                        for (let x = 0; x < pdfCanvas.width; x += stepX) {
                            context.save();
                            context.translate(x + stepX / 2, y + stepY / 2);
                            context.rotate(-Math.PI / 8);
                            context.fillText(text, 0, 0);
                            context.restore();
                        }
                    }
                    context.restore();
                }

                context.save();
                context.font = "bold 140px Arial";
                context.fillStyle = "rgba(255, 0, 0, 0.8)";
                context.textAlign = "center";
                context.translate(pdfCanvas.width / 2, pdfCanvas.height / 2);
                context.rotate(-Math.PI / 8);
                if (tipoDocumento == "pro") {
                    context.fillText("ANTEPRIMA GRATUITA", 0, 0);
                } else {// documento gratuito
                    context.fillText("ANTEPRIMA", 0, 0);
                }
                context.restore();
            });
        }).catch(pageErr => {
            //console.logog(pageErr);
        });
    }

}



jQuery(function ($) {

    //initPDFViewer($);
})


// Funzione per aggiornare la scala e ridisegnare il PDF
const redrawPDF = ($) => {
    $("#pdfViewerDiv").html(""); // Cancella il PDF attuale
    initPDFViewer($); // Ricarica il PDF con la nuova scala
}

jQuery(function ($) {

   

    // Crea l'overlay di caricamento
    $("#pdfContainer").append(`
        <div id="loadingOverlay" style="
            display: none;
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            text-align: center;
            color: black;
            font-size: 20px;
            padding-top: 20%;
        ">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div>Caricamento anteprima in corso...</div>
        </div>
    `);






    initPDFViewer($);


    // Zoom in
    $("#zoomIn").on("click", function () {
        displayScale += 0.2; // Incrementa la scala
        redrawPDF($);
    });

    // Zoom out
    $("#zoomOut").on("click", function () {
        displayScale = Math.max(displayScale - 0.2, 0.2); // Riduci la scala (minimo 0.2)
        redrawPDF($);
    });

    // Gestione del trascinamento
    $("#pdfViewerDiv").on("mousedown", function (e) {

        isDragging = true;
        startX = e.pageX - $(this).offset().left; // posizione iniziale X
        startY = e.pageY - $(this).offset().top; // posizione iniziale Y
        $(this).css('cursor', 'grabbing'); // Cambia il cursore
    });

    $(document).on("mouseup", function () {
        isDragging = false;
        $("#pdfViewerDiv").css('cursor', 'grab'); // Ripristina il cursore
    });

    $(document).on("mousemove", function (e) {
        if (isDragging) {
            let x = e.pageX - $("#pdfViewerDiv").offset().left;
            let y = e.pageY - $("#pdfViewerDiv").offset().top;
            let dx = x - startX;
            let dy = y - startY;
            $("#pdfViewerDiv").scrollLeft($("#pdfViewerDiv").scrollLeft() - dx);
            $("#pdfViewerDiv").scrollTop($("#pdfViewerDiv").scrollTop() - dy);
        }
    });
});


