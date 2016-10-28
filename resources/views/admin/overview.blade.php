@extends('layouts.admin')

@section('title','Overview - Boxzilla')

@section('content')

    <style type="text/css">
        .count { font-size: 36px; display: block; }
        .count a{ text-decoration: none; color: inherit; }
        .percentage { font-size: 24px; }
        .percentage.pos { color: limegreen; }
        .percentage.pos:before { content:"+" }
        .percentage.neg { color: orangered; }
        .percentage.neutral { color: #999; display: none; }
        .timeframe { display: block; }
    </style>

    <div class="container">

        <div class="row clearfix">
            <div class="col col-2 text-center">
                <h4 class="no-margin">New users</h4>
                <span class="count">
                    {{ $totals->new_users_this_month }}
                    <?php $percentage = $totals->calculatePercentageDifference( $totals->new_users_this_month, $totals->new_users_last_month ); ?>
                    <span class="percentage {{ ( $percentage > 0  ) ? 'pos' : ( ( $percentage < 0 ) ? 'neg' : 'neutral' ) }}">{{ $percentage }}%</span>
                </span>
                <small class="muted">(last {{ request('days', 30 ) }} days)</small>
            </div>
            <div class="col col-2 text-center">
                <h4 class="no-margin">New licenses</h4>
                <span class="count">
                    {{ $totals->new_licenses_this_month }}
                    <?php $percentage = $totals->calculatePercentageDifference( $totals->new_licenses_this_month, $totals->new_licenses_last_month ); ?>
                    <span class="percentage {{ ( $percentage > 0  ) ? 'pos' : ( ( $percentage < 0 ) ? 'neg' : 'neutral' ) }}">{{ $percentage }}%</span>
                </span>
                <small class="muted">(last {{ request('days', 30 ) }} days)</small>
            </div>
            <div class="col col-2 text-center">
                <h4 class="no-margin">Total revenue</h4>
                <span class="count">
                    ${{ round( $totals->total_revenue_this_month ) }}
                    <?php $percentage = $totals->calculatePercentageDifference( $totals->total_revenue_this_month, $totals->total_revenue_last_month ); ?>
                    <span class="percentage {{ ( $percentage > 0  ) ? 'pos' : ( ( $percentage < 0 ) ? 'neg' : 'neutral' ) }}">{{ $percentage }}%</span>
                </span>
                <small class="muted">(last {{ request('days', 30 ) }} days)</small>
            </div>
        </div>
        <div class="clearfix text-center medium-margin">
            <form method="GET" onchange="this.submit()">
                <p class="muted small">Select a different timeframe:
                    <select name="days" style="width: auto; height: auto;">
                        @foreach( $timeframeOptions as $option )
                            <option value="{{ $option }}" {{ request('days') == $option ? 'selected' : '' }}>{{ $option }} days</option>
                        @endforeach
                    </select>
                </p>
            </form>
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
                            <td><a href="/users/{{ $user->id }}">{{ str_limit($user->name, 18) }}</a></td>
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


        </div>

        <div class="medium-margin"></div>



    </div>

@endsection