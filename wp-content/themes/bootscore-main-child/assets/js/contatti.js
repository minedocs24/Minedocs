function sendContactEmail(){

    var name = document.getElementById('contact_name').value;
    var email = document.getElementById('contact_email').value;
    var message = document.getElementById('contact_message').value;
    
    var isValid = true;
    if (!name) {
        document.getElementById('error_name').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('error_name').style.display = 'none';
    }

    if (!email) {
        document.getElementById('error_email').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('error_email').style.display = 'none';
    }

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        document.getElementById('error_email_format').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('error_email_format').style.display = 'none';
    }

    if (!message) {
        document.getElementById('error_message').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('error_message').style.display = 'none';
    }

    if (!isValid) {
        return;
    }

    var formData = {
        'action': 'handle_contact_form',
        'name': name, 
        'email': email,
        'message': message,
        'nonce': env_contatti.nonce
    };

    jQuery(document).ready(function($) {
        var load = $('#icon-loading-send');
        load.prop('hidden', false);

        jQuery.ajax({
            type: 'POST',
            url: env_contatti.ajax_url,
            data: formData,
            success: function(response) {
                if(response.success){
                    showCustomAlert('Email inviata', 'Grazie per il tuo messaggio! Ti risponderemo a breve.', 'btn-success bg-success');
                    // Resetta il form e torna alla sezione FAQ
                    document.getElementById('contact-form').reset();
                    setTimeout(showFaqSection, 2000);
                }
                else if(response.data.message === "too_many_attempts") {
                    showCustomAlert('Errore', "Hai raggiunto il limite di invii consentiti. Riprova più tardi. Ti contatteremo al più presto.", 'btn-danger bg-danger');
                }
                else{
                    showCustomAlert('Errore', 'Si è verificato un errore. Riprova più tardi.', 'btn-danger bg-danger');
                }
            },
            error: function(response) {
                showCustomAlert('Errore', 'Si è verificato un errore. Riprova più tardi.', 'btn-danger bg-danger');
            },
            complete: function(response) {
                load.prop('hidden', true);
            }
        });
    });
}

// Funzione per mostrare il form di contatto con animazione
function showContactForm() {
    const formContainer = document.getElementById('contact-form-container');
    const faqSection = document.querySelector('.faq-section');
    
    // Nascondi la sezione FAQ con animazione
    faqSection.style.transition = 'opacity 0.3s ease-out';
    faqSection.style.opacity = '0';
    
    setTimeout(() => {
        faqSection.style.display = 'none';
        
        // Mostra il form con animazione
        formContainer.style.display = 'block';
        formContainer.style.opacity = '0';
        formContainer.style.transform = 'translateY(20px)';
        formContainer.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        
        // Forza il reflow
        formContainer.offsetHeight;
        
        formContainer.style.opacity = '1';
        formContainer.style.transform = 'translateY(0)';
        
        // Scrolla al form
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 300);
}

// Funzione per tornare alla sezione FAQ
function showFaqSection() {
    const formContainer = document.getElementById('contact-form-container');
    const faqSection = document.querySelector('.faq-section');
    
    // Nascondi il form con animazione
    formContainer.style.opacity = '0';
    formContainer.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        formContainer.style.display = 'none';
        
        // Mostra la sezione FAQ con animazione
        faqSection.style.display = 'block';
        faqSection.style.opacity = '0';
        
        // Forza il reflow
        faqSection.offsetHeight;
        
        faqSection.style.opacity = '1';
        
        // Scrolla alla sezione FAQ
        faqSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 300);
}
