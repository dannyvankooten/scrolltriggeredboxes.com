@extends('layouts.master')

@section('title','Reset Password - Boxzilla')

@section('content')

    <div class="container medium-margin">

            <h3>Reset Password</h3>
            <p>Now, please enter your new password and click <strong>Reset Password</strong></strong>.</p>

            @include('partials.form-messages')

            <form role="form" method="POST" action="{{ url('/password/reset') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label class="control-label">Email address</label>
                    <div class="form-element">
                        <input type="email" class="form-control" name="email" value="{{ Request::input('email') }}" >
                        <i class="fa fa-at form-element-icon"></i>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-2">
                        <div class="form-group">
                            <label class="control-label">Password</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                    </div>
                    <div class="col col-2">
                        <div class="form-group">
                            <label class="control-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation">
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Reset Password
                    </button>
                </div>

                <p class="text-muted">If you don't want to reset your password, please just leave this page. <strong>:)</strong></p>
            </form>
    </div>
@stop
