@extends('layouts.admin')

@section('title','Users - Boxzilla')

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
                    <label>Email Address</label>
                    <input type="text" name="filter[email]" value="{{ request('filter.email') }}" placeholder="Filter by email address" />
                </div>

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="filter[name]" value="" placeholder="Filter by name" />
                </div>

                <input type="submit" class="button" value="Filter" />
                @if( request('filter') )
                   &nbsp; <a href="?">Clear filters</a>
                @endif
            </div>
        </form>


        <div class="medium-margin"></div>

        <h1>Users</h1>

        <p>{{ count( $users ) }} users found.</p>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><a href="{{ url('/users/' . $user->id) }}">{{ $user->email }}</a></td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->created_at->format('F d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No results</td>
                </tr>
            @endforelse
            </tbody>
        </table>


    </div>
@stop
