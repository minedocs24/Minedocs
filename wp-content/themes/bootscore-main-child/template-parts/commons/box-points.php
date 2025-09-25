<?php

/**
 * Template part for displaying user points
 */

if (!is_user_logged_in()) {
    return;
}

$sistema_blu = get_sistema_punti('blu');
$icon_blu = $sistema_blu->get_icon();
$punti_blu = $sistema_blu->ottieni_totale_punti(get_current_user_id());

$sistema_pro = get_sistema_punti('pro');
$icon_pro = $sistema_pro->get_icon();
$punti_pro = $sistema_pro->ottieni_totale_punti(get_current_user_id());

?>

<div class="points-box d-flex align-items-center justify-content-around" onclick="showPointsModal();">
    <div class="points-box-left d-flex align-items-center">
        <div class="points-icon me-2">
            <img src="<?php echo $icon_blu; ?>" alt="icona punti blu" class="icon" style="width: 25px; height: 25px;">
        </div>
        <div class="points-info">
            <div class="points-value show_count_punti_blu"><?php echo number_format($punti_blu); ?></div>
            <div class="points-label small text-muted ">Punti Blu </div>
        </div>
    </div>
    <div class="separator" style="border-left: 1px solid #dee2e6; height: 50px; margin: 0 10px;"></div>
    <div class="points-box-right d-flex align-items-center">
        
        <div class="points-icon me-2">
            <img src="<?php echo $icon_pro; ?>" alt="icona punti pro" class="icon" style="width: 25px; height: 25px;">
        </div>
        <div class="points-info">
            <div class="points-value show_count_punti_pro"><?php echo number_format($punti_pro); ?></div>
            <div class="points-label small text-muted">Punti Pro</div>
        </div>
    </div>
</div>