@extends('layouts.master')

@section('title','Edit account info - Boxzilla')

@section('content')

<div class="container">

    <nav class="nav medium-margin">
        <strong style="margin-right: 10px;">Edit: </strong>
        <a href="/edit" class="strong">Account Info</a> <span class="sep"></span>
        <a href="/edit/billing" class="">Billing Info</a> <span class="sep"></span>
        <a href="/edit/payment">Payment Method</a>
    </nav>

    <h1 class="page-title">Update account info</h1>

    @include('partials.form-messages')

    <form method="post" id="account-info-form">
        {!! csrf_field() !!}

        <div class="form-group">
            <label for="emailAddressInput">Email address <span class="big red">*</span></label>

            <div class="form-element">
                <input type="email" name="user[email]" value="{{ old('user.email', $user->email ) }}" id="emailAddressInput" required>
                <i class="fa fa-at form-element-icon" aria-hidden="true"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="passwordInput">Current password <span class="big red">*</span></label>

            <div class="form-element">
                <input type="password" name="current_password" value="" id="passwordInput" required>
                <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
            </div>
        </div>

       <div class="row clearfix">
           <div class="col col-3">
               <div class="form-group">
                   <label for="newPasswordInput">New password <span class="muted pull-right">(optional)</span></label>

                   <div class="form-element">
                       <input type="password" name="new_password" value="" minlength="6" id="newPasswordInput">
                       <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                   </div>
               </div>
           </div>
           <div class="col col-3">

               <div class="form-group">
                   <label for="newPasswordConfirmationInput">Confirm new password <span class="muted pull-right">(optional)</span></label>

                   <div class="form-element">
                       <input type="password" name="new_password_confirmation" minlength="6" value="" id="newPasswordConfirmationInput">
                       <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                   </div>
               </div>
           </div>
       </div>

        <div class="form-group">
            <input type="submit" value="Save Changes" />
        </div>

        <p>
        <a href="/">&leftarrow; Go back</a>
    </p>
    </form>

</div>
@stop