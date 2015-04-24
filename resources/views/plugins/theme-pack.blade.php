@extends('plugins.show')

@section('content.primary')
<div class="plugin">
    <h1>Theme Pack</h1>
    <p>This plugin adds several beautiful preset themes which you can choose from to your Scroll Triggered Boxes.</p>
    <p>You can pick a theme, immediately see the result in your editor and then further customise the colors to your own liking, so you can truly make it your own.</p>
    <p>The plugin comes with the following themes.</p>

    <hr />
    <div class="row">
        <div class="col-xs-6 col-md-4">
            <a href="{{ asset('img/plugins/theme-pack/horizontal-stripe.jpg') }}" data-lightbox="horizontal-stripe" class="thumbnail"><img src="{{ asset('img/plugins/theme-pack/horizontal-stripe.jpg') }}" alt><span class="sr-only">Horizontal Stripe</span></a>
        </div>
        <div class="col-xs-6 col-md-4">
            <a href="{{ asset('img/plugins/theme-pack/paper.jpg') }}" class="thumbnail" data-lightbox="paper"><img src="{{ asset('img/plugins/theme-pack/paper.jpg') }}" alt><span class="sr-only">Paper</span></a>
        </div>
        <div class="col-xs-6 col-md-4">
            <a href="{{ asset('img/plugins/theme-pack/polkadot.jpg') }}" class="thumbnail" data-lightbox="polkadot"><img src="{{ asset('img/plugins/theme-pack/polkadot.jpg') }}" alt><span class="sr-only">Polkadot</span></a>
        </div>
        <div class="col-xs-6 col-md-4">
            <a href="{{ asset('img/plugins/theme-pack/top-color.jpg') }}" class="thumbnail" data-lightbox="top-color"><img src="{{ asset('img/plugins/theme-pack/top-color.jpg') }}" alt><span class="sr-only">Top Color</span></a>
        </div>
        <div class="col-xs-6 col-md-4">
            <a href="{{ asset('img/plugins/theme-pack/top-shadow.jpg') }}" class="thumbnail" data-lightbox="top-shadow"><img src="{{ asset('img/plugins/theme-pack/top-shadow.jpg') }}" alt><span class="sr-only">Top Shadow</span></a>
        </div>
        <div class="col-xs-6 col-md-4">
            <a href="{{ asset('img/plugins/theme-pack/striped-border.jpg') }}" class="thumbnail" data-lightbox="striped-border"><img src="{{ asset('img/plugins/theme-pack/striped-border.jpg') }}" alt><span class="sr-only">Striped Border</span></a>
        </div>
    </div>
    <hr />

    <p>The themes shown above are just a few of the many possibilities, since the colors in the themes can be fully customised to your liking.</p>

    <hr />
    <p><img src="{{ asset('img/plugins/theme-pack/option-panel.jpg') }}" alt="" /></p>
    <hr />

    <p>Using this theme pack you can easily create beautiful eye-catching boxes without needing complex CSS styling rules.</p>
</div>
@stop

{{--@section('content.nav')--}}
    {{--<a href="#" class="post-prev">Older Post</a>--}}
    {{--<a href="#" class="post-next">Previous Post</a>--}}
{{--@stop--}}

@section('head')
    <link href="{{ asset('css/lightbox.css') }}" rel="stylesheet">
@endsection

@section('foot')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="{{ asset( 'js/lightbox.min.js') }}"></script>
@endsection