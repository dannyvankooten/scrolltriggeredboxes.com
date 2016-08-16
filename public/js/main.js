(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/*
 * Mailcheck https://github.com/mailcheck/mailcheck
 * Author
 * Derrick Ko (@derrickko)
 *
 * Released under the MIT License.
 *
 * v 1.1.1
 */

var Mailcheck = {
  domainThreshold: 2,
  secondLevelThreshold: 2,
  topLevelThreshold: 2,

  defaultDomains: ['msn.com', 'bellsouth.net',
    'telus.net', 'comcast.net', 'optusnet.com.au',
    'earthlink.net', 'qq.com', 'sky.com', 'icloud.com',
    'mac.com', 'sympatico.ca', 'googlemail.com',
    'att.net', 'xtra.co.nz', 'web.de',
    'cox.net', 'gmail.com', 'ymail.com',
    'aim.com', 'rogers.com', 'verizon.net',
    'rocketmail.com', 'google.com', 'optonline.net',
    'sbcglobal.net', 'aol.com', 'me.com', 'btinternet.com',
    'charter.net', 'shaw.ca'],

  defaultSecondLevelDomains: ["yahoo", "hotmail", "mail", "live", "outlook", "gmx"],

  defaultTopLevelDomains: ["com", "com.au", "com.tw", "ca", "co.nz", "co.uk", "de",
    "fr", "it", "ru", "net", "org", "edu", "gov", "jp", "nl", "kr", "se", "eu",
    "ie", "co.il", "us", "at", "be", "dk", "hk", "es", "gr", "ch", "no", "cz",
    "in", "net", "net.au", "info", "biz", "mil", "co.jp", "sg", "hu"],

  run: function(opts) {
    opts.domains = opts.domains || Mailcheck.defaultDomains;
    opts.secondLevelDomains = opts.secondLevelDomains || Mailcheck.defaultSecondLevelDomains;
    opts.topLevelDomains = opts.topLevelDomains || Mailcheck.defaultTopLevelDomains;
    opts.distanceFunction = opts.distanceFunction || Mailcheck.sift3Distance;

    var defaultCallback = function(result){ return result };
    var suggestedCallback = opts.suggested || defaultCallback;
    var emptyCallback = opts.empty || defaultCallback;

    var result = Mailcheck.suggest(Mailcheck.encodeEmail(opts.email), opts.domains, opts.secondLevelDomains, opts.topLevelDomains, opts.distanceFunction);

    return result ? suggestedCallback(result) : emptyCallback()
  },

  suggest: function(email, domains, secondLevelDomains, topLevelDomains, distanceFunction) {
    email = email.toLowerCase();

    var emailParts = this.splitEmail(email);

    if (secondLevelDomains && topLevelDomains) {
        // If the email is a valid 2nd-level + top-level, do not suggest anything.
        if (secondLevelDomains.indexOf(emailParts.secondLevelDomain) !== -1 && topLevelDomains.indexOf(emailParts.topLevelDomain) !== -1) {
            return false;
        }
    }

    var closestDomain = this.findClosestDomain(emailParts.domain, domains, distanceFunction, this.domainThreshold);

    if (closestDomain) {
      if (closestDomain == emailParts.domain) {
        // The email address exactly matches one of the supplied domains; do not return a suggestion.
        return false;
      } else {
        // The email address closely matches one of the supplied domains; return a suggestion
        return { address: emailParts.address, domain: closestDomain, full: emailParts.address + "@" + closestDomain };
      }
    }

    // The email address does not closely match one of the supplied domains
    var closestSecondLevelDomain = this.findClosestDomain(emailParts.secondLevelDomain, secondLevelDomains, distanceFunction, this.secondLevelThreshold);
    var closestTopLevelDomain    = this.findClosestDomain(emailParts.topLevelDomain, topLevelDomains, distanceFunction, this.topLevelThreshold);

    if (emailParts.domain) {
      var closestDomain = emailParts.domain;
      var rtrn = false;

      if(closestSecondLevelDomain && closestSecondLevelDomain != emailParts.secondLevelDomain) {
        // The email address may have a mispelled second-level domain; return a suggestion
        closestDomain = closestDomain.replace(emailParts.secondLevelDomain, closestSecondLevelDomain);
        rtrn = true;
      }

      if(closestTopLevelDomain && closestTopLevelDomain != emailParts.topLevelDomain) {
        // The email address may have a mispelled top-level domain; return a suggestion
        closestDomain = closestDomain.replace(emailParts.topLevelDomain, closestTopLevelDomain);
        rtrn = true;
      }

      if (rtrn == true) {
        return { address: emailParts.address, domain: closestDomain, full: emailParts.address + "@" + closestDomain };
      }
    }

    /* The email address exactly matches one of the supplied domains, does not closely
     * match any domain and does not appear to simply have a mispelled top-level domain,
     * or is an invalid email address; do not return a suggestion.
     */
    return false;
  },

  findClosestDomain: function(domain, domains, distanceFunction, threshold) {
    threshold = threshold || this.topLevelThreshold;
    var dist;
    var minDist = 99;
    var closestDomain = null;

    if (!domain || !domains) {
      return false;
    }
    if(!distanceFunction) {
      distanceFunction = this.sift3Distance;
    }

    for (var i = 0; i < domains.length; i++) {
      if (domain === domains[i]) {
        return domain;
      }
      dist = distanceFunction(domain, domains[i]);
      if (dist < minDist) {
        minDist = dist;
        closestDomain = domains[i];
      }
    }

    if (minDist <= threshold && closestDomain !== null) {
      return closestDomain;
    } else {
      return false;
    }
  },

  sift3Distance: function(s1, s2) {
    // sift3: http://siderite.blogspot.com/2007/04/super-fast-and-accurate-string-distance.html
    if (s1 == null || s1.length === 0) {
      if (s2 == null || s2.length === 0) {
        return 0;
      } else {
        return s2.length;
      }
    }

    if (s2 == null || s2.length === 0) {
      return s1.length;
    }

    var c = 0;
    var offset1 = 0;
    var offset2 = 0;
    var lcs = 0;
    var maxOffset = 5;

    while ((c + offset1 < s1.length) && (c + offset2 < s2.length)) {
      if (s1.charAt(c + offset1) == s2.charAt(c + offset2)) {
        lcs++;
      } else {
        offset1 = 0;
        offset2 = 0;
        for (var i = 0; i < maxOffset; i++) {
          if ((c + i < s1.length) && (s1.charAt(c + i) == s2.charAt(c))) {
            offset1 = i;
            break;
          }
          if ((c + i < s2.length) && (s1.charAt(c) == s2.charAt(c + i))) {
            offset2 = i;
            break;
          }
        }
      }
      c++;
    }
    return (s1.length + s2.length) /2 - lcs;
  },

  splitEmail: function(email) {
    var parts = email.trim().split('@');

    if (parts.length < 2) {
      return false;
    }

    for (var i = 0; i < parts.length; i++) {
      if (parts[i] === '') {
        return false;
      }
    }

    var domain = parts.pop();
    var domainParts = domain.split('.');
    var sld = '';
    var tld = '';

    if (domainParts.length == 0) {
      // The address does not have a top-level domain
      return false;
    } else if (domainParts.length == 1) {
      // The address has only a top-level domain (valid under RFC)
      tld = domainParts[0];
    } else {
      // The address has a domain and a top-level domain
      sld = domainParts[0];
      for (var i = 1; i < domainParts.length; i++) {
        tld += domainParts[i] + '.';
      }
      tld = tld.substring(0, tld.length - 1);
    }

    return {
      topLevelDomain: tld,
      secondLevelDomain: sld,
      domain: domain,
      address: parts.join('@')
    }
  },

  // Encode the email address to prevent XSS but leave in valid
  // characters, following this official spec:
  // http://en.wikipedia.org/wiki/Email_address#Syntax
  encodeEmail: function(email) {
    var result = encodeURI(email);
    result = result.replace('%20', ' ').replace('%25', '%').replace('%5E', '^')
                   .replace('%60', '`').replace('%7B', '{').replace('%7C', '|')
                   .replace('%7D', '}');
    return result;
  }
};

// Export the mailcheck object if we're in a CommonJS env (e.g. Node).
// Modeled off of Underscore.js.
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Mailcheck;
}

// Support AMD style definitions
// Based on jQuery (see http://stackoverflow.com/a/17954882/1322410)
if (typeof define === "function" && define.amd) {
  define("mailcheck", [], function() {
    return Mailcheck;
  });
}

if (typeof window !== 'undefined' && window.jQuery) {
  (function($){
    $.fn.mailcheck = function(opts) {
      var self = this;
      if (opts.suggested) {
        var oldSuggested = opts.suggested;
        opts.suggested = function(result) {
          oldSuggested(self, result);
        };
      }

      if (opts.empty) {
        var oldEmpty = opts.empty;
        opts.empty = function() {
          oldEmpty.call(null, self);
        };
      }

      opts.email = this.val();
      Mailcheck.run(opts);
    }
  })(jQuery);
}

},{}],2:[function(require,module,exports){
'use strict';

var mailcheck = require('mailcheck');
var hintElement;
var helpers = {};

helpers.checkEmail = function () {
    var input = this;

    if (hintElement) {
        hintElement.parentNode.removeChild(hintElement);
        hintElement = null;
    }

    hintElement = document.createElement('div');
    hintElement.className = "muted email-hint tiny-margin";
    this.parentNode.appendChild(hintElement);

    mailcheck.run({
        email: this.value,
        suggested: function suggested(suggestion) {
            hintElement.innerHTML = "Did you mean <a>" + suggestion.address + "@<strong>" + suggestion.domain + "</strong></a>?";
            hintElement.onclick = function () {
                input.value = suggestion.full;
                hintElement.parentNode.removeChild(hintElement);
                hintElement = null;
            };
        }
    });
};

helpers.isCountryInEurope = function (country) {
    var europeanCountries = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];
    return country.length > 0 && europeanCountries.indexOf(country.toUpperCase()) > -1;
};

helpers.calculatePrice = function (plan, interval) {
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
    [].forEach.call(elements, function (el) {
        el.innerHTML = '$' + total + (isYearly ? ' per year' : ' per month');
    });
};

helpers.toggleElements = function (elements, show) {
    [].forEach.call(elements, function (el) {
        el.style.display = show ? '' : 'none';
    });
};

helpers.showFormError = function (form, msg) {
    var errorElement = form.querySelector('.errors');
    errorElement.className = "errors notice notice-warning";
    errorElement.innerText = msg;
};

module.exports = helpers;

},{"mailcheck":1}],3:[function(require,module,exports){
'use strict';

var app = {};
var helpers = app.helpers = require('./helpers.js');
var emailInputs = document.querySelectorAll('input[type="email"]');
var creditCardForms = document.querySelectorAll('form[data-credit-card]');
var europeElements = document.querySelectorAll('.europe-only');
var countryInputs = document.querySelectorAll('.country-input');
var pricingForms = document.querySelectorAll('form[data-pricing]');
var confirmationElements = document.querySelectorAll('[data-confirm]');
var dropdownToggles = document.querySelectorAll('.dropdown-toggle');
var optionalElements = document.querySelectorAll('[data-show-if]');

function showIf(el, expectedValue) {
    return function () {
        var value = this.form.elements.namedItem(this.name).value;
        var conditionMet = value === expectedValue || expectedValue === "" && value.length > 0;
        el.style.display = conditionMet ? '' : 'none';
    };
}

// hide fields with [data-show-if] attribute
[].forEach.call(optionalElements, function (el) {
    var condition = el.getAttribute('data-show-if').split(':');
    var fields = document.querySelectorAll('[name="' + condition[0] + '"]');
    var expectedValue = condition[1] || "";
    var callback = showIf(el, expectedValue);

    for (var i = 0; i < fields.length; i++) {
        fields[i].addEventListener('change', callback);
        fields[i].addEventListener('keyup', callback);
        callback.call(fields[i]);
    }
});

var askForConfirmation = function askForConfirmation(event) {
    var sure = confirm(this.getAttribute('data-confirm'));

    if (!sure) {
        event.preventDefault();
        return false;
    }
};

[].forEach.call(emailInputs, function (input) {
    input.addEventListener('blur', helpers.checkEmail);
});

[].forEach.call(creditCardForms, function (form) {

    var creditCardInput = form.querySelector('[data-stripe="number"]');
    creditCardInput.addEventListener('change', function () {
        var valid = Stripe.card.validateCardNumber(this.value);

        if (!valid) {
            this.className = this.className + ' invalid';
        } else {
            this.className = this.className.replace('invalid', '');
        }
    });

    form.addEventListener('submit', function (event) {
        var form = event.form || event.target || this.form || this;
        event.preventDefault();

        // validate expiry date
        var creditCardInput = form.querySelector('[data-stripe="number"]');
        var monthInput = form.querySelector('[data-stripe="exp_month"]');
        var yearInput = form.querySelector('[data-stripe="exp_year"]');

        if (!Stripe.card.validateCardNumber(creditCardInput.value)) {
            helpers.showFormError(form, 'That card number does not look right, sorry.');
            return;
        }

        if (!Stripe.card.validateExpiry(monthInput.value, yearInput.value)) {
            helpers.showFormError(form, 'That expiry date does not look right, sorry.');
            return;
        }

        // disable button
        var submitButton = form.querySelector('[type="submit"]');
        var buttonText = submitButton.value;

        submitButton.disabled = true;
        submitButton.value = "Please wait";

        Stripe.card.createToken(this, function (status, response) {

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

[].forEach.call(countryInputs, function (input) {
    input.addEventListener('change', function () {
        helpers.toggleElements(europeElements, helpers.isCountryInEurope(this.value));
    });
    helpers.toggleElements(europeElements, helpers.isCountryInEurope(input.value));
});

[].forEach.call(pricingForms, function (form) {
    function updatePrice() {
        var plan = [].filter.call(this.plan, function (node) {
            return node.checked;
        }).pop().value || "personal";
        var selectedInterval = [].filter.call(this.interval, function (node) {
            return node.checked;
        }).pop().value || "month";
        helpers.calculatePrice(plan, selectedInterval);
    }

    form.addEventListener('change', updatePrice);
    form.addEventListener('keyup', updatePrice);
    updatePrice.call(form);
});

[].forEach.call(confirmationElements, function (element) {
    var event = element.tagName === 'FORM' ? 'submit' : 'click';
    element.addEventListener(event, askForConfirmation);
});

[].forEach.call(dropdownToggles, function (element) {
    element.addEventListener('click', function () {
        this.parentNode.classList.toggle('open');
    });
});

window.app = app;

},{"./helpers.js":2}]},{},[3]);

//# sourceMappingURL=main.js.map
