@extends('layouts.master')

@section('title','Add-on plugins for Scroll Triggered Boxes')

@section('content')
<div class="jumbotron">
    <p class="">Get instant access to all premium add-ons. <a href="/pricing" class="">Purchase a plan</a>.</p>
</div>

<div class="container">

    <h1 class="page-title">Add-on plugins for Scroll Triggered Boxes</h1>
    <p>The following plugins are available as add-on plugins for Scroll Triggered Boxes, either free or with one of the <a href="/pricing">available plans</a>.</p>

    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
                <img src="{{ asset('images/plugins/theme-pack.jpg') }}" alt="...">
                <div class="caption">
                    <h3>Theme Pack</h3>
                    <p>A beautiful set of eye-catching themes for your boxes.</p>
                    <p>
                        <a href="/plugins/theme-pack" class="btn btn-default" role="button">Read more</a>
                        <span class="text-muted pull-right">Premium</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
                <img src="{{ asset('images/plugins/mailchimp.jpg') }}" alt="...">
                <div class="caption">
                    <h3>MailChimp Sign-Up</h3>
                    <p>Sign-up forms for your MailChimp list, with ease.</p>
                    <p>
                        <a href="/plugins/mailchimp" class="btn btn-default" role="button">Read more</a>
                        <span class="text-muted pull-right">Free</span>
                    </p>

                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
                <img src="{{ asset('images/plugins/related-posts.jpg') }}" alt="...">
                <div class="caption">
                    <h3>Related Posts</h3>
                    <p>Ask visitors to read a related post when they're done reading.</p>
                    <p>
                        <a href="/plugins/related-posts" class="btn btn-default" role="button">Read more</a>
                        <span class="text-muted pull-right">Free</span>
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>
@stop
