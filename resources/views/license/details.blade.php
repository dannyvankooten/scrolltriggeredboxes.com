@extends('layouts.master')

@section('title','License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered padded">
            <a href="/">Account</a> &rightarrow; <a href="/licenses">Licenses</a> &rightarrow; License Details
        </div>

        <h1>License details</h1>

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
            <tr>
                <th>Status</th>
                <td class="clearfix">
                    @if( $license->subscription->active )
                        Active
                    @else
                        Inactive
                    @endif
                </td>
                <td class="row-action">
                    <form method="post" action="/licenses/{{ $license->id }}">
                        @if( $license->subscription->active )
                            <input type="hidden" name="subscription[active]" value="0" />
                            <button class="button-small">Deactivate</button>
                        @else
                            <input type="hidden" name="subscription[active]" value="1" />
                            <button class="button-small">Reactivate</button>
                        @endif

                    </form>
                </td>
            </tr>
            <tr>
                <th>Payment</th>
                <td>
                    @if( $license->subscription->active )
                        You will be charged <strong>${{ $license->subscription->amount + 0 }}</strong> on <strong>{{ $license->subscription->next_charge_at->format('m/d/Y') }}</strong>.
                    @else
                        No payment due.
                    @endif
                </td>
                <td class="row-action"></td>
            </tr>
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

        <p><a href="/">Back to your account.</a></p>
    </div>
@stop

@section('foot')
    <script>
        (function() {
            var actions = document.querySelectorAll('[data-confirm]');
            for( var i=0; i<actions.length; i++) {
                actions[i].onclick = function(e) {
                    return confirm(this.getAttribute('data-confirm'));
                }.bind(actions[i])
            }
        })();
    </script>
@stop