<?php
/**
 * Template Name: Reset Password
 */

get_header();

// Verifica se sono presenti i parametri key e login
$key = isset($_GET['key']) ? $_GET['key'] : '';
$login = isset($_GET['login']) ? $_GET['login'] : '';

if (!empty($key) && !empty($login)) {
    // Modalità reset password
    $user = check_password_reset_key($key, $login);
    if (is_wp_error($user)) {
        $error_message = 'Il link di reset password non è valido o è scaduto.';
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($key) && !empty($login)): ?>
                        <?php if (isset($error_message)): ?>
                            <div id="alert-container" class="alert alert-danger"><?php echo esc_html($error_message); ?></div>
                        <?php else: ?>
                            <h2 class="card-title text-center mb-4">Reimposta Password</h2>
                            <div id="alert-container"></div>
                            <!-- Requisiti minimi di complessità della password -->
                            <div class="alert alert-info">
                                <strong>Requisiti minimi di complessità:</strong>
                                <ul>
                                    <li>- Almeno 8 caratteri</li>
                                    <li>- Almeno una lettera maiuscola</li>
                                    <li>- Almeno un numero</li>
                                    <li>- Almeno un carattere speciale</li>
                                    <li>- Lunghezza massima 20 caratteri</li>
                                </ul>
                            </div>
                            
                            <form id="new-password-form" method="post">
                                <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
                                <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nuova Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Conferma Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Reimposta Password</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <h2 class="card-title text-center mb-4">Recupero Password</h2>
                        <div id="alert-container"></div>
                        <form id="reset-password-form" method="post">
                            <div class="mb-3">
                                <label for="user_email" class="form-label">Inserisci di seguito l'email con cui ti sei registrato per richiedere il reset della password.</label>
                                <input type="email" class="form-control" id="user_email" name="user_email" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Invia Richiesta</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Gestione form richiesta reset
    $('#reset-password-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'handle_password_reset',
                email: $('#user_email').val(),
                nonce: '<?php echo wp_create_nonce('password_reset_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#alert-container').html(
                        '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        response.data.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>'
                    );
                    $('#reset-password-form')[0].reset();
                } else {
                    $('#alert-container').html(
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        response.data.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>'
                    );
                }
            }
        });
    });

    // Gestione form nuova password
    $('#new-password-form').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#new_password').val() !== $('#confirm_password').val()) {
            $('#alert-container').html(
                '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                'Le password non coincidono' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>'
            );
            return;
        }

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'handle_new_password',
                key: $('input[name="key"]').val(),
                login: $('input[name="login"]').val(),
                new_password: $('#new_password').val(),
                nonce: '<?php echo wp_create_nonce('new_password_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#alert-container').html(
                        '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        response.data.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>'
                    );
                    setTimeout(function() {
                        window.location.href = '<?php echo home_url('/login'); ?>';
                    }, 2000);
                } else {
                    $('#alert-container').html(
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        response.data.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>'
                    );
                }
            }
        });
    });
});
</script>

<?php
get_footer(); 