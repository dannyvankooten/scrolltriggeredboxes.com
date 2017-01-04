@extends('layouts.admin')

@section('title','Overview - Boxzilla')

@section('content')

    <style type="text/css">
        .count { font-size: 36px; display: block; }
        .count a{ text-decoration: none; color: inherit; }
        .percentage { font-size: 24px; }
        .percentage.pos, .percentage.lower-is-better.neg { color: limegreen; }
        .percentage.pos:before { content:"+" }

        .percentage.neg, .percentage.lower-is-better.pos { color: orangered; }

        .percentage.neutral { color: #999; display: none; }
        .timeframe { display: block; }
    </style>
    <div class="container">


        <div class="row clearfix">
            <div class="col col-2 text-center">
                <h4 class="no-margin">Total revenue</h4>
                <span class="count">
                    ${{ round( $totals->total_revenue_this_month ) }}
                    <?php $percentage = $totals->calculatePercentageDifference( $totals->total_revenue_this_month, $totals->total_revenue_last_month ); ?>
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
                <h4 class="no-margin">Churned licenses</h4>
                <span class="count">
                    {{ $totals->churn_this_month }}
                    <?php $percentage = $totals->calculatePercentageDifference( $totals->churn_this_month, $totals->churn_last_month ); ?>
                    <span class="percentage lower-is-better {{ ( $percentage > 0  ) ? 'pos' : ( ( $percentage < 0 ) ? 'neg' : 'neutral' ) }}">{{ $percentage }}%</span>
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

        <div>
            <h3>Last 5 licenses</h3>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Activations</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentLicenses as $license)
                    <tr>
                        <td><a href="/users/{{$license->user->id}}">{{ $license->user->email }}</a></td>
                        <td><a href="/licenses/{{$license->id}}>">{{ ucfirst($license->plan) }} <small class="muted">per {{ $license->interval }}</small></a></td>
                        <td class="{{ $license->isActive() ? 'success' : 'warning' }}">{{ $license->status }}</td>
                        <td>{{ $license->getActivationsCount() .'/'. $license->site_limit }}</td>
                        <td>{{ $license->created_at->format('M d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="small-margin"></div>

        <div class="row clearfix">

            <!-- Recent payments -->
            <div class="col col-3">
                <h3>Last 5 payments</h3>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentPayments as $payment)
                        <tr>
                            <td>
                                <a href="/users/{{ $payment->user->id }}">{{ str_limit($payment->user->email, 18) }}</a>

                                <div class="muted small">
                                    {{ $payment->created_at->format('M j') }}
                                    &middot;
                                    <a href="{{ $payment->getGatewayUrl() }}">{{ $payment->getGatewayName() }}</a>
                                </div>

                            </td>
                            <td class="{{ $payment->isRefund()  ? 'danger' : 'success' }}">{{ $payment->getFormattedTotal() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- / Recent payments -->

            <!-- Recent activations -->
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
                    @forelse($recentActivations as $activation)
                        <tr>
                            <td><a href="http://{{ $activation->domain }}">{{ str_limit( $activation->domain, 18 ) }}</a></td>
                            <td><a href="/licenses/{{ $activation->license->id }}">{{ count($activation->license->activations) . '/' . $activation->license->site_limit }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="2">No site activations.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <!-- / Recent acivations -->

        </div>

        <div class="medium-margin"></div>

        <!-- Expiring licenses -->
        <div>
            <h3>Expiring licenses</h3>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Activations</th>
                    <th>Expires</th>
                </tr>
                </thead>
                <tbody>
                @foreach($expiringLicenses as $license)
                    <tr>
                        <td><a href="/users/{{$license->user->id}}">{{ $license->user->email }}</a></td>
                        <td><a href="/licenses/{{ $license->id }}">{{ ucfirst($license->plan) }} <small class="muted">(per {{ $license->interval }})</small></a></td>
                        <td class="{{ $license->isActive() ? 'success' : 'warning' }}">{{ $license->status }}</td>
                        <td>{{ $license->getActivationsCount() .'/'. $license->site_limit }}</td>
                        <td>{{ $license->expires_at->diffInDays() <= 0 ? 'Today' : $license->expires_at->diffInDays() . ' days from now' }} </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>



    </div>

@endsection