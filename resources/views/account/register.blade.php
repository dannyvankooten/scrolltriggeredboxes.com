@extends('layouts.master')

@section('title','Register - Boxzilla')

@section('content')



<div class="container">

    <h1 class="page-title">Register</h1>

    @include('partials.form-messages')

    <form method="post" id="buy-form">
        {!! csrf_field() !!}

        <!-- Step 1: License -->

        <div class="step medium-margin">

            <h2 class="slashes">1. License</h2>

            <div class="form-group">
                <label class="control-label">How many site activations do you need?</label>
                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" step="1" min="1" required />
            </div>

            <div class="form-group radio">
                <label class="control-label">Would you like to pay monthly or yearly?</label>

                <label class="unstyled"><input type="radio" name="interval" value="month" {{ old('interval', 'month') === 'month' ? 'checked' : '' }}> Monthly</label>
                <label class="unstyled"><input type="radio" name="interval" value="year" {{ old('interval') === 'year' ? 'checked' : '' }}> Yearly</label>
            </div>

            <p>You will be charged <span class="total strong">$10 per month</span>.</p>

        </div>
        <!-- / Step -->

        <!-- Step 2: Billing Info -->

        <div class="step medium-margin">

            <h2 class="slashes">2. Account & Billing Info</h2>

            <div class="form-group">
                <label>Name</label>

                <div class="form-element">
                    <input type="text" name="user[name]" value="{{ old('user.name', '' ) }}" required>
                    <i class="fa fa-user form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Email address</label>

                <div class="form-element">
                    <input type="email" name="user[email]" value="{{ old('user.email', '' ) }}" required>
                    <i class="fa fa-at form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>

                <div class="form-element">
                    <input type="password" name="password" value="" required minlength="6">
                    <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm password</label>

                <div class="form-element">
                    <input type="password" name="password_confirmation" value="" required minlength="6">
                    <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Country</label>
                <select name="user[country]" id="country-input" data-stripe="address_country" required>
                    <option value="" disabled {{ old('user.country','') === '' ? 'selected' : '' }}>Select your country..</option>
                    @foreach(Countries::all() as $code => $country)
                    <option value="{{ $code }}" {{ old('user.country') == $code ? 'selected' : '' }}>{{ $country }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group eu-only" style="display: none;">
                <label>VAT Number</label>
                <input type="text" name="user[vat_number]" value="{{ old('user.vat_number', '') }}" />
                <p class="help">If you're buying as a Europe based company, please enter your company VAT number here.</p>
            </div>

        </div>
        <!-- / Step -->

        <!-- Step 3: Payment -->

        <div class="step medium-margin">

            <h2 class="slashes">3. Payment</h2>

            <div class="errors"></div>

            <div class="well small-margin">

                <div class="form-group">
                    <label>Credit Card Number</label>

                    <div class="form-element">
                        <input type="text" data-stripe="number" placeholder="**** **** **** ****" required>
                        <i class="fa fa-credit-card form-element-icon" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Expiration</label>
                    <select data-stripe="exp_month" style="width: 80px; display: inline;" required>
                        <option value="" disabled selected>Month</option>
                        @for ($i = 1; $i <= 12; $i++)
                        <option>{{ $i }}</option>
                        @endfor
                    </select>

                    <select data-stripe="exp_year" style="width: 80px; display: inline;" required>
                        <option value="" disabled selected>Year</option>
                        @for ($i = 0; $i < 10; $i++)
                        <option value="{{ date('Y') + $i }}">{{ date('y') + $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group">
                    <label>CVC</label>

                    <div class="form-element" style="width: 120px;">
                        <input type="text" data-stripe="cvc" maxlength="4" required>
                        <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                    </div>

                </div>

                <p class="muted" style="font-style: italic;">This is a 256bit SSL encrypted payment. Your credit card is safe.</p>

            </div>


            <div class="form-group">
                <input type="submit" value="Complete purchase" name="submit_button" />
            </div>

            <input type="hidden" name="payment_token" value="" />
            <input type="hidden" name="user[card_last_four]" value="" />

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

        event.preventDefault();

        // soft-validate credit card
        var creditCardNumber = form.querySelector('[data-stripe="number"]').value;
        if( ! Stripe.card.validateCardNumber(creditCardNumber) ) {
            error( "That credit card number doesn't seem right.");
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
                form.elements.namedItem('user[card_last_four]').value = response.card.last4;
                form.elements.namedItem('payment_token').value = response.id;
                form.submit();
            }
        });

        return false;
    });

    //var steps = new Steps('.step');

    Stripe.setPublishableKey('{{ config('services.stripe.key') }}');

    // try to get country from ipinfo.io
    if( countryElement.value === '' ) {
        var req = new XMLHttpRequest();
        req.onreadystatechange = function() {
            if (req.readyState != XMLHttpRequest.DONE || req.status != 200) {
                return;
            }

            var res = JSON.parse(req.responseText);
            countryElement.value = res.country;
            toggleEuFields();
        };
        req.open("GET", "http://ipinfo.io");
        req.setRequestHeader('Accept','application/json');
        req.send();
    }


</script>
@stop
