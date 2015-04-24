@extends('layouts.master')

@section('title','Account - Scroll Triggered Boxes')

@section('content')
    @include('account.parts.masthead')

    <div class="container">
        <h1>Account</h1>

        <p>Welcome, <strong>{{ $user->name }}</strong>.</p>

        <p>You have the following license keys.</p>

        <table class="table">
            <thead>
                <tr>
                    <th>License Key</th>
                    <th>Activations</th>
                </tr>
            </thead>
            <tbody>
            @foreach($user->licenses as $license)
                <tr>
                    <td><a href="/account/licenses/{{ $license->id }}">{{ $license->license_key }}</a></td>
                    <td>{{ count( $license->activations ) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
@stop
