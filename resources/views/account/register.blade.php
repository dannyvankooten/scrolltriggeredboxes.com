@extends('layouts.master')

@section('title','Register - Boxzilla')

@section('content')

<div class="container">

    <h1>Register</h1>

    <form method="post" id="buy-form" novalidate="novalidate">

        <div class="breadcrumb bordered small-margin">
            <a class="unstyled" onclick="steps.go(1);" data-step="1">1: License</a>
            <span class="muted sep"> // </span>
            <a class="unstyled" onclick="steps.go(2)" data-step="2">2: Billing Info</a>
            <span class="muted sep"> // </span>
            <a class="unstyled" onclick="steps.go(3)" data-step="3">3: Payment</a>
        </div>

        <!-- Step 1: License -->
        <div class="step">

            <div class="form-group">
                <label class="control-label">How many site activations do you need?</label>
                <input type="number" name="quantity" class="form-control" value="1" step="1" required />
            </div>

            <div class="form-group radio">
                <label class="control-label">Would you like to pay monthly or yearly?</label>

                <label class="unstyled"><input type="radio" name="interval" value="month" checked> Monthly</label>
                <label class="unstyled"><input type="radio" name="interval" value="year"> Yearly</label>
            </div>

            <p>You will be charged <span class="total strong">$10 per month</span>.</p>

            <div class="form-group">
                <input type="submit" value="Proceed to billing info" class="button">
            </div>

        </div>
        <!-- / Step -->

        <!-- Step 2: Billing Info -->
        <div class="step" style="hide-if-js">

            <div class="form-group">
                <label>Email address</label>

                <div class="form-element">
                    <input type="email" name="user[email]" value="" required>
                    <i class="fa fa-at form-element-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Name</label>
                <div class="form-element">
                    <input type="text" name="user[name]" value="">
                    <i class="fa fa-user form-element-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Company Name <span class="muted pull-right">(optional)</span></label>
                <div class="form-element">
                    <input type="text" name="user[company]" value="">
                    <i class="fa fa-building form-element-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Country</label>
                <select name="user[country]" id="country-input">
                    @foreach(Countries::all() as $code => $country)
                    <option value="{{ $code }}">{{ $country }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group eu-only" style="display: none;">
                <label>VAT Number</label>
                <input type="text" name="user[vat_number]" value="" />
            </div>

            <div class="form-group">
                <input type="submit" value="Proceed to payment" class="button">
            </div>
        </div>
        <!-- / Step -->

        <!-- Step 3: Payment -->
        <div class="step" style="hide-if-js">
            <div class="errors"></div>

            <div class="well small-margin">

                <div class="form-group">
                    <label>Credit Card Number</label>

                    <div class="form-element">
                        <input type="text" data-stripe="number" placeholder="**** **** **** ****">
                        <i class="fa fa-credit-card form-element-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Expiration</label>
                    <select data-stripe="exp_month" style="width: 80px; display: inline;">
                        <option disabled>Month</option>
                        @for ($i = 1; $i <= 12; $i++)
                        <option>{{ $i }}</option>
                        @endfor
                    </select>

                    <select data-stripe="exp_year" style="width: 80px; display: inline;">
                        <option disabled>Year</option>
                        @for ($i = 0; $i < 10; $i++)
                        <option value="{{ date('Y') + $i }}">{{ date('y') + $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group">
                    <label>CVC</label>

                    <div class="form-element" style="width: 120px;">
                        <input type="text" data-stripe="cvc">
                        <i class="fa fa-lock form-element-icon"></i>
                    </div>

                </div>

                <p class="muted" style="font-style: italic;">This is a 256bit SSL encrypted payment. Your credit card is safe.</p>

            </div>


            <div class="form-group">
                <input type="submit" value="Complete purchase" name="submit_button" />
            </div>

            <input type="hidden" name="token" value="" />

        </div>
        <!-- / Step -->

    </form>
</div>
@stop

@section('foot')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>

    function total(amount, interval) {
        var isYearly = interval === 'year';
        var price = isYearly ? 50 : 5;
        var total = amount * price;
        var discount = amount > 5 ? 30 : amount > 1 ? 20 : 0;
        if( discount > 0 ) {
            total = total * ( ( 100 - discount ) / 100 );
        }

        var elements = document.querySelectorAll('.total');
        [].forEach.call(elements,function(el) {
            el.innerHTML = '$' + total + ( isYearly ? ' per year' : ' per month' );
        });
    }

    // steps
    function Steps( selector ) {
        this.toggle = function() {
            // toggle step visibility
            [].forEach.call(elements,function(el) {
                el.style.display = ( el === elements[ currentStep ] ) ? '' : 'none';
            });

            // focus on first input
           var firstInput = elements[currentStep].querySelector('input');
            if( firstInput ) {
                firstInput.focus();
            }

            // highlight links to this step
           var links = document.querySelectorAll('[data-step]');
            [].forEach.call(links, function(el) {
                el.className = el.className.replace('current','');

                if( el.getAttribute('data-step') == ( currentStep + 1 ) ) {
                    el.className += " current";
                }
            })
        };
        this.next = function() {
            currentStep++;
            this.toggle();
        };
        this.previous = function() {
            currentStep--;
            this.toggle();
        };
        this.go = function(step) {
            currentStep = step-1;
            this.toggle();
        };

        this.done = function() {
            return ( currentStep + 1 ) == elements.length;
        };

        var elements = document.querySelectorAll(selector);
        var currentStep = 0;

        this.toggle();
    }

    function toggleEuFields() {
        var euCountries = [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' ];
        var isEu = euCountries.indexOf(countryElement.value.toUpperCase()) > -1;

        [].forEach.call(euOnlyFields, function(el) {
            el.style.display = isEu ? '' : 'none';
        });
    }

    function error(msg) {
        var errorElement = form.querySelector('.errors');
        errorElement.className += " notice notice-warning";
        errorElement.innerText = msg;
    }

    var form  = document.getElementById('buy-form');
    var euOnlyFields = document.querySelectorAll('.eu-only');
    var countryElement = document.getElementById('country-input');

    form.addEventListener('change', function(event) {
        total(this.quantity.value, this.interval.value);
        toggleEuFields();
    });

    form.addEventListener( 'submit', function(event) {

        // if we're not at last step yet, proceed one step.
        if( ! steps.done() ) {
            event.preventDefault();
            steps.next();
            return false;
        }

        // TODO: Validate all other fields (preferably per step)

        event.preventDefault();

        // soft-validate credit card
        var creditCardNumber = form.querySelector('[data-stripe="number"]').value;
        if( ! Stripe.card.validateCardNumber(creditCardNumber) ) {
            error( "That credit card number doesn't seem right.")
            return false;
        }

        // disable button
        var submitButton = form.elements.namedItem('submit_button');
        var buttonText = submitButton.value;

        submitButton.disabled = true;
        submitButton.value = "Please wait";

        Stripe.card.createToken(this, function(status, response) {

            // re-enable button
            submitButton.value = buttonText;
            submitButton.removeAttribute('disabled');

            if (response.error) {
                error(response.error.message);
            } else {
                form.elements.namedItem('token').value = response.id;
                form.submit();
            }
        });

        event.preventDefault();
        return false;
    });

    var steps = new Steps('.step');

    Stripe.setPublishableKey('{{ config('services.stripe.key') }}');

    // try to get country from ipinfo.io
    var req = new XMLHttpRequest();
    req.onreadystatechange = function() {
        if (req.readyState != XMLHttpRequest.DONE || req.status != 200) {
            return;
        }

       var res = JSON.parse(req.responseText);
        document.getElementById('country-input').value = res.country;
    };
    req.open("GET", "http://ipinfo.io");
    req.setRequestHeader('Accept','application/json');
    req.send();

</script>
@stop
