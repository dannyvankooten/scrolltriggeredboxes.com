@extends('layouts.master')

@section('title','Login - Boxzilla')

@section('content')

    <div class="container">

        <h1 class="page-title">Log in to your account</h1>
        <p>Please log in to download the add-on plugins and manage your Boxzilla license(s).</p>

        @include('partials.form-messages')

        <div class="well small-margin">
        <form method="post" action="/login">
            {!! csrf_field() !!}

            <div class="form-group">
                <label for="loginInputEmail">Email address</label>

                <div class="form-element">
                    <input type="email" name="email" class="form-control" id="loginInputEmail" value="{{ request('email', old('email')) }}" placeholder="Enter email">
                    <i class="fa fa-at form-element-icon"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="loginInputPassword">Password</label>
                <input type="password" name="password" class="form-control" id="loginInputPassword" placeholder="Password">
            </div>
            <div class="form-group">
                <label class="unstyled">
                    <input type="checkbox" name="remember_me" value="1"> Stay logged in?
                </label>
            </div>
           <div class="form-group no-margin">
               <button type="submit" class="btn btn-default">Login</button> &nbsp; <a href="{{ domain_url('/password/email', 'account') }}">Forgot your password?</a>
           </div>
        </form>
        </div>

        <div class="medium-margin">
            <h3>No account yet?</h3>
             <p><a href="{{ domain_url( '/register', 'account' ) }}">Purchase a license</a> to get instant access to <a href="{{ domain_url('/add-ons') }}">all premium plugins</a>.</p>
        </div>
        </div>
@stop
