@extends('layouts.admin')

@section('title','Licenses - Boxzilla')

@section('content')

    <div class="container">

        <!-- Filter form -->
        <form method="get" class="well">
            <h4 onclick="var el = document.getElementById('filter-form'); el.style.display = ( el.style.display == 'none' ) ? '' : 'none';" style="cursor: pointer; margin: 0;">
                Filter results.. &nbsp;
                <i class="fa fa-search" aria-hidden="true"></i>
            </h4>
            <div id="filter-form" style="{{ request('filter') ? '' : 'display: none;' }}">
                <div class="form-group" style="margin-top: 20px;">
                    <label>License Key</label>
                    <input type="text" name="filter[license_key]" value="{{ request('filter.email') }}" placeholder="Filter by key" />
                </div>

                <input type="submit" class="button" value="Filter" />
                @if( request('filter') )
                    &nbsp; <a href="?">Clear filters</a>
                @endif

                <span class="help pull-right">Use <strong>*</strong> as a wildcard.</span>
            </div>
        </form>

        <div class="small-margin"></div>

        <h1 class="page-title">Licenses</h1>
        <p>{{ count( $licenses ) }} licenses found.</p>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>License Key</th>
                    <th>Owner</th>
                    <th width="20%">Activations</th>
                    <th>Expires</th>
                </tr>
            </thead>
            <tbody style="font-size: 15px;">
            @foreach($licenses as $license)
                <tr>
                    <td><a href="{{ url('/licenses/' . $license->id) }}">{{ $license->license_key }}</a></td>
                    <td>{{ $license->user->email }}</td>
                    <td>{{ count( $license->activations ) . '/' . $license->site_limit }}</td>
                    <td><span class="{{ $license->isExpired() ? 'warning' : '' }}">{{ $license->expires_at->format('Y-m-d') }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="medium-margin">
            <a href="/licenses/create">Add new license</a>
        </div>

    </div>
@stop
