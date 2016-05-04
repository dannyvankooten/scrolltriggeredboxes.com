@extends('layouts.admin')

@section('title','Users - Boxzilla')

@section('content')

    <div class="container">
        <h1>Users</h1>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td><a href="{{ url('/users/' . $user->id) }}">{{ $user->email }}</a></td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->created_at->format('F d, Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>


    </div>
@stop
