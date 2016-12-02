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

            <div class="form-group radio">
                <label class="control-label">Select your plan?</label>

                <label class="unstyled">
                    <input type="radio" name="plan" value="personal" @if( old('plan', 'personal') == 'personal' ) checked @endif required>
                    Personal <small>- up to 2 sites</small>
                </label>
                <label class="unstyled">
                    <input type="radio" name="plan" value="developer" @if( old('plan') == 'developer' ) checked @endif>
                    Developer <small>- up to 10 sites</small>
                </label>
            </div>

            <div class="form-group radio">
                <label class="control-label">Would you like to pay monthly or yearly?</label>

                <label class="unstyled"><input type="radio" name="interval" value="month" @if( old('interval', 'month') == 'month' ) checked @endif required> Monthly</label>
                <label class="unstyled"><input type="radio" name="interval" value="year" @if( old('interval', 'month') == 'year' ) checked @endif> Yearly</label>
            </div>

            <p>
                Your {!! $user->payment_method === 'stripe' ? 'card ending in <strong>'.$user->card_last_four.'</strong>' : 'PayPal account with email <strong>'. $user->paypal_email .'</strong>' !!} (<a href="/edit/payment">edit</a>) will be charged <span class="price strong">$6 per month</span>
                @if( $user->getTaxRate() > 0) <span>(excl. {{ $user->getTaxRate() }}% tax)</span> @endif
                .
            </p>

            <div class="form-group">
                <input type="submit" value="Purchase" class="btn btn-primary">
            </div>

        </div>

    </form>

</div>
@stop
