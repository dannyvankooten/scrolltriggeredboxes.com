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

    @if( $user->payment_method === 'stripe' && $user->card_last_four)
        <p>You have registered your card ending in {{ $user->card_last_four }}.</p>
    @endif

    <p>Use the following form if you want to use a different payment method or card.</p>

    @include('partials.form-messages')

    <div class="well small-margin">
        <noscript>Please enable JavaScript to update your credit card.</noscript>
        <form method="post" id="payment-method-form" data-credit-card="true" class="hide-if-no-js">
            {!! csrf_field() !!}

            <div class="errors"></div>

            <div class="form-group">
                <label>Pay with</label>
                <div class="row clearfix ">
                    <div class="col col3-">
                        <label class="unstyled">
                            <input type="radio" name="payment_method" value="stripe" {{ $user->payment_method === 'stripe' ? 'checked' : '' }}> <i class="fa fa-credit-card" aria-hidden="true"></i> Credit card
                        </label>
                    </div>
                    <div class="col col3-">
                        <label class="unstyled">
                            <input type="radio" name="payment_method" value="braintree" {{ $user->payment_method === 'braintree' ? 'checked' : '' }}> <i class="fa fa-paypal" aria-hidden="true"></i> PayPal
                        </label>
                    </div>
                </div>
            </div>

            <!-- Start credit card fields -->
            <div data-show-if="payment_method:stripe">
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

    <!-- Load the client component. -->
    <script src="https://js.braintreegateway.com/web/3.6.0/js/client.min.js"></script>
    <script src="https://js.braintreegateway.com/web/3.6.0/js/paypal.min.js"></script>

    <script>
        var form = document.getElementById('payment-method-form');
        var submitButton = form.querySelector('[type="submit"]');
        var paypal,
            initialPaymentMethod = form.elements.namedItem('payment_method').value;


        // Create a client.
        braintree.client.create({
            authorization: '{{ $braintreeAgent->generateClientToken() }}'
        }, function (clientErr, clientInstance) {
            if (clientErr) {
                console.error('Error creating client:', clientErr);
                return;
            }

            // Create a PayPal component.
            braintree.paypal.create({
                client: clientInstance
            }, function (paypalErr, paypalInstance) {
                if (paypalErr) {
                    console.error('Error creating PayPal:', paypalErr);
                    return;
                }

                paypal = paypalInstance;
            });
        });

        // When the button is clicked, attempt to tokenize.
        form.addEventListener('submit', function (event) {
            var chosenPaymentMethod = form.elements.namedItem('payment_method').value;
            if(chosenPaymentMethod !== 'braintree' || initialPaymentMethod === chosenPaymentMethod) {
                return;
            }

            event.preventDefault();
            submitButton.disabled = true;

            // Because tokenization opens a popup, this has to be called as a result of
            // customer action, like clicking a button—you cannot call this at any time.
            paypal.tokenize({
                flow: 'vault'
            }, function (tokenizeErr, payload) {
                // Stop if there was an error.
                if (tokenizeErr) {
                    if (tokenizeErr.type !== 'CUSTOMER') {
                        console.error('Error tokenizing:', tokenizeErr);
                    }

                    // TODO: Handle errors gracefully.
                    submitButton.disabled = false;
                    return;
                }

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payment_token';
                input.value = payload.nonce;
                form.appendChild(input);
                form.submit();
            });

        });
    </script>
@endsection


