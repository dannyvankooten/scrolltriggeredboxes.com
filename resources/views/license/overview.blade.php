@extends('layouts.master')

@section('title','License - Boxzilla')

@section('content')
<div class="container">
    <h1 class="page-title">Licenses</h1>
    <p>You have the following license keys. You can use these keys to configure the plugin for automatic update checks.</p>

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
            <td><a href="/licenses/{{ $license->id }}">{{ $license->license_key }}</a></td>
            <td>{{ count( $license->activations ) }}</td>
            <td>{{ $license->created_at->format('F d, Y') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <p><a class="button" href="/purchase">Purchase a new license.</a></p>

    <p><a href="javascript:history.go(-1);">&lsaquo; Go back</a></p>

</div>
@stop