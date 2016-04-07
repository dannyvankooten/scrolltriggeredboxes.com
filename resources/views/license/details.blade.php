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
                <td>
                    <input type="text" value="{{ $license->license_key }}" class="unstyled" onfocus="this.select()" readonly />

                </td>
            </tr>
            <tr>
                <th>Status</th>
                <td class="clearfix">
                    @if( $license->subscription->active )
                        Active

                        <form method="post" action="/licenses/{{ $license->id }}" class="pull-right">
                            <input type="hidden" name="subscription[active]" value="0" />
                            <button class="button-small">Deactivate</button>
                        </form>
                    @else
                        Inactive

                        <form method="post" action="/licenses/{{ $license->id }}" class="pull-right">
                            <input type="hidden" name="subscription[active]" value="1" />
                            <button class="button-small">Reactivate</button>
                        </form>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Payment</th>
                <td>
                    @if( $license->subscription->active )
                        You will be charged <strong>${{ $license->subscription->amount }}</strong> on <strong>{{ $license->subscription->next_charge_at->format('m/d/Y') }}</strong>.
                    @else
                        No payment due.
                    @endif
                </td>
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