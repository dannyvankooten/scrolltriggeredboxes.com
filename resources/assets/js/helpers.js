'use strict';

var mailcheck = require('mailcheck');
var hintElement;
var helpers = {};

helpers.checkEmail = function() {
    var input = this;

    if( hintElement ) {
        hintElement.parentNode.removeChild(hintElement);
        hintElement = null;
    }

    hintElement = document.createElement('div');
    hintElement.className = "muted email-hint tiny-margin";
    this.parentNode.appendChild(hintElement);


    mailcheck.run({
        email: this.value,
        suggested: function(suggestion) {
            hintElement.innerHTML = "Did you mean <a>" + suggestion.address + "@<strong>"+ suggestion.domain +"</strong></a>?";
            hintElement.onclick = function() {
                input.value = suggestion.full;
                hintElement.parentNode.removeChild(hintElement);
                hintElement = null;
            };
        }
    });
};

helpers.isCountryInEurope = function(country) {
    var europeanCountries = [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' ];
    return country.length > 0 && europeanCountries.indexOf(country.toUpperCase()) > -1;
};

helpers.calculatePrice = function(plan, interval) {
    var planPrices = {
        "personal": 6,
        "developer": 10,
        "agency": 24
    };
    var price = planPrices[plan];
    var isYearly = interval === 'year';
    var total = isYearly ? price * 10 : price;
    total += 0;

    var elements = document.querySelectorAll('.price');
    [].forEach.call(elements,function(el) {
        el.innerHTML = '$' + total + ( isYearly ? ' per year' : ' per month' );
    });
};

helpers.toggleElements = function( elements, show ) {
    [].forEach.call(elements, function(el) {
        el.style.display = show ? '' : 'none';
    });
};

helpers.showFormError = function(form, msg) {
    var errorElement = form.querySelector('.errors');
    errorElement.className = "errors notice notice-warning";
    errorElement.innerText = msg;
};

module.exports = helpers;