@extends('layouts.master')

@section('title','Account - Scroll Triggered Boxes')

@section('content')
    @include('account.parts.masthead')

    <div class="container">
        <h1>Account</h1>

        @if (session('message'))
            <div class="bs-callout bs-callout-success">
                {!! session('message') !!}
            </div>
        @endif

        <p>Welcome, <strong>{{ $user->name }}</strong>.</p>

        <h3>Licenses</h3>
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

        <h3>Plugins</h3>
        <p>The core Scroll Triggered Boxes plugin can be downloaded from WordPress.org <a href="https://wordpress.org/plugins/scroll-triggered-boxes/">here</a>.</p>

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
                    <td><a href="{{ domain_url( '/plugins/' . $plugin->url ) }}">{{ $plugin->name }}</a></td>
                    <td>{{ $plugin->version }}</td>
                    <td><a href="{{ route('plugins_download', [ $plugin->url ]) }}">Download</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p>If you need help installing a plugin, please have a look at the <a href="http://scrolltriggeredboxes.readme.io/v1.0/docs/installing-add-on-plugins">installation instructions</a>.</p>
        @else
            <p>It seems you have no valid license. Please <a href="{{ domain_url('/pricing') }}">purchase one of the premium plans in order to get access to the premium add-on plugins</a>.</p>
        @endif

    </div>
@stop
