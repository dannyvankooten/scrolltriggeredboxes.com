@extends('layouts.admin')

@section('title','View License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/users">Users</a> &rightarrow; <a href="/users/{{$license->user->id }}">{{ $license->user->email }}</a> &rightarrow; License
        </div>

        <h1>License: <small><code>{{ $license->license_key }}</code></small></h1>

        @if (session('message'))
            <div class="bs-callout bs-callout-success">
                {!! session('message') !!}
            </div>
        @endif

        <div class="medium-margin"></div>

        <h3>Activations</h3>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Domain</th>
                @if( $license->activation && $license->activation[0]->plugin )<th>Plugin</th>@endif
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

        <h3>Subscription</h3>
        @if( $license->subscription )
        <table class="table row-scoped">
            <tr>
                <th>Status</th>
                <td><span class="label {{ $license->subscription->active ? "success" : "warning" }}">{{ $license->subscription->active ? "Active" : "Inactive" }}</span></td>
                <td>
                    <form method="POST" action="/subscriptions/{{ $license->subscription->id }}" data-confirm="Are you sure you want to toggle this subscription status?">
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
                <th>Expires</th>
                <td>{{ $license->expires_at->format('Y-m-d') }}</td>
                <td></td>
            </tr>
            <tr>
                <th>Interval</th>
                <td>{{ ucfirst( $license->subscription->interval ) . "ly" }}</td>
                <td></td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>${{ $license->subscription->amount }}</td>
                <td></td>
            </tr>

        </table>

        <div class="medium-margin"></div>

        <h3>Payments</h3>
        <table class="table">
            <tr>
                <th>Date</th>
                <th>Total</th>
                <th></th>
            </tr>
            @forelse( $license->subscription->payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                    <td>{{ $payment->getCurrencySign() . ' ' . $payment->getTotal() }}</td>
                    <td>
                        <form method="POST" action="/payments/{{ $payment->id }}" data-confirm="Are you sure you want to refund this payment?">
                            <input type="hidden" name="_method" value="DELETE" />
                            <input class="button-small" type="submit" value="Refund" />
                        </form>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="3">There are no payments for this subscription.</td>
                </tr>
            @endforelse
        </table>
        @else
            <p>This license has no subscription.</p>
        @endif


        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>


    </div>
@stop
