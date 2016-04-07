@extends('layouts.master')

@section('title','Edit billing information - Boxzilla')

@section('content')

<div class="container">

    <ul class="nav nav-inline bordered">
        <li><strong>Edit: </strong></li>
        <li><a href="/edit">Billing Info</a></li>
        <li><a href="/edit/payment">Payment Method</a></li>
    </ul>

    <h1 class="page-title">Update Billing Information</h1>

    @if (session('message'))
    <div class="notice notice-success">
        {!! session('message') !!}
    </div>
    @endif

    <form method="post">

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
            <label>Company Name <span class="muted pull-right">(optional)</span></label>
            <div class="form-element">
                <input type="text" name="user[company]" value="{{ $user->company }}">
                <i class="fa fa-building form-element-icon"></i>
            </div>
        </div>

        <div class="form-group">
            <label>Country</label>
            <select name="user[country]">
                @foreach(Countries::all() as $code => $country)
                <option value="{{ $code }}" @if($user->country == $code) selected="selected" @endif>{{ $country }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="@if(!$user->inEurope()) display: none; @endif">
            <label>VAT Number</label>
            <input type="text" name="user[vat_number]" value="{{ $user->vat_number }}" />
        </div>

        <div class="form-group">
            <input type="submit" value="Save" />
        </div>

    </form>

    <p>
        <a href="javascript:history.go(-1);">&lsaquo; Go back</a>
    </p>


</div>
@stop

@section('foot')

@stop

