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
                    <th><a href="?order={{ request('order', 'desc') === 'desc' ? 'asc' : 'desc' }}&by=key">License Key</a></th>
                    <th><a href="?order={{ request('order', 'desc') === 'desc' ? 'asc' : 'desc' }}&by=owner">Owner</a></th>
                    <th width="20%"><a href="?order={{ request('order', 'desc') === 'desc' ? 'asc' : 'desc' }}&by=activations">Activations</a></th>
                    <th><a href="?order={{ request('order', 'desc') === 'desc' ? 'asc' : 'desc' }}&by=status">Status</a></th>
                </tr>
            </thead>
            <tbody style="font-size: 15px;">
            @forelse($licenses as $license)
                <tr>
                    <td><a href="{{ url('/licenses/' . $license->id) }}">{{ $license->license_key }}</a></td>
                    <td><a href="/users/{{$license->user->id}}">{{ $license->user->email }}</a></td>
                    <td>{{ count( $license->activations ) . '/' . $license->site_limit }}</td>
                    <td class="{{ $license->isActive() ? 'success' : 'warning' }}">{{ $license->status }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No licenses found.</tr>
            @endforelse
            </tbody>
        </table>

        <div class="medium-margin">
            <a href="/licenses/create">&#43; Add new license</a>
        </div>

    </div>
@stop
