'use strict';

const braintree = require('braintree-web');
const forms = document.querySelectorAll('form[data-payment-method]');
var paypal;

// Create a client.
braintree.client.create({ authorization: window.BraintreeClientToken }, braintreeHandleClient);

function braintreeHandleClient(err, clientInstance) {
    if(err) {
        console.error('Error creating client:', err);
        return;
    }

    // Create a PayPal component.
    braintree.paypal.create({client: clientInstance}, braintreeHandlePaypalClient)
}

function braintreeHandlePaypalClient(err, paypalInstance) {
    if(err) {
        console.error('Error creating PayPal:', err);
        return;
    }

    // store instance
    paypal = paypalInstance;
}

function showFormError(form, msg) {
    var errorElement = form.querySelector('.errors');
    errorElement.className = "errors notice notice-warning";
    errorElement.innerText = msg;
}

function disableForm(form) {
    // disable button
    const submitButton = form.querySelector('[type="submit"]');
    const buttonText = submitButton.value;
    submitButton.disabled = true;
    submitButton.value = "Please wait";

    return function() {
        submitButton.disabled = false;
        submitButton.value = buttonText;
    }
}

function generateStripeToken(form) {
    // validate expiry date
    var creditCardInput = form.querySelector('[data-stripe="number"]');
    var monthInput = form.querySelector('[data-stripe="exp_month"]');
    var yearInput = form.querySelector('[data-stripe="exp_year"]');

    if( ! Stripe.card.validateCardNumber(creditCardInput.value) ) {
        showFormError(form, 'That card number does not look right, sorry.');
        return;
    }

    if( ! Stripe.card.validateExpiry(monthInput.value, yearInput.value) ) {
        showFormError(form, 'That expiry date does not look right, sorry.');
        return;
    }

    const enableForm = disableForm(form);

    Stripe.card.createToken(form, function(status, response) {

        if (response.error) {
            enableForm();
            showFormError(form, response.error.message);
        } else {
            form.elements.namedItem('user[card_last_four]').value = response.card.last4;
            form.elements.namedItem('payment_token').value = response.id;
            form.submit();
        }
    });
}

function generateBraintreeToken(form) {
    const enableForm = disableForm(form);

    if( ! paypal ) {
        window.setTimeout(generateBraintreeToken.call(form), 1000);
    }

    paypal.tokenize({
        flow: 'vault'
    }, function (err, payload) {

        if (err) {
            if (err.type !== 'CUSTOMER') {
                console.error('Error tokenizing:', err);
            }

            enableForm();
            return;
        }

        form.elements.namedItem('user[paypal_email]').value = payload.details.email;
        form.elements.namedItem('payment_token').value = payload.nonce;
        form.submit();
    });
}

[].forEach.call( forms, function(form) {
    const initialPaymentMethod = form.elements.namedItem('payment_method').value;
    const creditCardInput = form.querySelector('[data-stripe="number"]');

    creditCardInput.addEventListener('change', function() {
        var valid = Stripe.card.validateCardNumber(this.value);
        this.className = valid ? this.className.replace('invalid', '') : this.className + ' invalid';
    });

    form.addEventListener('submit', function(event) {
        const form = event.form || event.target || this.form || this;
        const chosenPaymentMethod = form.elements.namedItem('payment_method').value;

        if(chosenPaymentMethod === 'stripe') {
            event.preventDefault();
            return generateStripeToken(form);
        }

        if(chosenPaymentMethod === 'braintree') {
            event.preventDefault();
            return generateBraintreeToken(form);
        }
    });
});