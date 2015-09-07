@extends('layouts.admin')

@section('title','Account - Scroll Triggered Boxes')

@section('content')

    <div class="container">
        <h1>Licenses</h1>

        @if (session('message'))
            <div class="bs-callout bs-callout-success">
                {!! session('message') !!}
            </div>
        @endif


        <table class="table table-striped">
            <thead>
                <tr>
                    <th>License Key</th>
                    <th>Owner</th>
                    <th width="20%">Used on # sites</th>
                    <th>Created at</th>
                </tr>
            </thead>
            <tbody>
            @foreach($licenses as $license)
                <tr>
                    <td><a href="{{ url('/licenses/' . $license->id) }}">{{ $license->license_key }}</a></td>
                    <td>{{ $license->user->email }}</td>
                    <td>{{ count( $license->activations ) }}</td>
                    <td>{{ $license->created_at->format('F d, Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>


    </div>
@stop