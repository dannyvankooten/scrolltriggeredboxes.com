@extends('layouts.master')

@section('title','License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered small-padding">
            <a href="/">Account</a> &rightarrow; <a href="/licenses">Licenses</a> &rightarrow; License Details
        </div>

        <h1 class="page-title">License details</h1>

        <table class="table row-scoped">
            <tr>
                <th>Key</th>
                <td width="80%">
                    <input type="text" value="{{ $license->license_key }}" class="unstyled" onfocus="this.select()" readonly />
                </td>
                <td class="row-action"></td>
            </tr>
            <tr>
                <th>Activations</th>
                <td>
                    <p>Using {{ count( $license->activations ) }} of {{ $license->site_limit }} activations.</p>
                    <div class="progress"><div class="progress-bar" style="width: {{ $license->usagePercentage() }}%;"></div></div>
                </td>
                <td class="row-action"></td>
            </tr>
            @if( $license->subscription )
            <tr>
                <th>Subscription</th>
                <td class="clearfix">
                    @if( $license->subscription->active )
                        Active
                    @else
                        Inactive
                    @endif
                </td>
                <td class="row-action">
                    <form method="post" action="/licenses/{{ $license->id }}">
                        {!! csrf_field() !!}

                        @if( $license->subscription->active )
                            <input type="hidden" name="subscription[active]" value="0" />
                            <button class="button-small" data-confirm="Are you sure you want to deactivate auto-renewal for this license?">Deactivate</button>
                        @else
                            <input type="hidden" name="subscription[active]" value="1" />
                            <button class="button-small">Reactivate</button>
                        @endif

                    </form>
                </td>
            </tr>
                @if( $license->subscription->active )
                <tr>
                    <th>Payment</th>
                    <td>
                        You will be charged <strong>${{ $license->subscription->amount + 0 }}</strong> on <strong>{{ $license->subscription->getNextChargeDate()->format('m/d/Y') }}</strong>.
                    </td>
                    <td class="row-action">
                        @if( $license->subscription->isPaymentDue() )
                            <form method="post" action="/licenses/{{ $license->id }}">
                                {!! csrf_field() !!}
                                <button class="button-small">Pay Now</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endif
            @endif

            @if( ! $license->subscription || ! $license->subscription->active )
            <tr>
                <th>Expire{{ $license->isExpired() ? 'd' : 's' }}</th>
                <td>
                    {{ $license->expires_at->format('M j, Y') }}
                </td>
                <td class="row-action"></td>
            </tr>
            @endif
        </table>

        <div class="medium-margin"></div>

        <h3>Sites using this license</h3>
        <p>This license key is currently activated on the following domains.</p>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Domain</th>
                <th>Activated on</th>
            </tr>
            </thead>
            @foreach($license->activations as $activation)
            <tr>
                <td><a href="{{ $activation->url }}">{{ $activation->domain }}</a></td>
                <td>{{ $activation->updated_at->format('F j, Y') }}</td>
            </tr>
            @endforeach
            @if( count( $license->activations ) == 0 )
                <tr>
                    <td colspan="2">This license is not activated on any sites.</td>
                </tr>
            @endif
        </table>


        <div class="medium-margin">
            <p><a href="/licenses">&leftarrow; Back to your licenses</a></p>
        </div>

    </div>
@stop