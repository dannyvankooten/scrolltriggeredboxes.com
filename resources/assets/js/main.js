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

    var creditCardInput = form.querySelector('[data-stripe="number"]');
    creditCardInput.addEventListener('change', function() {
        var valid = Stripe.card.validateCardNumber(this.value);

        if( ! valid ) {
            this.className = this.className + ' invalid';
        } else {
            this.className = this.className.replace('invalid', '');
        }
    });


    form.addEventListener('submit', function(event) {
        var form = event.form || event.target || this.form || this;
        event.preventDefault();

        // validate expiry date
        var creditCardInput = form.querySelector('[data-stripe="number"]');
        var monthInput = form.querySelector('[data-stripe="exp_month"]');
        var yearInput = form.querySelector('[data-stripe="exp_year"]');

        if( ! Stripe.card.validateCardNumber(creditCardInput.value) ) {
            helpers.showFormError(form, 'That card number does not look right, sorry.');
            return;
        }

        if( ! Stripe.card.validateExpiry(monthInput.value, yearInput.value) ) {
            helpers.showFormError(form, 'That expiry date does not look right, sorry.');
            return;
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
        var plan = this.plan.value;
        var selectedInterval = [].filter.call( this.interval, function(node) { return node.checked; }).pop().value;
        helpers.calculatePrice( plan, selectedInterval);
    });
    form.addEventListener('keyup', function() {
        var plan = this.plan.value;
        var selectedInterval = [].filter.call( this.interval, function(node) { return node.checked; }).pop().value;
        helpers.calculatePrice( plan, selectedInterval);
    });

    var selectedInterval = [].filter.call( form.interval, function(node) { return node.checked; }).pop().value;
    helpers.calculatePrice(form.plan.value, selectedInterval);
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


