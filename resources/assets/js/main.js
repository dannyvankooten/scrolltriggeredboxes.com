'use strict';

var app = {};
const helpers = app.helpers = require('./helpers.js');
const emailInputs = document.querySelectorAll('input[type="email"]');
const europeElements = document.querySelectorAll('.europe-only');
const countryInputs = document.querySelectorAll('.country-input');
const pricingForms = document.querySelectorAll('form[data-pricing]');
const confirmationElements = document.querySelectorAll('[data-confirm]');
const optionalElements = document.querySelectorAll('[data-show-if]');

function calculateNewPrice() {
    var plan = this.elements.namedItem('plan').value;
    var interval = this.elements.namedItem('interval').value;
    helpers.calculatePrice( plan, interval);
}

function showIf(el, expectedValue ) {
    return function() {
        var value = this.form.elements.namedItem(this.name).value;
        var conditionMet = value === expectedValue  || ( expectedValue === "" && value.length > 0 );
        el.style.display = ( conditionMet ) ? '' : 'none';
    }
}

function askForConfirmation(event) {
    var sure = confirm(event.target.getAttribute('data-confirm'));

    if( ! sure ) {
        event.preventDefault();
        return false;
    }
}


// hide fields with [data-show-if] attribute
[].forEach.call(optionalElements, function(el) {
    var condition = el.getAttribute('data-show-if').split(':');
    var fields = document.querySelectorAll('[name="' + condition[0] + '"]');
    var expectedValue = condition[1] || "";
    var callback = showIf(el, expectedValue);

    for(var i=0; i<fields.length; i++) {
        fields[i].addEventListener('change', callback);
        fields[i].addEventListener('keyup', callback);
        callback.call(fields[i]);
    }
});

[].forEach.call( emailInputs, function(input) {
    input.addEventListener('blur', helpers.checkEmail);
});

[].forEach.call(countryInputs, function(input) {
    input.addEventListener('change', function() {
        helpers.toggleElements(europeElements, helpers.isCountryInEurope(this.value));
    });
    helpers.toggleElements(europeElements, helpers.isCountryInEurope(input.value));
});

[].forEach.call(pricingForms, function(form) {
    form.addEventListener('change', calculateNewPrice.bind(form));
    form.addEventListener('keyup', calculateNewPrice.bind(form));
    calculateNewPrice.call(form);
});

[].forEach.call(confirmationElements, function(element) {
    var event = element.tagName === 'FORM' ? 'submit' : 'click';
    element.addEventListener(event, askForConfirmation);
});


window.app = app;


