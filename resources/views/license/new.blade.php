@extends('layouts.master')

@section('title','Buy - Boxzilla')

@section('content')
<div class="container">

    <div class="breadcrumbs bordered small-padding">
        <a href="/">Account</a> &rightarrow; <a href="/licenses">Licenses</a> &rightarrow; New
    </div>

    <h1 class="page-title">Get a new license</h1>

    <noscript>
        Please enable JavaScript for a better experience.
    </noscript>

    @include('partials.form-messages')

    <form method="post" id="form-new-license" data-pricing="true">

        {!! csrf_field() !!}

        <div class="">

            <h3>License details</h3>

            <div class="form-group">
                <label class="control-label">How many site activations do you need?</label>
                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', '1') }}" step="1" min="1" required />
            </div>

            <div class="form-group radio">
                <label class="control-label">Would you like to pay monthly or yearly?</label>

                <label class="unstyled"><input type="radio" name="interval" value="month" @if( old('interval', 'month') == 'month' ) checked @endif required> Monthly</label>
                <label class="unstyled"><input type="radio" name="interval" value="year" @if( old('interval', 'month') == 'year' ) checked @endif> Yearly</label>
            </div>

            <p>Your card ending in <strong>{{ Auth::user()->card_last_four }}</strong> (<a href="/edit/payment">edit</a>) will be charged <span class="price strong">$6 per month</span>.</p>

            <div class="form-group">
                <input type="submit" value="Purchase" class="btn btn-primary">
            </div>

        </div>

    </form>

</div>
@stop