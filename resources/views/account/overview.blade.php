@extends('layouts.master')

@section('title','Account - Scroll Triggered Boxes')

@section('content')
    @include('account.parts.masthead')

    <div class="container">
        <h1>Account</h1>

        @if (session('message'))
            <div class="bs-callout bs-callout-success">
                {{ session('message') }}
            </div>
        @endif

        <p>Welcome, <strong>{{ $user->name }}</strong>.</p>

        <h3>Licenses</h3>
        <p>You have the following license keys. You can use these keys to configure the plugin for automatic update checks.</p>

        <table class="table table-striped">
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

        <h3>Plugins</h3>
        @if($user->hasValidLicense())
        <p>Since you have a valid license, you have access to the following plugin downloads.</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Plugin</th>
                    <th>Version</th>
                    <th width="1"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($plugins as $plugin)
                <tr>
                    <td><a href="{{ url('/plugins/' . $plugin->url )}}">{{ $plugin->name }}</a></td>
                    <td>{{ $plugin->version }}</td>
                    <td><a href="{{ url('/plugins/' . $plugin->url . '/download') }}">Download</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p>If you need help installing a plugin, please have a look at the <a href="{{ url('/kb/installation-instructions') }}">installation instructions</a>.</p>

        @endif

    </div>
@stop
