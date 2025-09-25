
function update_points_in_page(){

    
jQuery(document).ready(function($) {

    $.ajax({
        url: env_update_points.ajaxurl, // Usa l'url localizzato
        type: 'POST',
        data: {
            action: 'get_updated_points',
            nonce: env_update_points.nonce_UpdatePoints
        },
        success: function(response) {
            if (response.success) {
                var dati = response.data;
                Object.keys(dati).forEach(key => {
                    $(".show_count_"+key).html(dati[key]);
                });
            } else {
                // Mostra il popup di errore
                showCustomAlert('Errore', 'Errore: ' + response.data.message, 'bg-danger btn-danger');
            }
        },
        error: function(xhr, status, error) {
            showCustomAlert('Errore', 'Si è verificato un errore durante la richiesta: ' + error, 'bg-danger btn-danger');
        }
    });
});

}

update_points_in_page()
