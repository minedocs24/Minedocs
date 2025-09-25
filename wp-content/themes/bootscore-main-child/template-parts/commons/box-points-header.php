<?php

global $sistemiPunti;

if(is_user_logged_in(  )){
?>

<div  onclick="showPointsModal()" class="text-dark me-2 rounded-5 py-0 px-2 d-flex align-items-center" style="cursor: pointer;">



    <div class="d-flex flex-column mx-1 align-items-start p-0">
        <?php


$sistema = get_sistema_punti('blu');
$icon_blu = $sistema->get_icon();
        ?>

        <div>

            <img src="<?php echo $icon_blu; ?>" alt="icona punti" class="icon" style="width: 14px; height: 14px;">
            <span style="font-size: small;"
                class="show_count_<?php echo $sistema->get_meta_key(); ?>"><?php echo $sistema->ottieni_totale_punti(get_current_user_id());?></span>
        </div>
        <?php

$sistema = get_sistema_punti('pro');
$icon_pro = $sistema->get_icon();
        ?>
        <div style="margin-top: -8px;">
            <img src="<?php echo $icon_pro; ?>" alt="icona punti" class="icon" style="width: 14px; height: 14px;">
            <span style="font-size: small;"
                class="show_count_<?php echo $sistema->get_meta_key(); ?>"><?php echo $sistema->ottieni_totale_punti(get_current_user_id());?></span>
        </div>
        <?php


?>
    </div>
</div>
<?php
}