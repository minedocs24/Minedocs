<!-- Modal Bootstrap -->
<div class="modal fade" id="customAlertModal" tabindex="-1" aria-labelledby="customAlertLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Questa classe lo centra verticalmente -->
        <div class="modal-content">
            <div class="modal-header custom-modal-classes">
                <h5 class="modal-title" id="customAlertLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span id="customAlertMessage"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn custom-modal-classes" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


<style>
/* Assicurati che il modale sia al centro della pagina e abbia priorità */
.modal {
    z-index: 1070 !important;
    /* Molto alto per essere davanti a tutto */
}

.modal-backdrop {
    z-index: 1060 !important;
    /* Sfondo scuro dietro al modale */
}
</style>


<script>
function showCustomAlert(title, message, classes) {
    // Imposta il messaggio del modale
    //document.getElementById('customAlertMessage').html("<h1>"+message+"</h1>");

    // Mostra il modale
    //var myModal = new bootstrap.Modal(document.getElementById('customAlertModal'));
    //myModal.show();

    jQuery(document).ready(function($) {
        $('#customAlertLabel').html(title);
        $('#customAlertMessage').html(message);
        $('.custom-modal-classes').removeClass(function(index, className) {
            return (className.match(/(^|\s)(bg|btn)\S+/g) || []).join(' ');
        });
        $('.custom-modal-classes').addClass(classes);
        $('#customAlertModal').modal('show');
    });


}

// Esempio di utilizzo
//showCustomAlert("Titolo", 'Questo è un avviso personalizzato!');
</script>