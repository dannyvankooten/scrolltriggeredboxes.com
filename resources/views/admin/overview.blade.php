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
                            <td>{{ str_limit($user->name, 18) }}</td>
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


        <div class="row clearfix">
            <!-- Recent payments -->
            <div class="col col-3">
                <h3>Last 5 payments</h3>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentPayments as $payment)
                        <tr>
                            <td><a href="/users/{{ $payment->user->id }}">{{ str_limit( $payment->user->name, 18 ) }}</a></td>
                            <td>{{ $payment->getFormattedTotal() }}</td>
                            <td>{{ $payment->created_at->format('M j') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Upcoming payments -->
            <div class="col col-3">
                <h3>Upcoming payments</h3>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($upcomingPayments as $subscription)
                        <tr>
                            <td><a href="/users/{{ $subscription->user->id }}">{{ str_limit( $subscription->user->name, 18 ) }}</a></td>
                            <td>{{ $subscription->getFormattedAmountInclTax() }}</td>
                            <td>{{ $subscription->next_charge_at->format('M j') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <div class="medium-margin"></div>



    </div>

@endsection