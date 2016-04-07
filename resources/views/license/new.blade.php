@extends('layouts.master')

@section('title','Buy - Boxzilla')

@section('content')
<div class="container">

    <div class="breadcrumbs bordered padded small-margin">
        <a href="/">Account</a> &rightarrow; <a href="/licenses">Licenses</a> &rightarrow; New
    </div>

    <h1 class="page-title">Get a new license</h1>

    <div class="payment-errors"></div>

    <form method="post" id="buy-form">

        <div class="">

            <h3>License details</h3>

            <div class="form-group">
                <label class="control-label">How many site activations do you need?</label>
                <input type="number" name="quantity" class="form-control" value="1" step="1" />
            </div>

            <div class="form-group radio">
                <label class="control-label">Would you like to pay monthly or yearly?</label>

                <label class="unstyled"><input type="radio" name="interval" value="month" checked> Monthly</label>
                <label class="unstyled"><input type="radio" name="interval" value="year"> Yearly</label>
            </div>

            <p>Your card ending in <strong>{{ Auth::user()->card_last_four }}</strong> (<a href="/edit/payment">edit</a>) will be charged <span class="total strong">$10 per month</span>.</p>

            <div class="form-group">
                <input type="submit" value="Pay" class="btn btn-primary">
            </div>

        </div>

    </form>

</div>
@stop

@section('foot')
<script>

    function total(amount, interval) {
        var isYearly = interval === 'year';
        var price = isYearly ? 50 : 5;
        var total = amount * price;
        total = price + ( ( amount - 1 ) * price * 0.5 );

        var elements = document.querySelectorAll('.total');
        [].forEach.call(elements,function(el) {
            el.innerHTML = '$' + total + ( isYearly ? ' per year' : ' per month' );
        });
    }

    total(1,'month');

    var form  = document.getElementById('buy-form');
    form.addEventListener('change', function(event) {
        total(this.quantity.value, this.interval.value);
    });

</script>
@stop