(function($){
    $(document).ready(function(){
        var $btn = $('#btn-change-avatar');
        var $file = $('#input-avatar-file');
        var $img  = $('#user-avatar-img');

        if (!$btn.length || !$file.length || !$img.length || typeof env_user_avatar === 'undefined') {
            return;
        }

        var allowed = ['image/jpeg','image/png','image/webp'];
        var maxBytes = 5 * 1024 * 1024; // 5MB

        var originalBtnHtml = $btn.html();

        $btn.on('click', function(){
            $file.trigger('click');
        });

        $file.on('change', function(){
            var f = this.files && this.files[0];
            if (!f) return;

            if (allowed.indexOf(f.type) === -1) {
                showCustomAlert('Errore', 'Formato non supportato. Usa JPG, PNG, WEBP o GIF.', 'bg-danger btn-danger');
                $file.val('');
                return;
            }

            if (f.size > maxBytes) {
                showCustomAlert('Errore', 'L\'immagine supera i 5MB.', 'bg-danger btn-danger');
                $file.val('');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'upload_user_avatar');
            formData.append('security', env_user_avatar.nonce_upload_avatar);
            formData.append('avatar', f);

            // disabilita bottone e mostra spinner
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Caricamento...');

            $.ajax({
                url: env_user_avatar.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp){
                    if (resp && resp.success && resp.data && resp.data.url) {
                        $img.attr('src', resp.data.url);
                        showCustomAlert('Foto aggiornata con successo', 'La tua foto profilo è stata aggiornata con successo.', 'bg-success btn-success');
                    } else {
                        var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Errore durante l\'upload.';
                        showCustomAlert('Errore', msg, 'bg-danger btn-danger');
                    }
                },
                error: function(){
                    showCustomAlert('Errore', 'Si è verificato un errore di rete.', 'bg-danger btn-danger');
                },
                complete: function(){
                    // ripristina bottone
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    $file.val('');
                }
            });
        });
    });
})(jQuery);


