/*function updateProgressIndicator(currentSection) {
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        if (index === currentSection - 1) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

document.getElementById('next-button').addEventListener('click', function () {
    document.getElementById('section-1').classList.add('d-none');
    document.getElementById('section-2').classList.remove('d-none');
    updateProgressIndicator(2);
});

document.getElementById('next-button-2').addEventListener('click', function () {
    document.getElementById('section-2').classList.add('d-none');
    document.getElementById('section-3').classList.remove('d-none');
    updateProgressIndicator(3);
});*/ 
let currentStep = 0;

document.addEventListener("DOMContentLoaded", function () {
    showStep(currentStep);

    // Quando l'utente termina di digitare il nickname
    jQuery(document).ready(function ($) {
        $('#nickname').on('blur', function() {
            const nickname = $(this).val().trim();
            const nicknameError = $('#campo-nickname-already-used-error');
            const nicknameLoadingIcon = $('#icon-loading-nickname');

            if (!nickname) {
                document.getElementById("campo-nickname-error").style.display = 'block';
                // nicknameError.text('Il nickname è obbligatorio.').show();
                return;
            }

            // Mostra la rotella di loading all'itnerno del campo stesso?
            nicknameLoadingIcon.prop('hidden', false);

            $.ajax({
                url: env_registrazione_utente.ajax_url,
                type: 'POST',
                data: {
                    action: 'verifica_nickname',
                    nonce: env_registrazione_utente.nonce_verificaNickname,
                    nickname: nickname
                },
                success: function(response) {
                    //console.log(response);
                    if (response.success) {
                        nicknameError.hide();
                    } else {
                        nicknameError.show();
                    }
                },
                error: function() {
                    showCustomAlert('Errore', 'Errore durante la verifica del nickname.', 'bg-danger btn-danger');
                },
                complete: function() {
                    // Nasconde la rotella di loading
                    nicknameLoadingIcon.prop('hidden', true);
                }
            });
        });
    }
    );


});

function updateProgressIndicator(currentSection) {
    currentStep = currentSection;
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        if (index === currentSection - 1) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function selectPlan(plan) { 
    const plans = document.querySelectorAll('.plan-card');
    plans.forEach((p) => p.classList.remove('selected'));
    plan.classList.add('selected');
}

document.addEventListener("DOMContentLoaded", function () {
    // seleziona di default il piano annuale
    document.querySelector(`input[name="plan"][value="annuale"]`).checked = true;
    // gestione del click sui piani
    const plans = document.querySelectorAll('.plan-card');
    plans.forEach((plan) => {
        plan.addEventListener('click', function() {
            // Rimuove la classe 'selected' da tutti i piani
            plans.forEach((p) => p.classList.remove('selected'));

            // Aggiunge la classe 'selected' al piano cliccato
            this.classList.add('selected');

            // Imposta il radio button come selezionato quando un piano viene cliccato
            const selectedPlan = this.getAttribute('data-plan');
            const radioButton = document.querySelector(`input[name="plan"][value="${selectedPlan}"]`);
            if (radioButton) {
                radioButton.checked = true;
            }
        });
    });
    
    let currentStep = 0;

    document.getElementById("back2").addEventListener("click", function () {
        event.preventDefault();
        currentStep = 0;
        showStep(currentStep);
    });

});

function getSelectedLanguages() {
    const selectedLanguages = [];
    const checkboxes = document.querySelectorAll("input[name='languages[]']:checked");
    
    checkboxes.forEach((checkbox) => {
        selectedLanguages.push(checkbox.value);
    });
    
    return selectedLanguages;
}

// Funzione per registrare l'utente
function register() {
    event.preventDefault();
    // Ottieni il bottone cliccato
    var submitButton = event.currentTarget.id;
    jQuery(document).ready(function($) {
        var loadFreeSignIn = $('#icon-loading-free-signin');
        var loadSubricribe = $('#icon-loading-subscribe-now');

        var selectedPlan = null;
        // ottieni il piano selezionato col radio button se l'utente ha cliccato su "Abbonanti"
        if (submitButton === "subscribe-plan-choose-button") {
            selectedPlan = document.querySelector('input[name="plan"]:checked').value;
            loadSubricribe.prop('hidden', false);
        } else {
            loadFreeSignIn.prop('hidden', false);
        }
        var nonce = document.getElementById('nonce-profilazione').value;

        //console.log("Piano:", selectedPlan);

        jQuery.ajax({
            type: 'POST',
            url: env_registrazione_utente.ajax_url,
            data: {
                action: 'profilazione_utente',
                selectedPlan: selectedPlan,
                nonce: nonce
            },
            success: function (response) {
                //console.log(response);
                if (response.success) {
                    showCustomAlert("Esito registrazione", "Registrazione completata. Buono studio!","bg-success btn-success");
                    if (response.data.redirect ) { // L'utente ha cliccato su "Abbonati" e deve essere reindirizzato alla pagina di checkout
                        window.location.href = response.data.redirect;
                    } else { // L'utente non ha cliccato su "Abbonati" e deve essere reindirizzato alla home page
                        window.location.href = env_registrazione_utente.redirect_url; 
                    }
                } else {
                    showCustomAlert("Esito registrazione", "Registrazione fallita","bg-danger btn-danger");
                }
                loadFreeSignIn.prop('hidden', true);
                loadSubricribe.prop('hidden', true);
            }
        });
    });
}

function flagRegistrationCompleted() {
    jQuery(document).ready(function ($) {
        $.ajax({
            url: env_registrazione_utente.ajax_url, 
            type: 'POST',
            data: {
                action: 'imposta_completamento_informazioni',
                nonce: env_registrazione_utente.nonce_registrationCompleted
            },
            success: function (response) {

            },
            error: function () {
                showCustomAlert('Errore', 'Errore durante la richiesta.', 'bg-danger btn-danger');
            }
        });
    });
}

function saveProfilationData(){
    event.preventDefault();
    var nextButton = event.target.id;
    jQuery(document).ready(function ($) {
        var nonce = document.getElementById('nonce-profilazione').value;
        var nickname = document.getElementById('nickname').value;
        var universita = document.getElementById('nome_istituto_select').value;
        var corsoDiLaurea = document.getElementById('nome_corso_di_laurea_select').value;
        var anno = document.getElementById('registration_year').value;
        var linguaExtra = getSelectedLanguages();
        var loadNext1 = $('#icon-loading-next1');
        var loadNext2 = $('#icon-loading-next2');
        var nextStep = 0;

        const data = {
            action: 'salva_dati_profilazione_utente',
            nonce: env_registrazione_utente.nonce_saveProfilationData
        };

        if (nextButton === 'next1') {
            data.nickname = nickname;
            data.universita = universita;
            data.corsoDiLaurea = corsoDiLaurea;
            nextStep = 1;
            loadNext1.prop('hidden', false);

            // Controlla che tutti i campi siano compilati
            const fieldsToCheck = [
                // { field: nickname, error: 'campo-nickname-error' },
                { field: universita, error: 'campo-universita-error' },
                { field: corsoDiLaurea, error: 'campo-corso-di-laurea-error' },
            ];
            if (!checkFieldsCompleted(fieldsToCheck)) {
                loadNext1.prop('hidden', true);
                return;
            }
            var isDuplicated = false;//optare per una soluzione al termine della scrittura nel campo, evento blur
            if (isDuplicated) {
                loadNext1.prop('hidden', true);
                return;
            }
        }

        if (nextButton === 'next2') {
            data.anno = anno;
            data.linguaExtra = JSON.stringify(linguaExtra);
            nextStep = 2;
            loadNext2.prop('hidden', false);
            
            // Controlla che tutti i campi siano compilati
            const fieldsToCheck = [
                { field: anno, error: 'campo-anno-error' },
                { field: linguaExtra, error: 'campo-lingue-extra-error' },
            ];
            if (!checkFieldsCompleted(fieldsToCheck)) {
                loadNext2.prop('hidden', true);
                return;
            }
        }
        
        //console.log(data);

        $.ajax({
            url: env_registrazione_utente.ajax_url,
            type: 'POST',
            data: data,
            success: function (response) {
            if (response.success) {
                showStep(nextStep);
                if (nextStep === 2) {
                    flagRegistrationCompleted();
                }
            } else {
                showCustomAlert("Errore", "Si è verificato un errore durante il salvataggio dei dati. Riprova", "bg-danger btn-danger");
                //console.log(response.data);
            }
            loadNext1.prop('hidden', true);
            loadNext2.prop('hidden', true);
            },
            error: function () {
                showCustomAlert('Errore', 'Errore durante la richiesta.', 'bg-danger btn-danger');
                loadNext1.prop('hidden', true);
                loadNext2.prop('hidden', true);
            }
        });
    });
}

// Funzione per mostrare lo step corrente
function showStep(currentStep) {
    const steps = document.querySelectorAll(".registration-section");
    const dots = document.querySelectorAll(".dot");
    const icon = document.getElementById("emoji-step-caricamento");
    
    steps.forEach((s, index) => {
        s.classList.toggle("active", index === currentStep);
    });
    dots.forEach((d, index) => {
        d.classList.toggle("active", index === currentStep);
    });

    // Cambio immagine in base allo step
    switch (currentStep) {
        case 0:
            icon.src = baseImageUrl + "Icone_Minedocs_benvenuto.svg";//"hand.png";
            break;
        case 1:
            icon.src = baseImageUrl + "Icone_Minedocs_quasi_finito.svg";//"monkey.png";
            break;
        case 2:
            icon.src = baseImageUrl + "party.png";
            break;
        default:
            icon.src = baseImageUrl + "party.png";
            break;
    }
}


// Controlla che tutti i campi siano compilati. False se non tutti i campi sono compilati, True altrimenti
// fieldsToCheck: array di oggetti strutturati come { field: campo.value, error: idCampoErrore }
function checkFieldsCompleted(fieldsToCheck) {
    //console.log(fieldsToCheck);
    let hasError = false;
    fieldsToCheck.forEach(({ field, error }) => {
        if (error === 'campo-lingue-extra-error') {
            if (field.length === 0) {
                document.getElementById(error).style.display = 'block';
                hasError = true;
            } else {
                document.getElementById(error).style.display = 'none';
            }
        } else {
            if (!field.trim()) {
                document.getElementById(error).style.display = 'block';
                hasError = true;
            } else {
                document.getElementById(error).style.display = 'none';
            }
        }
    });
    return !hasError;
}
