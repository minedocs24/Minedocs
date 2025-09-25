
jQuery(document).ready(function($) {
    $('.btn-error-abbonamento-attivo').click(function() {
        var link = $(this).data('link-points-page');
        showCustomAlert('Attenzione', '<p>Hai già un abbonamento attivo, non puoi acquistare un nuovo abbonamento.</p> <div class="alert alert-success"> Hai bisogno di Punti Pro? Visita la pagina <a class="btn btn-success" href="' + link + '">Acquista Punti</a></div>', 'bg-warning btn-warning');
    });
});