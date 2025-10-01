<?php
/*
Template Name: Nuovo Profilo Utente
*/

get_header(); 

$class_padding_admin = current_user_can('administrator') ? 'pt-5' : '';
?>

<?php

$menu_data = get_dati_menu_profilo_utente();
?>

<div class="  <?php echo $class_padding_admin; ?>">
    <div class="row">
        <div class="col-md-4 col-lg-3">
            <button class="navbar-toggler d-md-none" type="button" data-toggle="collapse" data-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse d-md-block" id="navbarMenu">
                <?php get_template_part('template-parts/nuovo-profilo-utente/sidebar', null, ['menu_data' => $menu_data]); ?>
            </div>
        </div>
        <div id="content-section" class="col-md-8 col-lg-9">
            <?php
             $contenuti_caricati = array();
            
            ?>
            
            <?php /* foreach ($menu_data as $sezione) {
                
                foreach ($sezione['voci'] as $menu_item) {
                    $attivo = isset($menu_item['attivo']) ? $menu_item['attivo'] : true;
                    $slug = isset($menu_item['slug']) ? $menu_item['slug'] : '';
                    $default = isset($menu_item['default']) ? $menu_item['default'] : false;
                    $contenuto = isset($menu_item['contenuto']) ? $menu_item['contenuto'] : null;
                    $tipo = isset($menu_item['tipo']) ? $menu_item['tipo'] : null;
                    $display = $default ? 'block' : 'none';

                    if(!$attivo || $tipo != 'contenuto') {
                        continue;
                    }

                    if(in_array($contenuto, $contenuti_caricati)) {
                        continue;
                    } else {
                        $contenuti_caricati[] = $contenuto;
                    }

                    ?>

                    <div id="section-<?php echo $slug; ?>" class="section" style="display: <?php echo $display; ?>">

                        <?php if($tipo === 'contenuto') {// && $default) {
                         
                            get_template_part($contenuto);
                        } ?>

                    </div>

                    <?php
                } 
            } */?>
            


        </div>
    </div>
</div>


<?php get_footer(  ); 