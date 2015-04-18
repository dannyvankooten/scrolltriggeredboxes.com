@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')

    <hr class="header-divider">

    <div class="container bodyContent">
        <div class="content">

            <h3>Login</h3>

            <p>Please login using the email address that you used when <a href="/pricing">purchasing your plan</a>.</p>

            @foreach($errors->all() as $error)
                <p class="padding bg-warning">{{ $error }}</p>
            @endforeach

            <form method="post" action="/login">
                <div class="form-group">
                    <label for="loginInputEmail">Email address</label>
                    <input type="email" name="email" class="form-control" id="loginInputEmail" placeholder="Enter email">
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
                <button type="submit" class="btn btn-default">Login</button>
            </form>
        </div>
    </div>
@stop
