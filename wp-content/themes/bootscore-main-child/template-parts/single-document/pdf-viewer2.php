<?php
    $file_id =  $args['anteprima_att_id'];
    $download_url = create_safe_download_link($file_id);
    $pdf_path = $download_url;
?>

<!--<a href="<?php echo $download_url; ?>"><?php echo $download_url ?></a>-->
<div id="card-anteprima" class="card border no-hover" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">

    <div id="pdfContainer" style="width: 100%;overflow: auto; ">
        <div id="pdfViewerDiv"></div>
        <?php 
        if( !is_user_logged_in(  ) || !is_abbonamento_attivo(get_current_user_id(  ))){
            get_template_part("template-parts/single-document/popup-passa-a-pro", null, null); 
        } ?>
    </div>

    <div id="controls" class="d-flex justify-content-center gap-2 mt-3 mb-3" data-toggle="buttons">
        <button class="btn btn-primary" id="zoomOut">Zoom -</button>
        <button class="btn btn-primary" id="zoomIn">Zoom +</button>
    </div>
</div>


<script>
    var pdfPath = "<?php echo esc_url($pdf_path); ?>"; // Passa il percorso del PDF a una variabile JavaScript
</script>
<!--<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>-->
<!--<script src="<?php  echo get_stylesheet_directory_uri(  ); ?>/assets/js/pdfviewer.js"></script>-->

