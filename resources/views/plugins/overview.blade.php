@extends('layouts.master')

@section('title','Plugins - Boxzilla')

@section('content')
<div class="container">
    <h1 class="page-title">Plugins</h1>
    <p>The core Boxzilla plugin can be downloaded from WordPress.org <a href="https://wordpress.org/plugins/scroll-triggered-boxes/">here</a>.</p>

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

    <p><a href="javascript:history.go(-1);">&lsaquo; Go back</a></p>
</div>
@stop