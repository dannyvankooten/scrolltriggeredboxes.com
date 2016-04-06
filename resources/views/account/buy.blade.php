@extends('layouts.master')

@section('title','Buy - Boxzilla')

@section('content')
<div class="container">

    <h1>Get a new license</h1>

    <div class="payment-errors"></div>

    <form method="post" id="buy-form">

        <div class="step">

            <h3>License details</h3>

            <div class="form-group">
                <label class="control-label">How many site activations do you need?</label>
                <input type="number" name="quantity" class="form-control" value="1" step="1" />
            </div>

            <div class="form-group radio">
                <label class="control-label">Would you like to pay monthly or yearly?</label>

                <label><input type="radio" name="interval" value="monthly" checked> Monthly</label>
                <label><input type="radio" name="interval" value="yearly"> Yearly</label>
            </div>

            <p>You'll be paying <span class="total" style="font-weight: bold;">$10 per month</span>.</p>

            <button class="btn btn-primary" onclick="steps.next()">Proceed to payment</button>

        </div>

        <div class="step">

            <h3>Payment details</h3>

            @if(Auth::user()->stripe_customer_id)
                <div class="existing-cart-details">
                    <p>Your card ending in {{ Auth::user()->card_last_four }} will be charged <span class="total">$10 per month</span>.</p>
                    <div class="form-group">
                        <button onclick="showCartDetails()">Change payment method</button>
                    </div>
                </div>
            @endif

            <div class="cart-details" style="@if(Auth::user()->stripe_customer_id) display:none; @endif">
                <div class="form-group">
                    <label class="control-label">Credit Card Number</label>
                    <input type="text" data-stripe="number" class="form-control">
                </div>

                <div class="form-group">
                    <label class="control-label">CVC</label>
                    <input type="text" data-stripe="cvc" class="form-control">
                </div>

                <div class="form-group">
                    <label class="control-label">Expiration MM/YY</label>
                    <select data-stripe="exp_month" class="form-control" style="width: 80px; display: inline;">
                        <option disabled>Month</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option>{{ $i }}</option>
                        @endfor
                    </select>

                    <select data-stripe="exp_year" class="form-control" style="width: 80px; display: inline;">
                        <option disabled>Year</option>
                        @for ($i = 0; $i < 10; $i++)
                        <option value="{{ date('Y') + $i }}">{{ date('y') + $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="Pay" class="btn btn-primary">
                <a href="javascript:steps.previous()">Go back</a>
            </div>
        </div>

        <input type="hidden" name="token" value="" />
    </form>

</div>
@stop

@section('foot')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>

    // steps
    function Steps( selector ) {

        this.toggle = function() {
            [].forEach.call(elements,function(el) {
                el.style.display = ( el === elements[ currentStep ] ) ? '' : 'none';
            });
        };

        this.next = function() {
            currentStep++;
            this.toggle();
        };

        this.previous = function() {
            currentStep--;
            this.toggle();
        };

        var elements = document.querySelectorAll(selector);
        var currentStep = 0;
        this.toggle();
    }

    var steps = new Steps('.step');

    function total(amount, interval) {
        var isYearly = interval === 'yearly';
        var price = isYearly ? 50 : 5;
        var total = amount * price;

        var elements = document.querySelectorAll('.total');
        [].forEach.call(elements,function(el) {
            el.innerHTML = '$' + total + ( isYearly ? ' per year' : ' per month' );
        });
    }

    function showCartDetails() {
        document.querySelector('.cart-details').style.display = '';
        document.querySelector('.existing-cart-details').style.display = 'none';
    }

    Stripe.setPublishableKey('{{ config('services.stripe.key') }}');
    var form  = document.getElementById('buy-form');

    form.addEventListener('change', function(event) {
       total(this.quantity.value, this.interval.value);
    });

    form.addEventListener( 'submit', function(event) {
        // todo: validate inputs

        Stripe.card.createToken(this, function(status, response) {
            if (response.error) {
                form.querySelector('.payment-errors').innerText = response.error.message;
            } else {
                form.elements.namedItem('token').value = response.id;
                form.submit();
            }
        });

        event.preventDefault();
        return false;
    })
</script>
@stop