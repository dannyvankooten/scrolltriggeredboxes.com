@extends('layouts.master')

@section('title','Edit payment method - Boxzilla')

@section('content')

<div class="container">

    <ul class="nav nav-inline bordered">
        <li><a href="/edit">Billing Info</a></li>
        <li><a href="/edit/payment">Payment Method</a></li>
    </ul>

    <h1 class="page-title">Update Payment Method</h1>

    @if (session('message'))
    <div class="notice notice-success">
        {!! session('message') !!}
    </div>
    @endif

    <form method="post" id="cc-form">

        @if(Auth::user()->card_last_four)
           <p>You have registered your card ending in {{ Auth()->user()->card_last_four }}.</p>
           <p>Use the following form if you want to use a different credit card.</p>
        @endif

        <div class="errors"></div>

        <div class="form-group">
            <label>Credit Card Number</label>
            <input type="text" data-stripe="number">
        </div>

        <div class="form-group">
            <label>CVC</label>
            <input type="text" data-stripe="cvc">
        </div>

        <div class="form-group">
            <label>Expiration MM/YY</label>
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
            <input type="submit" value="Save" />
        </div>

        <input type="hidden" name="token" value="" />
    </form>

    <p>
        <a href="javascript:history.go(-1);">&lsaquo; Go back</a>
    </p>


</div>
@stop

@section('foot')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script>
        Stripe.setPublishableKey('{{ config('services.stripe.key') }}');
        var form  = document.getElementById('cc-form');

        form.addEventListener('change', function(event) {
            total(this.quantity.value, this.interval.value);
        });

        form.addEventListener( 'submit', function(event) {
            // todo: validate inputs

            Stripe.card.createToken(this, function(status, response) {
                if (response.error) {
                    var errorElement = form.querySelector('.errors');
                    errorElement.className += " notice notice-warning";
                    errorElement.innerText = response.error.message;
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

