@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12 blogleft">
                <article class="content clearfix">
                   @yield('content.primary')
                </article>
                <div class="postnav clearfix">
                   @yield('content.nav')
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 col-md-offset-1 col-lg-offset-1 col-sm-offset-1 col-xs-offset-0 blogsidebar">
                <aside class="widget sidebar">
                   @yield('content.secondary')
                </aside>
            </div>
        </div>
    </div>
@stop
