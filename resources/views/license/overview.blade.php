@extends('layouts.master')

@section('title','License - Boxzilla')

@section('content')
<div class="container">

    <div class="breadcrumbs bordered small-padding">
        <a href="/">Account</a> &rightarrow; Licenses
    </div>

    <h1 class="page-title">Licenses</h1>
    <p>You have the following license keys. You can use these keys to configure the plugin for automatic update checks.</p>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>License Key</th>
            <th width="20%">Used #</th>
            <th>Created at</th>
        </tr>
        </thead>
        <tbody>
        @forelse($user->licenses as $license)
        <tr>
            <td><a href="/licenses/{{ $license->id }}">{{ $license->license_key }}</a></td>
            <td>{{ count( $license->activations ) }}</td>
            <td>{{ $license->created_at->format('F d, Y') }}</td>
        </tr>
        @empty
            <tr>
                <td colspan="3">You don't have any licenses yet. Why not <a href="/licenses/new">buy one now</a>?</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <p class="medium-margin"><a class="button" href="/licenses/new">Purchase a new license.</a></p>

    <div class="medium-margin">
        <p><a href="/">&leftarrow; Back to account overview</a></p>
    </div>


</div>
@stop