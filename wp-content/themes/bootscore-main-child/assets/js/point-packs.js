
jQuery(document).ready(function ($) {
    $('.btn-error-abbonamento-disattivo').click(function () {
        var link = $(this).data('link-pro-page');
        showCustomAlert('Attenzione', '<p>Non sei ancora un utente Pro? Allora diventalo subito e poi acquista i nostri pacchetti punti.</p> <div class="alert alert-success"> Visita la pagina dedicata ai nostri pacchetti Pro! <a class="btn btn-success" href="' + link + '">Passa a Pro</a></div>', 'bg-warning btn-warning');
    });
});