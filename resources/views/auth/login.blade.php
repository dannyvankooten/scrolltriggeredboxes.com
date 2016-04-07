@extends('layouts.master')

@section('title','Login - Boxzilla')

@section('content')

    <div class="container">

        <h1 class="page-title">Login to your account</h1>

        <p>The account area is where you manage your licenses and download all premium add-on plugins.</p>

        @foreach($errors->all() as $error)
            <p class="notice notice-error">{{ $error }}</p>
        @endforeach

        <div class="well small-margin">
        <form method="post" action="{{ action('Auth\AuthController@postLogin')  }}">
            <div class="form-group">
                <label for="loginInputEmail">Email address</label>

                <div class="form-element">
                    <input type="email" name="email" class="form-control" id="loginInputEmail" value="{{ Request::input('email') }}" placeholder="Enter email">
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
           <div class="form-group">
               <button type="submit" class="btn btn-default">Login</button> &nbsp; <a href="{{ domain_url('/password/email', 'account') }}">Forgot your password?</a>
           </div>
        </form>
        </div>

        <h3>No account yet?</h3>
        <p><a href="/register">Purchase a license</a> to get instant access to <a href="{{ domain_url('/plugins') }}">all premium plugins</a>.</p>
    </div>
@stop
