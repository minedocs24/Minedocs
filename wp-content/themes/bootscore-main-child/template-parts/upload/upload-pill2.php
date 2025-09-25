<?php global $post_id; 
global $post_hid;
?>
<div class="upload-section-description">
    <?php get_template_part( 'template-parts/upload/sezione-messaggi-analisi-upload' ); ?>

    <div class="section-description">
        <div class="text-center py-2">
            <h2>Informazioni <span class="green-text">generali</span></h2>
        </div>

        <label for="universita" class="bold-text"><i class="icon-university"></i> Scuola o Università</label>
        <?php get_template_part( 'template-parts/upload/campo-universita', null, array('onchange' => 'check_fields()') ); ?>

        <?php get_template_part( 'template-parts/upload/campo-corso-di-laurea', null, array('onchange' => 'check_fields()') ); ?>

        <?php get_template_part( 'template-parts/upload/campo-materia', null, array('onchange' => 'check_fields()') ); ?>

    </div>

    <hr class="divider">

    <div class="section-description">
        <div class="text-center py-2">
            <h2>Qualche <span class="text-pantone-arancione">dettaglio</span></h2>
        </div>
        <?php get_template_part( 'template-parts/upload/campo-anno-accademico' ); ?>
        <?php get_template_part( 'template-parts/upload/campo-tipo-documento' ); ?>
        <div class="form-group">
            <label for="titolo" class="bold-text">
                <i class="icon-title"></i> Scegli un titolo:
            </label>
            <?php
           // $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;
            $current_titolo = $post_id ? get_the_title($post_id) : ''; 
            ?>
            <input type="text" id="titolo" class="form-control custom-input" placeholder="Inserisci titolo" value="<?php echo esc_attr($current_titolo); ?>" onchange="check_fields()">
            <div id="invalid-title-msg" class="alert alert-danger mt-2 d-none">
                Il titolo può contenere solo i seguenti caratteri speciali: -)('
            </div>
        </div>

        <div class="form-group">
            <label for="descrizione" class="bold-text">
                <i class="icon-description"></i> Descrizione:
            </label>
            <?php
            $current_descrizione = $post_id ? get_the_excerpt($post_id) : ''; 
            if (empty($current_descrizione)) {
                $current_descrizione = '';
            }
            ?>
            <textarea id="descrizione" rows="4" class="form-control custom-input" placeholder="Inserisci descrizione" minlength="10" onchange="check_fields()"><?php echo esc_textarea($current_descrizione); ?></textarea>
            <div id="invalid-description-msg" class="alert alert-danger mt-2 d-none">
                La descrizione può contenere solo i seguenti caratteri speciali: .,;:!?\'"-)(
            </div>
        </div>

        <?php get_template_part( 'template-parts/upload/campo-modalita-pubblicazione' ); ?>
    </div>
</div>

<div class="progress-section my-4">
    <div id="msg-submitting" class="alert alert-warning my-2 d-none">
        <p class="fw-bold">
            <div class="btn-loader mx-2 d-inline-block">
                <span class="spinner-border spinner-border-sm text-dark"></span>
                <span class="text-dark">Attendi, stiamo caricando il tuo documento su Minedocs. Qualche secondo...</span>
            </div>
        </p>
    </div>

    <div class="my-2">
        <div class="alert alert-success d-none" role="alert" id="msg-success-upload">
            Success
        </div>
        <div class="alert alert-danger d-none" role="alert" id="msg-error-upload">
            Error
        </div>
    </div>
</div>

<div class="d-flex justify-content-around mb-5">
    <button id="restart" class="button-custom button-custom-blue" onclick="restart()">Ricomincia</button>

    <?php if ($post_id) : ?>
        <button disabled id="btn-go-to-section3" class="button-custom button-custom-blue button-custom-disabled" onclick="submitForm('<?php echo $post_hid; ?>', '<?php echo admin_url('admin-ajax.php'); ?>')">Conferma</button>
    <?php else : ?>
        <button disabled id="btn-go-to-section3" class="button-custom button-custom-blue button-custom-disabled" onclick="submitForm(null, '<?php echo admin_url('admin-ajax.php'); ?>')">Conferma</button>
    <?php endif; ?>
</div>
