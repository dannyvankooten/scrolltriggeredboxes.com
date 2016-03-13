@extends('layouts.master')

@section('title','New Password - Scroll Triggered Boxes')

@section('content')

    <hr class="header-divider">

    <div class="container bodyContent">
        <div class="content col-lg-offset-1 col-lg-10">

            <h3>Request a new password</h3>

            <p>If you forgot your password then you can request a new one by filling out the form below. Please use the same email address as when you <a href="{{ domain_url( '/pricing' ) }}">purchased your premium plan</a>.</p>

            <form  role="form" method="POST" action="{{ url('/password/email') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label class="control-label">E-Mail Address</label>
                    <div class="input-group">
                        <span class="input-group-addon">@</span>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Send Password Reset Link
                    </button>
                </div>
            </form>

            @if (session('status'))
                <div class="bs-callout bs-callout-success">
                    {{ session('status') }}
                </div>
            @endif

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