@extends('layouts.master')

@section('title','Edit billing information - Boxzilla')

@section('subnav')


@endsection

@section('content')

    <div class="container">

        <nav class="nav medium-margin">
            <strong style="margin-right: 10px;">Edit: </strong>
            <a href="/edit" class="">Account Info</a> <span class="sep"></span>
            <a href="/edit/billing" class="strong">Billing Info</a> <span class="sep"></span>
            <a href="/edit/payment">Payment Method</a>
        </nav>

        <h1 class="page-title">Update billing information</h1>

        @include('partials.form-messages')

        <form method="post" id="billing-info-form">
            {!! csrf_field() !!}

            <div class="form-group">
                <label for="nameInput">Name <span class="big red">*</span></label>
                <div class="form-element">
                    <input type="text" name="user[name]" value="{{ old('user.name', $user->name ) }}" id="nameInput" required>
                    <i class="fa fa-user form-element-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="addressInput">Address</label>
                <div class="form-element">
                    <input type="text" name="user[address]" value="{{ old('user.address', $user->address  ) }}" placeholder="Address line 1" id="addressInput">
                </div>
            </div>

            <div class="row clearfix">
                <div class="col col-3">
                    <div class="form-group">
                        <label for="cityInput">City</label>
                        <div class="form-element">
                            <input type="text" name="user[city]" value="{{ old('user.city', $user->city  ) }}" placeholder="City" id="cityInput">
                        </div>
                    </div>
                </div>
                <div class="col col-3">
                    <div class="form-group">
                        <label for="postalCodeInput">ZIP / Postal code</label>
                        <div class="form-element">
                            <input type="text" name="user[zip]" value="{{ old('user.zip', $user->zip  ) }}" placeholder="ZIP or Postal Code" id="postalCodeInput">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row clearfix">
                <div class="col col-3">
                    <div class="form-group">
                        <label for="countryInput">Country <span class="big red">*</span></label>
                        <select name="user[country]" class="country-input" id="countryInput" required>
                            <option value="" disabled {{ old('user.country','') === '' ? 'selected' : '' }}>Select your country..</option>
                            @foreach(Countries::all() as $code => $country)
                                <option value="{{ $code }}" {{ old('user.country', $user->country ) == $code ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col col-3">
                    <div class="form-group">
                        <label for="stateInput">State / Province</label>
                        <div class="form-element">
                            <input type="text" name="user[state]" placeholder="State" value="{{ old('user.state', $user->state  ) }}" id="stateInput">
                        </div>
                    </div>
                </div>
            </div>

            <div class="europe-only"  style="display: none;">
                <p class="help">If you're buying as a Europe based company, please enter your company details below.</p>
                <div class="row europe-only clearfix">

                    <div class="col col-3">
                        <div class="form-group">
                            <label for="companyNameInput">Company Name <span class="small muted pull-right">(optional)</span></label>
                            <div class="form-element">
                                <input type="text" name="user[company]" value="{{ old('user.company', $user->company ) }}" placeholder="Company Name" id="companyNameInput">
                                <i class="fa fa-building form-element-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col col-3">
                        <div class="form-group">
                            <label for="vatNumberInput">VAT Number <span class="small pull-right muted">(optional)</span></label>
                            <input type="text" name="user[vat_number]" value="{{ old('user.vat_number', '') }}" placeholder="VAT Number" id="vatNumberInput" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="Save Changes" />
            </div>

        </form>

        @if($user->isEligibleForTax())
            <p>Given the current information, your VAT rate is <strong>{{ $user->getTaxRate() }}%</strong>.</p>
        @endif

        <p>
            <a href="javascript:history.go(-1);">&lsaquo; Go back</a>
        </p>


    </div>
@stop

@section('foot')

@stop

