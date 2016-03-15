@extends('layouts.admin')

@section('title','View License - Boxzilla')

@section('content')

    <div class="container">

        <h1>License: <small><code>{{ $license->license_key }}</code></small></h1>

        @if (session('message'))
            <div class="bs-callout bs-callout-success">
                {!! session('message') !!}
            </div>
        @endif

        <p>This license key is currently activated on the following domains.</p>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Domain</th>
                @if( $license->activation && $license->activation[0]->plugin )<th>Plugin</th>@endif
                <th>Activated on</th>
            </tr>
            </thead>
            @foreach($license->activations as $activation)
                <tr>
                    <td><a href="{{ $activation->url }}">{{ $activation->domain }}</a></td>
                    <td>{{ $activation->updated_at->format('F j, Y') }}</td>
                </tr>
            @endforeach
            @if( count( $license->activations ) == 0 )
                <tr>
                    <td colspan="2">This license is not activated on any sites.</td>
                </tr>
            @endif
        </table>


        <p><a href="javascript:history.go(-1);">Go back.</a></p>


    </div>
@stop
