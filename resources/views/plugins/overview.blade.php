@extends('layouts.master')

@section('title','Plugins - Boxzilla')

@section('content')
<div class="container">

    <div class="breadcrumbs bordered small-padding">
        <a href="/">Account</a> &rightarrow; Plugins
    </div>

    <h1 class="page-title">Plugins</h1>
    <p>You can <a href="https://boxzillaplugin.com/download/">download the core Boxzilla plugin here</a>.</p>

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
            <td><a href="{{ $plugin->getPageUrl() }}">{{ $plugin->name }}</a></td>
            <td>{{ $plugin->getVersion() }}</td>
            <td><a class="button button-small" href="{{ route('plugins_download', [ $plugin->id ]) }}">Download</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <p>If you need help installing a plugin, please have a look at the <a href="http://scrolltriggeredboxes.readme.io/v1.0/docs/installing-add-on-plugins">installation instructions</a>.</p>
    @else
    <p>It seems you have no valid license. Please <a href="{{ domain_url('/pricing') }}">purchase one of the premium plans in order to get access to the premium add-on plugins</a>.</p>
    @endif

    <div class="medium-margin">
        <p><a href="/">&leftarrow; Back to account overview</a></p>
    </div>


</div>
@stop