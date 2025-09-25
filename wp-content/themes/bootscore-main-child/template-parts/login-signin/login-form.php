<div id="login-fields" class="login-section">
    <div class="form-group mb-3">
        <input type="text" name="log" id="login-email-field" class="form-control" value="" size="20"
            autocapitalize="off" aria-describedby="access-form" placeholder="Inserisci la tua mail">
        <small id="login-email-error" class="form-text text-danger" style="display: none;">Questo campo è obbligatorio.</small>
    </div>
    <div class="form-group mb-3">
        <div class="input-group">
            <input type="password" name="pwd" id="login-password-field" class="form-control" value="" size="20"
                aria-describedby="access-form" placeholder="Inserisci la tua password">
            <button id="login-showpwd" type="button" class="btn btn-outline-secondary toggle-password" data-target="login-password-field">
                <i class="fas fa-eye"></i>
            </button>
        </div>            
        <small id="login-password-error" class="form-text text-danger" style="display: none;">Questo campo è obbligatorio.</small>
    </div>
    <div id="login-captcha" class="mb-3" hidden>
        <?php 
            if (defined('ABILITA_CAPTCHA') && ABILITA_CAPTCHA) {
                aggiungi_recaptcha_al_form("login"); 
            }
        ?>
    </div>
    <div id="remember-forgot" class="row variable-gutters mb-3">
        <div class="col-lg-6">
            <div class="form-check">
                <input name="rememberme" type="checkbox" id="rememberme" value="forever" class="form-check-input" />
                <label for="rememberme" class="form-check-label">
                    <?php esc_html_e('Remember Me'); ?>
                </label>
            </div>
        </div>
        <div class="col-lg-6 text-lg-end">
            <a href="<?php echo esc_url(home_url('/reset-password')); ?>" class="text-decoration-none"
                aria-label="<?php _e('Lost your password?'); ?>">
                <?php _e('Lost your password?'); ?>
            </a>
        </div>
    </div>

    <div id="register-link" class="text-center mt-4">
        <p>Non hai un account? <button type="button" id="show-register-fields" class="btn btn-link"
                onclick="showRegisterSection()">Registrati</button></p>
    </div>
    <div id="login-button" class="row variable-gutters justify-content-center">
        <button type="submit"
            class="btn btn-primary w-75 mb-3 d-flex align-items-center justify-content-center mx-auto"
            name="login" value="Accedi">
            <div id="icon-loading-login" class="btn-loader mx-2" hidden style="display: inline-block;">
                <span class="spinner-border spinner-border-sm"></span>
            </div>
            <?php _e("Accedi", "minedocs"); ?>
        </button>
    </div>
</div>
