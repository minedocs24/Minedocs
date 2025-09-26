<?php
    $free_plan_info = [
        'nome' => 'MineDocs Free',
        'vantaggi' => [
            '<img src="https://test.minedocs.it/test1/wp-content/themes/bootscore-main-child/assets/img/registrazione/Icone_Minedocs_V.svg" alt="Icona V" class="img-fluid" style="width: 35px; height: 35px;"> Accedi solo ai documenti condivisi',
            '<img src="https://test.minedocs.it/test1/wp-content/themes/bootscore-main-child/assets/img/registrazione/Icone_Minedocs_X.svg" alt="Icona X" class="img-fluid" style="width: 35px; height: 35px;"> Accedi ai documenti Pro',
            '<img src="https://test.minedocs.it/test1/wp-content/themes/bootscore-main-child/assets/img/registrazione/Icone_Minedocs_X.svg" alt="Icona X" class="img-fluid" style="width: 35px; height: 35px;"> Punti Pro',
            '<img src="https://test.minedocs.it/test1/wp-content/themes/bootscore-main-child/assets/img/registrazione/Icone_Minedocs_X.svg" alt="Icona X" class="img-fluid" style="width: 35px; height: 35px;"> Studia con AI*'
        ]
    ];

?>

<div class="registration-section" id="section-3">
    <div class="card shadow-sm border-0 mb-4 no-hover">
        <div class="card-body p-4">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold mb-3">Benvenut* a bordo!</h2>
                <p class="lead ">Corri a scaricare tutto il materiale di cui hai bisogno</p>
            </div>

            <div class="row g-4">
                <!-- Piano Free -->
                <div class="col-md-4 order-2 order-md-1 ">
                    <div class="card h-100 border-0 shadow-sm hover-card no-hover">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h4 class="card-title mb-0">
                                    MineDocs <strong class="text-danger">Free</strong>
                                </h4>
                            </div>
                            <hr class="my-4">
                            <div class="text-center mb-4">
                                <p class="card-text ">Prosegui senza i vantaggi di uno studio smart</p>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <?php foreach ($free_plan_info['vantaggi'] as $vantaggio) { ?>
                                    <li class="mb-3 d-flex align-items-center">
                                        <?php echo $vantaggio; ?>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="text-center">
                                <button id="free-plan-choose-button" class="btn btn-danger w-100 mb-2" onclick="register()">
                                    <div id="icon-loading-free-signin" class="btn-loader mx-2" hidden style="display: inline-block;">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </div>    
                                    <span>Continua con il piano gratuito</span>
                                </button>
                                <!--<p class=" small mb-0">Rinuncia ai vantaggi</p>-->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Piano Pro -->
                <div class="col-md-8 order-1 order-md-2">
                    <div class="card h-100 border-0 shadow-sm hover-card no-hover">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h4 class="card-title mb-0">
                                    MineDocs <strong class="pro">Pro</strong>
                                </h4>
                            </div>
                            <hr class="my-4">
                            <div class="text-center mb-4">
                                <p class="card-text">Approfitta di tutti i vantaggi del piano Pro:</p>
                                <ul class="list-unstyled">
                                    <li><img src="https://test.minedocs.it/test1/wp-content/themes/bootscore-main-child/assets/img/registrazione/Icone_Minedocs_V.svg" alt="Icona V" class="img-fluid" style="width: 35px; height: 35px;"> Punti pro per acquistare documenti migliori</li>
                                    <li><img src="https://test.minedocs.it/test1/wp-content/themes/bootscore-main-child/assets/img/registrazione/Icone_Minedocs_V.svg" alt="Icona V" class="img-fluid" style="width: 35px; height: 35px;"> Studia con AI per un apprendimento personalizzato*</li>
                                    <li><img src="https://test.minedocs.it/test1/wp-content/themes/bootscore-main-child/assets/img/registrazione/Icone_Minedocs_V.svg" alt="Icona V" class="img-fluid" style="width: 35px; height: 35px;"> Genera quiz per testare le tue conoscenze*</li>
                                </ul>
                            </div>
                            
                            <div class="row justify-content-around flex-wrap gap-3 mb-4">
                                <?php get_template_part('template-parts/registrazione-utente/plans');?>
                            </div>

                            <div class="text-center">
                                <button id="subscribe-plan-choose-button" class="btn btn-success w-100" onclick="register()">
                                    <div id="icon-loading-subscribe-now" class="btn-loader mx-2" hidden style="display: inline-block;">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </div>
                                    <span>Abbonati ora</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <p class="small"></p>
            </div>
        </div>
        <div class="text-center">
                <p class="small">*Funzionalità disponibili a breve</p>
            </div>
    </div>
</div>
