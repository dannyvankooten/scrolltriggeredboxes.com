@extends('layouts.admin')

@section('title','View User - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered padded small-margin">
            <a href="/users/">Users</a> &rightarrow; {{ $user->email }}
        </div>

        <h1>User {{ $user->email }}</h1>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>License Key</th>
                <th width="20%">Used on # sites</th>
                <th>Created at</th>
            </tr>
            </thead>
            <tbody>
            @foreach($user->licenses as $license)
                <tr>
                    <td><a href="{{ url('/licenses/' . $license->id) }}">{{ $license->license_key }}</a></td>
                    <td>{{ count( $license->activations ) }}</td>
                    <td>{{ $license->created_at->format('F d, Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>

    </div>
@stop
