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
        @foreach($plugins as $plugin)
        <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
                <img src="{{ url( $plugin->image_path ) }}" alt="...">
                <div class="caption">
                    <h3>{{ $plugin->name }}</h3>
                    <p>{{ $plugin->short_description }}</p>
                    <p>
                        <a href="/plugins/{{ $plugin->url }}" class="btn btn-default" role="button">Read more</a>
                        <span class="text-muted pull-right">{{ ucfirst( $plugin->type ) }}</span>
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@stop
