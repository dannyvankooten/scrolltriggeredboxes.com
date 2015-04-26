@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')

    <hr class="header-divider">

    <div class="container bodyContent">
        <div class="content">

            <h3>Login to your account</h3>

            <p>The account area is where you can manage your license(s) and download the plugins. To access it, please login using the same email address as when purchasing the premium plan.</p>

            @foreach($errors->all() as $error)
                <p class="bs-callout bs-callout-warning">{{ $error }}</p>
            @endforeach

            <div class="well">
            <form method="post" action="{{ action('Auth\AuthController@postLogin')  }}">
                <div class="form-group">
                    <label for="loginInputEmail">Email address</label>
                    <input type="email" name="email" class="form-control" id="loginInputEmail" value="{{ Request::input('email') }}" placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label for="loginInputPassword">Password</label>
                    <input type="password" name="password" class="form-control" id="loginInputPassword" placeholder="Password">
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember_me" value="1"> Stay logged in?
                    </label>
                </div>
               <div>
                   <button type="submit" class="btn btn-default">Login</button> &nbsp; <a href="{{ url('/password/email') }}">Forgot your password?</a>
               </div>
            </form>
            </div>

            <p>No account yet? <a href="{{ url('/pricing') }}">Purchase one of the premium plans</a> to get instant access to <a href="{{ url('/plugins') }}">all premium plugins</a>.</p>
        </div>
    </div>
@stop
