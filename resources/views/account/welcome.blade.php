@extends('layouts.master')

@section('title','Thank you - that was a success!')

@section('content')

    <div class="container">

        <h3 class="page-title slashes">Thank you for your purchase!</h3>

        <p>Welcome, <strong>{{ $user->name }}</strong>.</p>

        <p>You can <a href="/plugins">download the add-on plugins here</a>.</p>
        <p>After installing & activating any of the plugins, you will need the following license key to configure the plugin for update checks.</p>

        <div class="small-margin">
        <code style="font-size: 24px;">
            {{ $license->license_key }}
        </code>
        </div>

        <p>This license key is valid for <strong>{{ $license->site_limit }}</strong> site activations.</p>

        <p>If you're unsure how to proceed, please have a <a href="https://kb.boxzillaplugin.com/installing-add-on-plugins/">look at the installation guide</a>.</p>

        <p>Questions? <a href="mailto:support@boxzillaplugin.com">Shoot us an email</a> and we'll help you out!</p>

    </div>
@stop
