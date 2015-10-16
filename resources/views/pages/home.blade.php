@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')
    @include('parts.masthead')

    <div class="container marketing">

        <!-- START THE FEATURETTES -->

        <hr class="featurette-divider" style="margin-top: 0; border-top-color: transparent;">

        <div class="row featurette">
            <div class="col-md-5">
                <h2 class="featurette-heading">Attention grabbing boxes. <br /><span class="text-muted">At just the right time.</span></h2>
                <p class="lead">Using Scroll Triggered Boxes, you can ask your visitors to perform a certain action at just the right time.</p>
                <p class="lead">For example, slide in a sign-up form box once a visitor reaches the end of your post. Already engaged visitors are very likely to want more of your goodness.</p>
            </div>
            <div class="col-md-7">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/box.png') }}">
            </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
            <div class="col-md-5 col-md-push-7">
                <h2 class="featurette-heading">Your own call to actions. <span class="text-muted">With ease.</span></h2>
                <p class="lead">Use any content you like for your boxes. Whatever you would like to offer or ask your visitors.</p>
                <p class="lead">Buttons, related posts, forms, even third-party plugin shortcodes. You will be able to see the final result straight from your WordPress visual editor.</p>
            </div>
            <div class="col-md-7 col-md-pull-5">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/editor.png') }}">
            </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
            <div class="col-md-5">
                <h2 class="featurette-heading">Blend in with your theme. <span class="text-muted">Make your boxes pretty.</span></h2>
                <p class="lead">Finish off your boxes with a pretty design sauce, so the box is truly your own.</p>
                <p class="lead">A color here, a border there. A sure way to catch the attention of your visitor.</p>
            </div>
            <div class="col-md-7">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/appearance-settings.png') }}">
            </div>
        </div>

        <hr class="featurette-divider" style="margin-bottom: 0; border-top-color: transparent;">

        <!-- /END THE FEATURETTES -->
    </div>

    @include('parts.cta')
@stop
