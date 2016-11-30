@extends('layouts.master')

@section('title','Edit payment method - Boxzilla')

@section('content')

<div class="container">

    <nav class="nav medium-margin">
        <strong style="margin-right: 10px;">Edit: </strong>
        <a href="/edit" class="">Account Info</a> <span class="sep"></span>
        <a href="/edit/billing" class="">Billing Info</a> <span class="sep"></span>
        <a href="/edit/payment" class="strong">Payment Method</a>
    </nav>

    <h1 class="page-title">Update payment method</h1>

    @if(Auth::user()->card_last_four)
        <p>You have registered your card ending in <strong>{{ Auth()->user()->card_last_four }}</strong>.</p>
        <p>Use the following form if you want to use a different credit card.</p>
    @endif

    @if( $user->payment_method === 'paypal' )
        <p>You are currently paying by PayPal.</p>
        <p>When purchasing a new license, you will be asked to log-in to your PayPal account.</p>
    @endif

    @include('partials.form-messages')



    <div class="well small-margin">
        <noscript>Please enable JavaScript to update your credit card.</noscript>
        <form method="post" id="cc-form" data-credit-card="true" class="hide-if-no-js">
            {!! csrf_field() !!}

            <div class="errors"></div>

            <div class="form-group">
                <label>Pay with</label>
                <div class="row clearfix ">
                    <div class="col col3-">
                        <label class="unstyled">
                            <input type="radio" name="payment_method" value="credit-card" {{ $user->payment_method === 'credit-card' ? 'checked' : '' }}> <i class="fa fa-credit-card" aria-hidden="true"></i> Credit card
                        </label>
                    </div>
                    <div class="col col3-">
                        <label class="unstyled">
                            <input type="radio" name="payment_method" value="paypal" {{ $user->payment_method === 'paypal' ? 'checked' : '' }}> <i class="fa fa-paypal" aria-hidden="true"></i> PayPal
                        </label>
                    </div>
                </div>
            </div>

            <!-- Start credit card fields -->
            <div data-show-if="payment_method:credit-card">
                <div class="row clearfix">
                    <div class="col col-5">
                        <div class="form-group">
                            <label for="creditCardNumberInput">Credit Card Number <span class="big red">*</span></label>

                            <div class="form-element">
                                <input type="text" data-stripe="number" placeholder="**** **** **** ****" id="creditCardNumberInput">
                                <i class="fa fa-credit-card form-element-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row clearfix">
                    <div class="col col-3">
                        <div class="form-group">
                            <label for="expMonthInput">Expiration <span class="big red">*</span></label>
                            <select data-stripe="exp_month" style="width: 80px; display: inline;" id="expMonthInput">
                                <option disabled value="" selected>Month</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option>{{ $i }}</option>
                                @endfor
                            </select>

                            <select data-stripe="exp_year" style="width: 80px; display: inline;">
                                <option disabled value="" selected>Year</option>
                                @for ($i = 0; $i < 10; $i++)
                                    <option value="{{ date('Y') + $i }}">{{ date('y') + $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col col-2">
                        <div class="form-group">
                            <label for="cvcInput">CVC <span class="big red">*</span></label>

                            <div class="form-element stretch">
                                <input type="password" data-stripe="cvc" id="cvcInput" maxlength="4" placeholder="***">
                                <i class="fa fa-lock form-element-icon"></i>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <!-- / End credit card fields -->


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
        Stripe.setPublishableKey('{{ config('services.stripe.key') }}');
    </script>
@stop


