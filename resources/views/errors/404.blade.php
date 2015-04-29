@extends('layouts.master')

@section('title', 'Page not found - Scroll Triggered Boxes')

@section('content')

    @include('parts.masthead')

    <div class="container">

        <h1>Oops! <small>404, page not found</small></h1>

        <p>
            We're sorry, but the requested page was not found! If you came here by clicking a link on the site, we've been notified and will fix it as soon as we can.
        </p>

        <p><a href="javascript:history.go(-1);">Click here to go back.</a></p>

    </div>
@stop
