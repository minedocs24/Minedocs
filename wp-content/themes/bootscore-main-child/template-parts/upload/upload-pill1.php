<div id="uploadSection" class="upload-section" 
     ondrop="handleDrop(event, '<?php echo admin_url('admin-ajax.php'); ?>')" 
     ondragover="event.preventDefault()">
    <img fetchpriority="high" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/upload_logo.svg" alt="Upload logo">
    <p class="text-large">Trascina qui il tuo file</p>
    <p class="text-small">Oppure</p>
    <button class="button-custom button-custom-blue" onclick="document.getElementById('fileInput').click()">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/add.svg" class="add-icon">
        Scegli tra i file
    </button>
    <input type="file" id="fileInput" class="d-none" 
           onchange="handleFile(this.files[0], '<?php echo admin_url('admin-ajax.php'); ?>')">
    <p class="text-limit-section">Limite massimo 10 MB per file. Formati accettati: pdf</p>
</div>

<div class="progress-section my-4">
    <p id="fileName" class="file-name d-none">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/upload/tick.png" alt="Success" class="me-2"> 
        <span class="filename-text">Filename</span>
    </p>
    <div class="progress" id="progressBar">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
             style="width: 0%;"></div>
    </div>
    <?php get_template_part('template-parts/upload/sezione-messaggi-analisi-upload'); ?>
</div>

<div class="form-check my-3 text-center d-flex justify-content-center align-items-center">
    <input class="form-check-input" type="checkbox" id="termsConditionsCheck" required onchange="toggleProceedButton()">
    <label class="form-check-label ms-2" for="termsConditionsCheck">
        Caricando il file, confermi di avere i diritti d'autore o l'autorizzazione dei titolari. Leggi i <a href='termini-e-condizioni'>termini e condizioni</a>.
    </label>
</div>

<input type="hidden" name="uploadedFile" id="uploadedFile">
<canvas hidden id="pdfCanvas"></canvas>

<div class="d-flex justify-content-around">
    <button id="restart" class="button-custom button-custom-blue" onclick="restart()">Ricomincia</button>
    <button disabled id="btn-go-to-section2" class="button-custom button-custom-blue button-custom-disabled"
            onclick="go_to_section2()">Procedi</button>
</div>
