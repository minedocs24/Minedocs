<?php


$file_id =  $args['anteprima_att_id'];

$download_url = create_safe_download_link($file_id);

$pdf_path = $download_url;


?>

<!--<a href="<?php echo $download_url; ?>"><?php echo $download_url ?></a>-->
<div class="card border">
  

<div id="controls" class="btn-group btn-group-toggle" data-toggle="buttons">
  <button class="btn btn-secondary" id="zoomOut">Zoom -</button>
  <button class="btn btn-secondary" id="zoomIn">Zoom +</button>



</div>



<div id="pdfContainer" style="width: 100%; height: 800px; overflow: auto; ">
    <div id="pdfViewerDiv"></div>
    <?php get_template_part("template-parts/single-document/popup-passa-a-pro", null, null); ?>
</div>

</div>
<script>

    

    var pdfPath = "<?php echo esc_url($pdf_path); ?>"; // Passa il percorso del PDF a una variabile JavaScript
</script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
<script src="<?php  echo get_stylesheet_directory_uri(  ); ?>/assets/js/pdfviewer.js"></script>
