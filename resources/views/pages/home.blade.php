@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')
    @include('parts.masthead')

    <div class="container marketing">

        <!-- START THE FEATURETTES -->

        <hr class="featurette-divider" style="margin-top: 0; border-top-color: transparent;">

        <div class="row featurette">
            <div class="col-md-5">
                <h2 class="featurette-heading">Eye-catching boxes. <span class="text-muted">At just the right time.</span></h2>
                <p class="lead">With Scroll Triggered Boxes you can boost your conversions by showing your visitors eye-catching boxes at just the right time.</p>
                <p class="lead">Boxes contain <strong>any</strong> call-to-action you like.</p>
            </div>
            <div class="col-md-7">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/box.png') }}">
            </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
            <div class="col-md-5 col-md-push-7">
                <h2 class="featurette-heading">Effective call to actions. <span class="text-muted">With ease.</span></h2>
                <p class="lead">Fill the box with whatever you would like to offer or ask your visitors, straight from your WordPress editor.</p>
                <p class="lead">Use whatever content you like, even third-party plugin shortcodes.</p>
            </div>
            <div class="col-md-7 col-md-pull-5">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/editor.png') }}">
            </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
            <div class="col-md-5">
                <h2 class="featurette-heading">Attract attention. <span class="text-muted">Make your boxes pretty.</span></h2>
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
