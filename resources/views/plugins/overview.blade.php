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
            <td>{{ $plugin->name }}</td>
            <td>{{ $plugin->getVersion() }}</td>
            <td><a class="button button-small" href="{{ route('plugins_download', [ $plugin->id ]) }}?version={{ $plugin->getVersion() }}" title="Download {{ $plugin->name }}">Download</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <p>If you need help installing a plugin, please have a look at the <a href="https://kb.boxzillaplugin.com/installing-add-on-plugins/">installation instructions</a>.</p>
    @else

        <div class="notice notice-warning">
            <p>It seems you have no valid license.</p>
            <p>Please <a href="/licenses/">purchase a license</a> to get access to the premium add-on plugins.</p>
        </div>
    @endif

    <div class="medium-margin">
        <p><a href="/">&leftarrow; Back to account overview</a></p>
    </div>


</div>
@stop