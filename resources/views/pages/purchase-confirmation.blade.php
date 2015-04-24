@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')
    @include('parts.masthead')

    <div class="container content">
        <h1>Thank you!</h1>
        <p>Success! You just bought a premium license for Scroll Triggered Boxes.</p>
        <p>An email is sent your way containing your login credentials. Using that, you can login to the <a href="{{ url('/account') }}">account area</a> to view your license key and download the desired plugins.</p>
        <p>If you have any questions, please contact us at <a href="mailto:support@scrolltriggeredboxes.com">support@scrolltriggeredboxes.com</a>.</p>
    </div>
@stop;

