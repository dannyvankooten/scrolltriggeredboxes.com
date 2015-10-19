@extends('layouts.master')

@section('title','Add-on plugins for Scroll Triggered Boxes')

@section('content')
<div class="jumbotron">
    <p class="">Get instant access to all add-ons listed here.</p>
    <p><a href="{{ url('/pricing') }}" class="btn btn-lg btn-cta">Purchase a Premium plan</a></p>
</div>

<div class="container content">

    <h1 class="page-title">Add-on plugins for Scroll Triggered Boxes</h1>
    <p>The following plugins are available as add-on plugins for Scroll Triggered Boxes, either free or with one of the <a href="/pricing">available plans</a>.</p>

    <div class="row">
        @foreach($plugins as $plugin)
        <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
                <a href="{{ url( '/plugins/' . $plugin->url ) }}" class="unstyled">
                    <img src="{{ url( $plugin->image_path ) }}" alt="{{ $plugin->name }}">
                </a>
                <div class="caption">
                    <h3><a href="{{ url( '/plugins/' . $plugin->url ) }}" class="unstyled">{{ $plugin->name }}</a></h3>
                    <p>{{ $plugin->short_description }}</p>
                    <p>
                        <a href="{{ url( '/plugins/' . $plugin->url ) }}">Read more <span class="sr-only">about {{ $plugin->name }}</span></a>
                        <span class="text-muted pull-right">{{ ucfirst( $plugin->type ) }}</span>
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@stop
