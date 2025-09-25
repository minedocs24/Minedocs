<?php

    $user_id = get_current_user_id();

    $sistema_blu = get_sistema_punti('blu');
    $sistema_pro = get_sistema_punti('pro');

    $punti_blu = $sistema_blu->ottieni_totale_punti($user_id);
    $punti_pro = $sistema_pro->ottieni_totale_punti($user_id);

    $abbonamento_attivo = is_abbonamento_attivo($user_id);
    $scadenza_abbonamento = get_user_meta($user_id, 'scadenza_abbonamento', true);
    if($scadenza_abbonamento) {
        $scadenza_abbonamento = date('d/m/Y', strtotime($scadenza_abbonamento));
    }
    

?>
<div class="my-4">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="mb-2">I miei punti</h3>

            <!-- Sezione Punti -->
            <div class="row text-center">
                <!-- Punti Blu -->
                <div class="col-6 mb-3 mb-md-0">
                    <div class="d-flex flex-column align-items-center">

                        <h5 class="mb-1">Punti Blu <?php echo $sistema_blu->print_icon(); ?> </h5>

                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center"
                            style="width: 100px; height: 100px;">
                            <strong class="fs-3"><?php echo $punti_blu; ?> </strong>

                        </div>
                    </div>
                </div>

                <!-- Punti Pro -->
                <div class="col-6">
                    <div class="d-flex flex-column align-items-center">
                    <h5 class="mb-1">Punti Pro <?php echo $sistema_pro->print_icon(); ?> </h5>
                    <?php if($abbonamento_attivo) { ?>

                        
                    

                        <div class="rounded-circle bg-danger text-white d-flex justify-content-center align-items-center"
                            style="width: 100px; height: 100px;">
                            <strong class="fs-3"><?php echo $punti_pro; ?> </strong>

                        </div>
                        <div class="text-center mt-2">
                            <span class="text-muted">Scadenza abbonamento: <?php echo $scadenza_abbonamento; ?></span>
                    
                        </div>
                        <div class="text-center mt-2">
                            <button class="btn btn-outline-danger btn-sm" onclick="window.location.href = '<?php echo PACCHETTI_PUNTI_PAGE; ?>'">Ho bisogno di punti Pro!</button>
                        </div>

                        
                    <?php } else { ?>
                        <!-- Invito all'acquisto per Pro -->
                        <div class="alert alert-info mt-3 text-center">
                            <i class="bi bi-lightning-fill text-warning fs-5"></i>
                            <span class="ms-2">Non hai un abbonamento Pro attivo. <button onclick="window.location.href = '<?php echo PIANI_PRO_PAGE; ?>'" class="btn btn-warning btn-sm me-2">Attivalo ora!</button></span>

                        </div>

                    <?php } ?>
                    </div>
                </div>

                <!-- Pulsanti generali -->
                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-outline-secondary btn-sm" onclick="showPointsModal()">Qual è la differenza?</button>
                </div>
            </div>
        </div>
    </div>