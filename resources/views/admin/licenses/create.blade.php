@extends('layouts.admin')

@section('title','View License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/licenses">Licenses</a> &rightarrow;
            Create license
        </div>

        <h1>Create License</h1>

        <form method="post" action="/licenses">
            {!! csrf_field() !!}

            <div class="form-group">
                <label>User ID <span class="big red">*</span></label>
                <div class="form-element">
                    <input type="number" name="license[user_id]" value="{{ $license->user_id }}" required>
                </div>
            </div>

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
                <input type="submit" value="Create" />
            </div>

        </form>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>

    </div>
@stop
