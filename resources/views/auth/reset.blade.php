@extends('layouts.master')

@section('title','Reset Password - Scroll Triggered Boxes')

@section('content')

    <hr class="header-divider">

    <div class="container bodyContent">
        <div class="content col-lg-offset-1 col-lg-10">

            <h3>Reset Password</h3>
            <p>Now, please enter your new password and click <strong>Reset Password</strong></strong>.</p>

            <form role="form" method="POST" action="{{ url('/password/reset') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label class="control-label">Email address</label>
                    <div class="input-group">
                        <span class="input-group-addon">@</span>
                        <input type="email" class="form-control" name="email" value="{{ Request::input('email') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">Password</label>
                    <input type="password" class="form-control" name="password">
                </div>

                <div class="form-group">
                    <label class="control-label">Confirm Password</label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Reset Password
                    </button>
                </div>

                <p class="text-muted">If you don't want to reset your password, just leave this page. :)</p>
            </form>

            @if (count($errors) > 0)
                <div class="bs-callout bs-callout-warning">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@stop
