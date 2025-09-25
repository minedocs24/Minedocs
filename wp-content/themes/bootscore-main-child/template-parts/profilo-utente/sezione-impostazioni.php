<?php
// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: devi essere loggato per visualizzare i tuoi file.';
    return;
}

$user = wp_get_current_user();
$nome = $user->first_name;
$cognome = $user->last_name;
$telefono = get_user_meta($user->ID, 'billing_phone', true);
$email = $user->user_email;
$istituto = get_user_meta( $user->ID, 'nome_istituto',  true);
$istituto = array_map('intval', (array) $istituto);

$corso_di_laurea = get_user_meta( $user->ID, 'nome_corso_di_laurea',  true);
$corso_di_laurea = array_map('intval', (array) $corso_di_laurea);

$scadenza_abbonamento = get_user_meta($user->ID, 'scadenza_abbonamento', true);
$abbonamento_attivo = is_abbonamento_attivo($user->ID);

$lingua = get_user_meta($user->ID, 'lingua', true);
$nazione = get_user_meta($user->ID, 'nazione', true);

$connesso_google = get_account_google_collegato($user->ID);
if (isset($_GET['chg_pwd']) && $_GET['chg_pwd'] == 'true') : ?>
    <script>
        showCustomAlert("Password cambiata con successo!", "Congratulazioni, la tua password è stata modificata correttamente.","bg-success","btn-success");
    </script>
<?php 
endif;
?>
<main id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class="mt-5">
        <h3 class="my-h3">Impostazioni</h3>
        <hr class="my-hr">
        <div id="user-form" class="row">
            <div class="col-md-6 mb-4">
                <label for="nome" class="form-label field-title">Nome</label>
                <input type="text" class="form-control custom-input" id="nome" placeholder="Nome attuale"
                    value="<?php echo esc_attr($nome); ?>">
            </div>
            <div class="col-md-6 mb-4">
                <label for="cognome" class="form-label field-title">Cognome</label>
                <input type="text" class="form-control custom-input" id="cognome" placeholder="Cognome attuale"
                    value="<?php echo esc_attr($cognome); ?>">
            </div>
            <div class="col-md-6 mb-4">
                <label for="telefono" class="form-label field-title">Numero di Telefono</label>
                <input type="text" class="form-control custom-input" id="telefono"
                    placeholder="Numero di telefono attuale" value="<?php echo esc_attr($telefono); ?>">
            </div>
            <div class="col-md-6 mb-4">
                <label for="mail" class="form-label field-title">Indirizzo Email</label>
                <input type="text" class="form-control custom-input" id="mail" placeholder="Indirizzo email attuale"
                    value="<?php echo esc_attr($email); ?>" disabled>
            </div>
            <div class="col-md-6 mb-4">
                <label for="istituto" class="form-label field-title">Istituto</label>
                <?php get_template_part( 'template-parts/upload/campo-universita', null, array('istituto' => $istituto, 'onchange' => 'enableSubmitButton()') ); ?>
            </div>
            <div class="col-md-6 mb-4">
                <label for="corso-di-laurea" class="form-label field-title">Corso di Laurea</label>
                <?php get_template_part( 'template-parts/upload/campo-corso-di-laurea', null, array('corso-di-laurea' => $corso_di_laurea, 'show_title' => false, 'onchange' => 'enableSubmitButton()') ); ?>
            </div>
            <div class="col-md-6 mb-4">
                <label for="lingua" class="form-label field-title">Lingua</label>
                <?php get_template_part( 'template-parts/profilo-utente/lingue', null, array('lingua' => $lingua)); ?>
            </div>
            <div class="col-md-6 mb-4">
                <label for="nazione" class="form-label field-title">Nazione</label>
                <?php get_template_part( 'template-parts/profilo-utente/nazioni', null, array('nazione' => $nazione)); ?>
            </div>
            <div class="col-12">
                <button id="edit-button"
                    class="btn btn-primary edit-button w-100 mb-3 disabled d-flex align-items-center justify-content-center"
                    onclick="editUserFields()">
                    <div id="icon-loading-download" class="btn-loader mx-2" hidden>
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                    <span>Modifica dati</span>
                </button>
            </div>
        </div>
    </div>
    <?php if (!$connesso_google): ?>
    <div>
        <div class=" mt-5">
            <hr class="my-hr">
            <h3 class="my-h3">Cambio Password</h3>
            <div id="password-change-form" class="row">
                <div class="mb-4">

                    <label for="current_password" class="form-label field-title">Password attuale</label>
                    <div class="input-group">
                        <input type="password" class="form-control custom-input custom-input-eyeble" id="current_password"
                            placeholder="Password attuale">
                        <button type="button" class="btn btn-outline-primary toggle-password custom-input muted-border"
                            data-target="current_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="new_password" class="form-label field-title">Nuova password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="new_password"
                            class="form-control custom-input custom-input-eyeble" value="" placeholder="Nuova password">
                        <button type="button" class="btn btn-outline-primary toggle-password custom-input muted-border"
                            data-target="new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small id="password-error" class="text-danger" style="display: none;">Questo campo è
                        obbligatorio.</small>
                    <div id="password-strength-bar" class="progress" style="display: none;">
                        <div id="password-strength-bar-inner" class="progress-bar" role="progressbar" style="width: 0%;"
                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-4"> 
                    <label for="confirm_password" class="form-label field-title">Conferma nuova password</label>
                    <div class="input-group">
                        <input type="password" name="confirm_pwd" id="confirm_password"
                            class="form-control custom-input custom-input-eyeble" value="" placeholder="Conferma nuova password">
                        <button type="button" class="btn btn-outline-primary toggle-password custom-input muted-border"
                            data-target="confirm_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small id="confirm-password-error" class="text-danger" style="display: none;">Le password non
                        corrispondono.</small>
                </div>
                <button id="change-password-button"
                    class="btn btn-primary edit-button w-100 mb-3 d-flex align-items-center justify-content-center"
                    onclick="changePassword()">
                    <div id="icon-loading-change-password" class="btn-loader mx-2" hidden>
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                    <span>Cambia Password</span>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class=" mt-5">
        <hr class="my-hr">
        <h3 class="my-h3">Account collegati</h3>
        <div class="d-flex flex-column gap-3 mb-5">
            <!--<div class="d-flex align-items-center">
               <button class="btn btn-light custom-button d-flex align-items-center">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/login/logo-apple.svg" alt="Apple"
                        width="20" class="me-2"> AO chi collega Apple
                </button>
                <span class="ms-3 text-success d-flex align-items-center" style="display: none !important;">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/login/connected-account.svg"
                        alt="Apple" width="20" class="me-2"> Connesso
                </span>
            </div>-->
            <?php if (get_account_google_collegato($user->ID)) {
                $tooltip_google = 'tooltip="Account Google collegato. Le opzioni di modifica password sono disabilitate in quanto l\'account è collegato a Google."';
            } else {
                $tooltip_google = 'tooltip="Effettua il login con Google per collegare l\'account (valido solo se l\'Indirizzo Email è lo stesso di quello di Google)"';
            } ?>




            <div class="d-flex align-items-center" id="pulsante-google-collegato">
                <button class="btn btn-light custom-button d-flex align-items-center" <?php echo $tooltip_google; ?> >
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/login/logo-google.svg"
                        alt="Apple" width="20" class="me-2"> Google
    
                </button>
                <span class="ms-3 text-success align-items-center fw-bold <?php echo $connesso_google ? 'd-flex' : 'd-none'; ?>">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/login/connected-account.svg"
                        alt="Apple" width="20" class="me-2"> Connesso
                </span>
            </div>



        </div>
        <hr class="my-hr">
        <h3 id="abbonamento" class="my-h3">Abbonamento</h3>
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="row">
                <div class="col-12 d-flex align-items-center mb-2">
                    <label for="stato_piano" class="form-label field-title mb-0">Stato:</label>
                    <span class="fw-bold ms-2 badge bg-<?php echo $abbonamento_attivo ? 'success' : 'danger'; ?>">
                        <?php echo $abbonamento_attivo ? 'Attivo' : 'Non attivo'; ?>
                    </span>
                </div>
                <?php if ($abbonamento_attivo): ?>
                <div class="col-12 d-flex align-items-center mb-2">
                    <label for="scadenza_piano" class="form-label field-title mb-0">Scade il:</label>
                    <span class="fw-bold ms-2 badge bg-primary">
                        <?php echo $scadenza_abbonamento; ?>
                    </span>
                </div>
                <?php endif; ?>

                <div id="disable-renew"
                    class="col-12 d-flex align-items-center mb-2 <?php echo ha_sottoscrizioni_attive($user->ID) ? 'd-visible' : 'd-none'; ?>">
                    <button class="btn btn-danger delete-button d-flex align-items-center justify-content-center"
                        onclick="disableAutomaticRenew()">
                        <div id="icon-loading-disable-renew" class="btn-loader mx-2" hidden>
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                        Disabilita rinnovo del piano di abbonamento
                    </button>
                </div>

                <div id="reenable-cancel"
                    class="<?php echo ( ha_sottoscrizioni_sospese($user->ID) && !ha_sottoscrizioni_attive($user->ID)) ? 'd-visible' : 'd-none'; ?>">
                    <p class="text-mute">Hai una sottoscrizione sospesa. Puoi riattivarla o cancellarla definitivamente.
                    </p>
                    <div class="col-12 d-flex align-items-center mb-2 ">
                        <button class="enable-button btn btn-success d-flex align-items-center justify-content-center"
                            onclick="enableAutomaticRenew()">
                            <div id="icon-loading-enable-renew" class="btn-loader mx-2" hidden>
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>Riattiva sottoscrizione
                        </button>
                        <button
                            class="delete-button btn btn-danger ms-2 d-flex align-items-center justify-content-center"
                            onclick="cancelAutomaticRenew()">
                            <div id="icon-loading-cancel-renew" class="btn-loader mx-2" hidden>
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>Cancella sottoscrizione
                        </button>
                    </div>
                </div>

                <div id="pro-plan-choose"
                    class="col-12 d-flex align-items-center mb-2 <?php echo (!ha_sottoscrizioni_sospese($user->ID) && !ha_sottoscrizioni_attive($user->ID)) ? 'd-visible' : 'd-none'; ?>">
                    <a href="<?php echo PIANI_PRO_PAGE; ?>"
                        class="btn btn-primary choose-plan-button">Scegli il piano
                        che fa per te</a>
                </div>


            </div>
        </div>
        <hr class="my-hr">
        <h3 class="my-h3">Elimina account</h3>
        <div class="d-flex align-items-center gap-2 mb-3">
            <button id="delete-account-button" class="btn btn-danger delete-button" onclick="openDeleteAccountModal()">Elimina account</button>
            <span>😢</span>
        </div>
        <p class="text-muted">
            Eliminando l'account acconsenti all'eliminazione totale e definitiva dei tuoi dati utente, compresi i tuoi
            documenti, che non saranno più recuperabili in alcuna maniera. <br>
            Non eliminare l'account se non ne sei totalmente sicuro*, lascialo in sospeso, lo eliminerai quando ne avrai
            la certezza!
        </p>
    </div>


</main>

