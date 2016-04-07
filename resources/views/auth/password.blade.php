@extends('layouts.master')

@section('title','New Password - Boxzilla')

@section('content')

    <div class="container bodyContent">

            <h1 class="page-title">Request a new password</h1>

            <p>If you forgot your password then you can request a new one by filling out the form below.</p>

            <form  role="form" method="POST" action="{{ url('/password/email') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label class="control-label">Email Address</label>
                    <div class="form-element">
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Your email address..">
                        <i class="fa fa-at form-element-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Send Password Reset Link
                    </button>
                </div>
            </form>

            @if (session('status'))
                <div class="notice notice-success">
                    {{ session('status') }}
                </div>
            @endif

            @if (count($errors) > 0)
                <div class="notice notice-error">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
    </div>
@stop