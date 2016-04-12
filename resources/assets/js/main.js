'use strict';

var mailcheck = require('mailcheck');
var emailInputs = document.querySelectorAll('input[type="email"]');
var hintElement;

[].forEach.call( emailInputs, function(input) {
    input.addEventListener('blur', checkEmail);
});

function checkEmail() {
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
}
