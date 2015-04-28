@extends('plugins.show')

@section('content.primary')
    <div class="plugin">
        <h1>{{ $plugin->name }}</h1>

        @if( '' !== $plugin->long_description )
            {{ $plugin->description }}
        @else
            {{ $plugin->short_description }}
        @endif
    </div>
@stop