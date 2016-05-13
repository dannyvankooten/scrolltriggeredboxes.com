'use strict';

var app = {};
var helpers = app.helpers = require('./helpers.js');
var emailInputs = document.querySelectorAll('input[type="email"]');
var creditCardForms  = document.querySelectorAll('form[data-credit-card]');
var europeElements = document.querySelectorAll('.europe-only');
var countryInputs = document.querySelectorAll('.country-input');
var pricingForms = document.querySelectorAll('form[data-pricing]');
var confirmationElements = document.querySelectorAll('[data-confirm]');
var dropdownToggles = document.querySelectorAll('.dropdown-toggle');

var askForConfirmation = function(event) {
    var sure = confirm(this.getAttribute('data-confirm'));

    if( ! sure ) {
        event.preventDefault();
        return false;
    }
};

[].forEach.call( emailInputs, function(input) {
    input.addEventListener('blur', helpers.checkEmail);
});

[].forEach.call( creditCardForms, function(form) {
    form.addEventListener('submit', function(event) {
        var form = this;
        event.preventDefault();

        // soft-validate credit card
        var creditCardNumber = form.querySelector('[data-stripe="number"]').value.trim();
        if( ! Stripe.card.validateCardNumber(creditCardNumber) ) {
            helpers.showFormError(form, "That credit card number doesn't seem right, sorry.");
            return false;
        }

        // validate cvc
        var exp = {
            month: form.querySelector('[data-stripe="exp_month"]').value.trim(),
            year: form.querySelector('[data-stripe="exp_year"]').value.trim()
        };
        if( ! Stripe.card.validateExpiry(exp.month, exp.year) ) {
            helpers.showFormError(form, "That expiration date doesn't seem right, sorry.");
            return false;
        }

        // disable button
        var submitButton = form.querySelector('[type="submit"]');
        var buttonText = submitButton.value;

        submitButton.disabled = true;
        submitButton.value = "Please wait";

        Stripe.card.createToken(this, function(status, response) {

            if (response.error) {
                // re-enable button
                submitButton.value = buttonText;
                submitButton.removeAttribute('disabled');

                helpers.showFormError(form, response.error.message);
            } else {
                form.elements.namedItem('user[card_last_four]').value = response.card.last4;
                form.elements.namedItem('payment_token').value = response.id;
                form.submit();
            }
        });

        return false;
    });
});

[].forEach.call(countryInputs, function(input) {
    input.addEventListener('change', function() {
        helpers.toggleElements(europeElements, helpers.isCountryInEurope(this.value));
    });

    helpers.toggleElements(europeElements, helpers.isCountryInEurope(input.value));
});

[].forEach.call(pricingForms, function(form) {
    form.addEventListener('change', function() {
        helpers.calculatePrice(this.quantity.value, this.interval.value);
    });
    form.addEventListener('keyup', function() {
        helpers.calculatePrice(this.quantity.value, this.interval.value);
    });

    helpers.calculatePrice(form.quantity.value, form.interval.value);
});

[].forEach.call(confirmationElements, function(element) {
    var event = element.tagName === 'FORM' ? 'submit' : 'click';
    element.addEventListener(event, askForConfirmation);
});

[].forEach.call(dropdownToggles, function(element) {
    element.addEventListener('click', function() {
        this.parentNode.classList.toggle('open');
    });

});

window.app = app;


