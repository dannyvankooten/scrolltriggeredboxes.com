@extends('layouts.master')

@section('title', 'Page not found - Boxzilla')

@section('content')

    <div class="container">

        <h1>Oops! <small>Page not found.</small></h1>

        <p>
            We're sorry, but the requested page was not found! If you came here by clicking a link on the site, we've been automatically notified. We will see to it that this gets fixed.
        </p>

        <p><a href="javascript:history.go(-1);">Click here to go back.</a></p>

    </div>
@stop
