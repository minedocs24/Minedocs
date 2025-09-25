function getCookie(name) {
    let cookieArr = document.cookie.split(";");
    for(let i = 0; i < cookieArr.length; i++) {
        let cookiePair = cookieArr[i].split("=");
        if(name == cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
}

function showPointsModal() {
    // Create a placeholder modal
    let placeholderModalHtml = `
        <div class="modal fade" id="pointsModal" tabindex="-1" aria-labelledby="pointsModalLabel" aria-hidden="true" style="z-index: 1000000 !important;">
            <div class="modal-dialog">
                <div class="modal-content placeholder-glow">
                    <div class="modal-header placeholder">
                        <h5 class="modal-title" id="pointsModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card" aria-hidden="true">

                        <div class="card-body">
                            <h5 class="card-title placeholder-glow">
                            <span class="placeholder col-6"></span>
                            </h5>
                            <p class="card-text placeholder-glow">
                            <span class="placeholder col-7"></span>
                            <span class="placeholder col-4"></span>
                            <span class="placeholder col-4"></span>
                            <span class="placeholder col-6"></span>
                            <span class="placeholder col-8"></span>
                            </p>
                            <a href="#" tabindex="-1" class="btn btn-primary disabled placeholder col-6"></a>
                        </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    `;
    jQuery('body').append(placeholderModalHtml);
    let pointsModal = new bootstrap.Modal(document.getElementById('pointsModal'));
    pointsModal.show();

    // Fetch the actual content
    jQuery.ajax({
        url: env_general_functions.ajax_url,
        type: 'POST',
        data: {
            action: 'popup_come_guadagnare_punti'
        },
        success: function(response) {
            let data = response.data;

            //console.log(data);

            contenuto = data.contenuto;
            titolo = data.titolo;
            larghezza = data.larghezza;

            


                
            
            // Update the modal content
            jQuery('#pointsModal .modal-body').html(contenuto);
            jQuery('#pointsModal .modal-title').text(titolo);
            jQuery('#pointsModal .modal-dialog').css('min-width', larghezza);
            // Remove placeholder classes
            jQuery('#pointsModal .modal-content').removeClass('placeholder-glow');
            jQuery('#pointsModal .modal-header').removeClass('placeholder');
            jQuery('#pointsModal .modal-body').removeClass('placeholder-glow');
            jQuery('#pointsModal .modal-body .placeholder').removeClass('placeholder');
        },
        error: function(xhr, status, error) {
            console.error('Error fetching the PHP file:', error);
            jQuery('#pointsModal .modal-body').html('An error occurred while loading the content.');
            jQuery('#pointsModal .modal-title').text('Error');
            // Remove placeholder classes
            jQuery('#pointsModal .modal-content').removeClass('placeholder-glow');
            jQuery('#pointsModal .modal-header').removeClass('placeholder');
            jQuery('#pointsModal .modal-body').removeClass('placeholder-glow');
            jQuery('#pointsModal .modal-body .placeholder').removeClass('placeholder');
        }
    });
}


jQuery(document).on('mouseenter', '[tooltip]', function(event) {
    let tooltipText = jQuery(this).attr('tooltip');
    let tooltipColor = jQuery(this).attr('tooltip-color') || '#d3d3d3'; // Default to light gray if not set
    let tooltipTextColor = jQuery(this).attr('tooltip-text-color') || '#000000'; // Default to black if not set
    let tooltip = jQuery('<div class="custom-tooltip"></div>').html(tooltipText);
    jQuery('body').append(tooltip);
    
    // Calculate tooltip position
    let tooltipTop = event.pageY + 10 + 'px';
    let tooltipLeft = event.pageX + 10 + 'px';

    // Adjust position for small screens
    if (window.innerWidth < 768) {
        tooltipLeft = Math.min(event.pageX + 10, window.innerWidth - tooltip.outerWidth() - 10) + 'px';
    }

    tooltip.css({
        top: tooltipTop,
        left: tooltipLeft,
        position: 'absolute',
        backgroundColor: 'var(--secondary)', // Colore di sfondo coerente
        color: 'var(--white)', // Colore del testo
        padding: '5px 10px',
        borderRadius: 'var(--border-radius-sm)', // Utilizza la variabile di stile
        boxShadow: '0 0 5px rgba(0,0,0,0.3)',
        zIndex: 10000,
        maxWidth: '300px'
    });
}).on('mouseleave', '[tooltip]', function() {
    jQuery('.custom-tooltip').remove();
}).on('mousemove', '[tooltip]', function(event) {
    let tooltipLeft = event.pageX + 10 + 'px';

    // Adjust position for small screens
    if (window.innerWidth < 768) {
        tooltipLeft = Math.min(event.pageX + 10, window.innerWidth - jQuery('.custom-tooltip').outerWidth() - 10) + 'px';
    }

    jQuery('.custom-tooltip').css({
        top: event.pageY + 10 + 'px',
        left: tooltipLeft
    });
});


/**
 * This function validates the format of an email address.
 * 
 * Controls performed:
 * - Ensures the email matches a standard email format using a regular expression.
 * - The regular expression checks for:
 *   - Local part before the "@" symbol.
 *   - A domain name after the "@" symbol.
 *   - A valid top-level domain (TLD) with at least two characters.
 * 
 * @param {string} email - The email address to validate.
 * @returns {boolean} - Returns true if the email format is valid, otherwise false.
 */
function validate_email_format(email) {
    // Regular expression for validating an Email
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((?!.*--)[^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*\.[^<>()\[\]\\.,;:\s@"]{2,})$/i;
    return re.test(String(email).toLowerCase());
}



/*
console.log('Testing validate_email_format function...');

// Test cases for valid email formats
const validEmails = [
    'test@example.com',
    'user.name+tag+sorting@example.com',
    'user_name@example.co.uk',
    'user-name@sub.example.org',
    'user@123.123.123.123',
    'user@[IPv6:2001:db8::1]',
    'paypal-+++-@gmail.com',

];

validEmails.forEach(email => {
    const result = validate_email_format(email);
    console.log(`validate_email_format('${email}') === true:`, result === true);
});

// Test cases for invalid email formats
const invalidEmails = [
    'plainaddress',
    '@missingusername.com',
    'username@.com',
    'username@com',
    'username@domain..com',
    'username@domain,com',
    'username@domain@domain.com',
    'username@domain..com',
    'username@.domain.com',
    'paypal---@gmail.com',
    'paypal-@gmail.com',
    'paypal--@gmail.com',
    'paypal---@gmail.com'

];

invalidEmails.forEach(email => {
    const result = validate_email_format(email);
    console.log(`validate_email_format('${email}') === false:`, result === false);
});

// Test cases for empty or non-string inputs
const invalidInputs = [
    '',
    null,
    undefined,
    12345,
    {},
    [],
    true,
    false
];

invalidInputs.forEach(input => {
    const result = validate_email_format(input);
    console.log(`validate_email_format(${JSON.stringify(input)}) === false:`, result === false);
});
*/

document.addEventListener('DOMContentLoaded', function() {

    const sidebar = document.getElementById('sidebarMobile-profilo-utente');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('overlay-profilo-utente');

    toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
    });

});