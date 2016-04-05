@extends('layouts.master')

@section('title','Buy - Boxzilla')

@section('content')
<div class="container">

    <h1>Get a new license</h1>

    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

    <div class="payment-errors"></div>

    <form method="post" id="buy-form">

        <div class="form-group">
            <label class="control-label">How many site activations?</label>
            <input type="number" name="quantity" class="form-control" value="1" step="1" />
        </div>

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

        <div class="form-group">
            <input type="submit" value="Pay" class="btn btn-primary">
        </div>

        <input type="hidden" name="stripe_token" value="" />
    </form>

    <script>
        Stripe.setPublishableKey('{{ config('services.stripe.key') }}');
        var form  = document.getElementById('buy-form');
        form.addEventListener( 'submit', function(event) {
            // todo: validate inputs

            Stripe.card.createToken(this, function(status, response) {
                if (response.error) {
                    form.querySelector('.payment-errors').innerText = response.error.message;
                } else {
                    form.elements.namedItem('stripe_token').value = response.id;
                    form.submit();
                }
            });

            event.preventDefault();
            return false;
        })
    </script>

</div>
@stop