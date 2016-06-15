@extends('layouts.admin')

@section('title','Overview - Boxzilla')

@section('content')

    <style type="text/css">
        .count { font-size: 36px; display: block; }
        .count a{ text-decoration: none; color: inherit; }
    </style>

    <div class="container">

        <div class="row clearfix">
            <div class="col col-2 text-center">
                <span class="count"><a href="/users">{{ $userCount }}</a></span>
                <span class="muted">users</span>
            </div>
            <div class="col col-2 text-center">
                <span class="count"><a href="/licenses">{{ $licenseCount }}</a></span>
                <span class="muted">licenses</span>
            </div>
            <div class="col col-2 text-center">
                <span class="count">{{ $activationCount }}</span>
                <span class="muted">activations</span>
            </div>
        </div>

        <div class="medium-margin"></div>

        <div class="row clearfix">

            <!-- Recent users -->
            <div class="col col-3">
                <h3>Last 5 users</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td><a href="mailto:{{$user->email}}">{{ $user->email }}</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Recent licenses -->
            <div class="col col-3">
                <h3>Last 5 activations</h3>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Site</th>
                        <th>Activations</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentActivations as $activation)
                        <tr>
                            <td><a href="http://{{ $activation->domain }}">{{ str_limit( $activation->domain, 18 ) }}</a></td>
                            <td>{{ count($activation->license->activations) . '/' . $activation->license->site_limit }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="medium-margin"></div>



    </div>

@endsection