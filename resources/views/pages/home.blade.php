@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')
    <div class="jumbotron">
        <div class="container">
            <h2>A better alternative to pop-ups</h2>
            <p class="lead">Unobtrusive, conversion boosting boxes. The only lead generator we would ever use.</p>
            <p>
                <a class="btn btn-lg btn-default xs-bottom-margin" href="https://wordpress.org/plugins/scroll-triggered-boxes/" rel="nofollow" role="button"><span class="glyphicon glyphicon-download"></span> Download</a>
                <a class="btn btn-lg btn-primary" href="/plugins" role="button"><span class="glyphicon glyphicon-th-list"></span> Browse Plugins</a>
            </p>
        </div>
    </div>

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
