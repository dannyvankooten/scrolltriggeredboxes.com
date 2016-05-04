@extends('layouts.master')

@section('title','Register - Boxzilla')

@section('content')



<div class="container">

    <h1 class="page-title">Register</h1>

    <p>Registering for a Boxzilla+ account gives you instant access to all premium add-on plugins.</p>

    @include('partials.form-messages')

    <form method="post" id="buy-form" data-credit-card="true" data-pricing="true">
        {!! csrf_field() !!}

        <!-- Step 1: License -->

        <div class="step medium-margin">

            <h2 class="slashes">1. License</h2>

            <div class="form-group">
                <label class="control-label">How many site activations do you need?</label>
                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', request('quantity', 1)) }}" step="1" min="1" required />
            </div>

            <div class="form-group radio">
                <label class="control-label">Would you like to pay monthly or yearly?</label>

                <label class="unstyled"><input type="radio" name="interval" value="month" {{ old('interval', request('interval', 'month')) === 'month' ? 'checked' : '' }}> Monthly</label>
                <label class="unstyled"><input type="radio" name="interval" value="year" {{ old('interval', request('interval')) === 'year' ? 'checked' : '' }}> Yearly <small class="muted">(2 free months)</small></label>
            </div>

            <p>You will be charged <span class="price strong">$6 per month</span>.</p>

        </div>
        <!-- / Step -->

        <!-- Step 2: Billing Info -->

        <div class="step medium-margin">

            <h2 class="slashes">2. Account & Billing Info</h2>

            <div class="form-group">
                <label>Name <span class="big red">*</span></label>

                <div class="form-element">
                    <input type="text" name="user[name]" value="{{ old('user.name', '' ) }}" placeholder="Your name.." data-stripe="name" required>
                    <i class="fa fa-user form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Email address <span class="big red">*</span></label>

                <div class="form-element">
                    <input type="email" name="user[email]" value="{{ old('user.email', '' ) }}" placeholder="Your email address.." required>
                    <i class="fa fa-at form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Password <span class="big red">*</span></label>

                <div class="form-element">
                    <input type="password" name="password" value=""  placeholder="Your password.." required minlength="6">
                    <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm password <span class="big red">*</span></label>

                <div class="form-element">
                    <input type="password" name="password_confirmation" value="" placeholder="Repeat your password.." required minlength="6">
                    <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Country <span class="big red">*</span></label>
                <select name="user[country]" class="country-input" data-stripe="address_country" required>
                    <option value="" disabled {{ old('user.country','') === '' ? 'selected' : '' }}>Select your country..</option>
                    @foreach(Countries::all() as $code => $country)
                    <option value="{{ $code }}" {{ old('user.country') == $code ? 'selected' : '' }}>{{ $country }}</option>
                    @endforeach
                </select>
                <p class="help">We need to know your country for taxes.</p>
            </div>

            <div class="form-group europe-only" style="display: none;">
                <label>VAT Number <span class="small pull-right muted">(optional)</span></label>
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
                    <label>Credit Card Number <span class="big red">*</span></label>

                    <div class="form-element">
                        <input type="text" data-stripe="number" placeholder="**** **** **** ****" required>
                        <i class="fa fa-credit-card form-element-icon" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Expiration <span class="big red">*</span></label>
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
                    <label>CVC <span class="big red">*</span></label>

                    <div class="form-element" style="width: 120px;">
                        <input type="password" data-stripe="cvc" maxlength="4" required>
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
    Stripe.setPublishableKey('{{ config('services.stripe.key') }}');
</script>
@stop
