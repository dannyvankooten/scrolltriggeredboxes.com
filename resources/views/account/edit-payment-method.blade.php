@extends('layouts.master')

@section('title','Edit payment method - Boxzilla')

@section('content')

<div class="container">

    <div class="breadcrumbs bordered small-padding">
        <a href="/">Account</a> &rightarrow; Edit
    </div>

    <div class="small-padding bordered small-margin">
        <ul class="nav nav-inline no-margin">
            <li><strong>Edit: </strong></li>
            <li><a href="/edit" class="">Account Info</a></li>
            <li><a href="/edit/billing" class="">Billing Info</a></li>
            <li><a href="/edit/payment" class="strong">Payment Method</a></li>
        </ul>
    </div>

    <h1 class="page-title">Update Payment Method</h1>

    @if(Auth::user()->card_last_four)
        <p>You have registered your card ending in {{ Auth()->user()->card_last_four }}.</p>
        <p>Use the following form if you want to use a different credit card.</p>
    @endif

    @include('partials.form-messages')

    <div class="well small-margin">
        <noscript>Please enable JavaScript to update your credit card.</noscript>
        <form method="post" id="cc-form" class="hide-if-no-js">
            {!! csrf_field() !!}

            <div class="card-errors"></div>

            <div class="form-group">
                <label>Credit Card Number</label>

                <div class="form-element">
                    <input type="text" data-stripe="number" placeholder="**** **** **** ****" required>
                    <i class="fa fa-credit-card form-element-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Expiration</label>
                <select data-stripe="exp_month" style="width: 80px; display: inline;" required>
                    <option disabled value="" selected>Month</option>
                    @for ($i = 1; $i <= 12; $i++)
                    <option>{{ $i }}</option>
                    @endfor
                </select>

                <select data-stripe="exp_year" style="width: 80px; display: inline;">
                    <option disabled value="" selected required>Year</option>
                    @for ($i = 0; $i < 10; $i++)
                    <option value="{{ date('Y') + $i }}">{{ date('y') + $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="form-group">
                <label>CVC</label>

                <div class="form-element" style="width: 120px;">
                    <input type="text" data-stripe="cvc" required maxlength="4">
                    <i class="fa fa-lock form-element-icon"></i>
                </div>

            </div>


            <div class="form-group">
                <input type="submit" value="Save Changes" />
            </div>

            <input type="hidden" name="payment_token" value="" />
            <input type="hidden" name="user[card_last_four]" value="" />
        </form>
    </div>


<p>
        <a href="javascript:history.go(-1);">&lsaquo; Go back</a>
    </p>


</div>
@stop

@section('foot')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script>

        function error(msg) {
            var errorElement = form.querySelector('.card-errors');
            errorElement.className += " notice notice-warning";
            errorElement.innerText = msg;
        }

        Stripe.setPublishableKey('{{ config('services.stripe.key') }}');
        var form  = document.getElementById('cc-form');

        form.addEventListener('change', function(event) {
            total(this.quantity.value, this.interval.value);
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
    </script>
@stop

