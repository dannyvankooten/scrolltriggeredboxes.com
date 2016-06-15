@extends('layouts.admin')

@section('title','Licenses - Boxzilla')

@section('content')

    <div class="container">
        <h1>Licenses</h1>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>License Key</th>
                    <th>Owner</th>
                    <th width="20%">Activations</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            @foreach($licenses as $license)
                <tr>
                    <td><a href="{{ url('/licenses/' . $license->id) }}">{{ $license->license_key }}</a></td>
                    <td>{{ $license->user->email }}</td>
                    <td>{{ count( $license->activations ) . '/' . $license->site_limit }}</td>
                    <td>{{ $license->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>


    </div>
@stop
