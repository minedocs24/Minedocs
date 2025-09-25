<div id="register-fields" class="register-section" style="display: none;">
    <div class="form-group mb-3">
        <input type="text" name="first_name" id="register-first-name" class="form-control" value=""
            placeholder="Nome">
        <small id="first-name-error" class="form-text text-danger" style="display: none;">Questo campo è obbligatorio.</small>
    </div>
    <div class="form-group mb-3">
        <input type="text" name="last_name" id="register-last-name" class="form-control" value=""
            placeholder="Cognome">
        <small id="last-name-error" class="form-text text-danger" style="display: none;">Questo campo è obbligatorio.</small>
    </div>
    <div class="form-group mb-3">
        <input type="email" name="email" id="register-email" class="form-control" value=""
            placeholder="Email">
        <small id="email-error" class="form-text text-danger" style="display: none;">Email non valida.</small>
        <small id="duplicated-email-error" class="form-text text-danger" style="display: none;">L'email risulta essere già esistente e registrata.</small>
    </div>
    <div class="form-group mb-3">
        <div class="input-group">
            <input type="password" name="password" id="register-password" class="form-control" value=""
                placeholder="Password">
            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="register-password">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small id="password-error" class="form-text text-danger" style="display: none;">Questo campo è obbligatorio.</small>
        <div id="password-strength-bar" class="progress mt-2" style="display: none;">
            <div id="password-strength-bar-inner" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    <div class="form-group mb-3">
        <div class="input-group">
            <input type="password" name="confirm_pwd" id="register-confirm-password" class="form-control" value=""
                placeholder="Ripeti la password">
            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="register-confirm-password">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small id="confirm-password-error" class="form-text text-danger" style="display: none;">Le password non corrispondono.</small>
    </div>
    <ul class="password-requirements list-unstyled mb-3">
        <li class="requirement" id="length-requirement">
            <span class="status-circle"></span> Almeno 8 caratteri
        </li>
        <li class="requirement" id="uppercase-requirement">
            <span class="status-circle"></span> Almeno 1 lettera maiuscola
        </li>
        <li class="requirement" id="special-requirement">
            <span class="status-circle"></span> Almeno 1 carattere speciale
        </li>
        <li class="requirement" id="number-requirement">
            <span class="status-circle"></span> Almeno 1 numero
        </li>
    </ul>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="accept-privacy-policy" name="accept_privacy_policy">
        <label class="form-check-label privacy-policy-label" for="accept-privacy-policy">
            Ho letto e compreso la <a href="https://www.iubenda.com/privacy-policy/61111609" target="_blank">Privacy Policy</a> e acconsento al trattamento dei miei dati personali.
        </label>
        <small id="privacy-policy-error" class="form-text text-danger" style="display: none;">Devi accettare la Privacy Policy per procedere con la registrazione.</small>
    </div>
    <div id="register-captcha" class="mb-3">
    <?php if(defined('ABILITA_CAPTCHA') && ABILITA_CAPTCHA){ 
        aggiungi_recaptcha_al_form("register"); 
        }?>
    </div>
    <small id="captcha-error" class="form-text text-danger" style="display: none;">Completa il captcha per procedere con la registrazione.</small>
    
    <div id="login-link" class="text-center mt-4">
        <p>Hai già un account? <button type="button" id="show-login-fields" class="btn btn-link" onclick="showLoginSection()">Accedi</button></p>
    </div>
    <div id="register-button" class="row variable-gutters justify-content-center">
        <button type="button" id="register-submit"
            class="btn btn-primary w-75 mb-3 d-flex align-items-center justify-content-center mx-auto">
            <div id="icon-loading-signin" class="btn-loader mx-2" hidden style="display: inline-block;">
                <span class="spinner-border spinner-border-sm"></span>
            </div>
            <span id="register-button-text"><?php _e("Registrati", "minedocs"); ?></span>
        </button>
    </div>
</div>
