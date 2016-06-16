@extends('layouts.admin')

@section('title','View User - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/users/">Users</a> &rightarrow; {{ $user->email }}
        </div>

        <h1>User <small class="muted">{{ $user->email }}</small></h1>

        <table class="table table-striped">
            <tr>
                <th>Email</th>
                <td><a href="mailto:{{$user->email}}">{{ $user->email }}</a></td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>{{ $user->getFullAddress() }}</td>
            </tr>
            @if( $user->company )
                <tr>
                    <th>Company</th>
                    <td>{{ $user->company }}</td>
                </tr>
            @endif

            @if( $user->vat_number )
                <tr>
                    <th>VAT Number</th>
                    <td>{{ $user->vat_number }}</td>
                </tr>
            @endif

            <tr>
                <th>Joined</th>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
            </tr>
        </table>


        <h3>Licenses</h3>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>License Key</th>
                <th width="20%">Activations</th>
                <th>Created</th>
            </tr>
            </thead>
            <tbody>
            @foreach($user->licenses as $license)
                <tr>
                    <td><a href="{{ url('/licenses/' . $license->id) }}">{{ $license->license_key }}</a></td>
                    <td>{{ count( $license->activations ) .'/' . $license->site_limit }}</td>
                    <td>{{ $license->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>

    </div>
@stop
