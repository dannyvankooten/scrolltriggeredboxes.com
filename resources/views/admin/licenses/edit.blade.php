@extends('layouts.admin')

@section('title','Edit License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/users">Users</a> &rightarrow;
            <a href="/users/{{$license->user->id }}">{{ $license->user->email }}</a> &rightarrow;
            <a href="/licenses/{{ $license->id }}">License {{ $license->id }}</a> &rightarrow;
            Edit
        </div>

        <h1>Edit License: <small><code>{{ $license->license_key }}</code></small></h1>

        <form method="post" action="/licenses/{{ $license->id }}">
            <input type="hidden" name="_method" value="PUT" />
            {!! csrf_field() !!}


            <div class="form-group">
                <label>License Key <span class="big red">*</span></label>
                <div class="form-element">
                    <input type="text" name="license[license_key]" disabled value="{{ $license->license_key }}">
                </div>
            </div>

            <div class="form-group">
                <label>Site Limit <span class="big red">*</span></label>
                <div class="form-element">
                    <input type="number" name="license[site_limit]" value="{{ $license->site_limit }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>Expires At <span class="big red">*</span></label>
                <div class="form-element">
                    <input type="date" name="license[expires_at]" value="{{ $license->expires_at->format("Y-m-d") }}" required>
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="Save Changes" />
            </div>

        </form>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>


    </div>
@stop
