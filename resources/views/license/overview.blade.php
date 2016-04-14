@extends('layouts.master')

@section('title','License - Boxzilla')

@section('content')
<div class="container">

    <div class="breadcrumbs bordered padded small-margin">
        <a href="/">Account</a> &rightarrow; Licenses
    </div>

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

    <p class="medium-margin"><a class="button" href="/licenses/new">Purchase a new license.</a></p>

    <p><a href="/">&leftarrow; Back to account overview</a></p>

</div>
@stop