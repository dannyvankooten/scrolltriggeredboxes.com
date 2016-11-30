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
                <th>Status</th>
                <td class="{{ $license->isActive() ? 'success' : 'warning' }}">
                    {{ $license->isActive() ? 'Active' : 'Inactive' }}
                </td>
                <td class="row-action"></td>
            </tr>
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

            @if( ! $license->isActive() )
            <tr>
                <th>Expire{{ $license->isExpired() ? 'd' : 's' }}</th>
                <td>
                    <span class="{{ $license->isExpired() ? 'danger' : 'warning' }}">{{ $license->expires_at->format('M j, Y') }}</span>
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

        <div class="medium-margin"></div>

        @if( $license->isActive() )
            <h3>Cancel auto-renew for this license</h3>
            <p>Use the button below to stop this license from auto-renewing.</p>
            <form method="post" action="/licenses/{{ $license->id }}">
                {!! csrf_field() !!}

                <input type="hidden" name="license[status]" value="canceled" />
                <button class="button-small button-danger" data-confirm="Are you sure you want to deactivate auto-renewal for this license?">Cancel license</button>
            </form>
        @else
            <h3>Enable auto-renew for this license</h3>
            <p>This license is not auto-renewing right now.</p>
            <form method="post" action="/licenses/{{ $license->id }}">
                {!! csrf_field() !!}

                <input type="hidden" name="license[status]" value="active" />
                <button class="button-small">Re-enable license</button>
            </form>
        @endif


        <div class="medium-margin">
            <p><a href="/licenses">&leftarrow; Back to your licenses</a></p>
        </div>

    </div>
@stop