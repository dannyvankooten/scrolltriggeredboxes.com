@extends('layouts.master')

@section('title','Account - Boxzilla')

@section('content')

    <div class="container">

        @if (session('message'))
            <div class="notice notice-success">
                {!! session('message') !!}
            </div>
        @endif

        <p>Welcome, <strong>{{ $user->name }}</strong>.</p>
        <p>Your last login was on ...</p>

        <h4>Actions</h4>
        <ul>
            <li><a href="/licenses">View your licenses</a></li>
            <li><a href="/edit">Edit billing information</a></li>
            <li><a href="/edit/payment">Edit payment method</a></li>
            <li><a href="/plugins">Download plugins</a></li>
            <li><a href="/licenses/new">Purchase a new license</a></li>
        </ul>

        <p>Alternatively, <a href="/logout">click here to log out</a>.</p>


    </div>
@stop
