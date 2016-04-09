@extends('layouts.master')

@section('title','Edit billing information - Boxzilla')

@section('content')

<div class="container">

    <div class="breadcrumbs bordered padded">
        <a href="/">Account</a> &rightarrow; Edit
    </div>

    <ul class="nav nav-inline bordered">
        <li><strong>Edit: </strong></li>
        <li><a href="/edit">Billing Info</a></li>
        <li><a href="/edit/payment">Payment Method</a></li>
    </ul>

    <h1 class="page-title">Update Billing Information</h1>

    @if (count($errors) > 0)
    <div class="notice notice-warning">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="post" id="billing-info-form">

        <div class="form-group">
            <label>Email address</label>

            <div class="form-element">
                <input type="email" name="user[email]" value="{{ $user->email }}" required>
                <i class="fa fa-at form-element-icon"></i>
            </div>
        </div>

        <div class="form-group">
            <label>Name</label>
            <div class="form-element">
             <input type="text" name="user[name]" value="{{ $user->name }}">
                <i class="fa fa-user form-element-icon"></i>
            </div>
        </div>

        <div class="form-group">
            <label>Country</label>
            <select name="user[country]" id="country-input">
                @foreach(Countries::all() as $code => $country)
                <option value="{{ $code }}" @if($user->country == $code) selected="selected" @endif>{{ $country }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Company Name <span class="muted pull-right">(optional)</span></label>
            <div class="form-element">
                <input type="text" name="user[company]" value="{{ $user->company }}">
                <i class="fa fa-building form-element-icon"></i>
            </div>
        </div>

        <div class="form-group eu-only" style="@if(!$user->inEurope()) display: none; @endif">
            <label>VAT Number <span class="muted pull-right">(optional)</span></label>
            <input type="text" name="user[vat_number]" value="{{ $user->vat_number }}" />
        </div>

        <div class="form-group">
            <input type="submit" value="Save" />
        </div>

    </form>

    <p>Given the current information, your VAT rate is <strong>{{ $user->getTaxRate() }}%</strong>.</p>

    <p>
        <a href="javascript:history.go(-1);">&lsaquo; Go back</a>
    </p>


</div>
@stop

@section('foot')
    <script>
        function toggleFields() {
            var euCountries = [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' ];
            var isEu = euCountries.indexOf(countryElement.value.toUpperCase()) > -1;

            [].forEach.call(euOnlyFields, function(el) {
                el.style.display = isEu ? '' : 'none';
            });
        }

        var euOnlyFields = document.querySelectorAll('.eu-only');
        var countryElement = document.getElementById('country-input');
        var form = document.getElementById('billing-info-form');

        form.addEventListener('change', toggleFields);
        toggleFields();
    </script>
@stop

