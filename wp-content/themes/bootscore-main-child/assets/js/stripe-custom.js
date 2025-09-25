document.addEventListener('DOMContentLoaded', function () {
    const stripe = Stripe(stripe_params.key);
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#stripe-card-element');

    const form = document.querySelector('form.checkout');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        stripe.createPaymentMethod('card', card).then(function (result) {
            if (result.error) {
                showCustomAlert('Errore', result.error.message, 'bg-danger btn-danger');
            } else {
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'payment_method_id');
                hiddenInput.setAttribute('value', result.paymentMethod.id);
                form.appendChild(hiddenInput);
                form.submit();
            }
        });
    });
});

