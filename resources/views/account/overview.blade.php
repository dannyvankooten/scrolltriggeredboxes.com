@extends('layouts.master')

@section('title','Account - Boxzilla')

@section('content')

    <div class="container">

        <p>Welcome, <strong>{{ $user->name }}</strong>.</p>
        <p>Your last login was on <em>{{ $user->last_login_at->format( 'F j, Y' ) }}</em> at <em>{{ $user->last_login_at->format( 'H:i' ) }}</em>.</p>

        <h4>Actions</h4>
        <ul>
            <li><a href="/plugins">Download plugins</a></li>
            <li><a href="/licenses">View your licenses</a></li>
            <li><a href="/payments">View your payments</a></li>
            <li><a href="/edit">Edit account & payment information</a></li>
            <li><a href="/licenses/new">Purchase a new license</a></li>
            <li><a href="mailto:support@boxzillaplugin.com">Contact support</a></li>
        </ul>

        <p>Alternatively, <a href="/logout">click here to log out</a>.</p>


    </div>
@stop
