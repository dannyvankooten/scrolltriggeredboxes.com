@extends('layouts.admin')

@section('title','View User - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/users/">Users</a> &rightarrow; {{ $user->email }}
        </div>

        <h1>User <small class="muted">{{ $user->email }}</small></h1>

        <table class="table table-striped">
            <tr>
                <th>Email</th>
                <td><a href="mailto:{{$user->email}}">{{ $user->email }}</a></td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>{{ $user->getFullAddress() }}</td>
            </tr>
            @if( $user->company )
                <tr>
                    <th>Company</th>
                    <td>{{ $user->company }}</td>
                </tr>
            @endif

            @if( $user->vat_number )
                <tr>
                    <th>VAT Number</th>
                    <td>{{ $user->vat_number }}</td>
                </tr>
            @endif

            <tr>
                <th>Joined</th>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
            </tr>
        </table>

        <div class="medium-margin"></div>

        <h3>Licenses</h3>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>License Key</th>
                <th width="20%">Activations</th>
                <th>Expires</th>
            </tr>
            </thead>
            <tbody>
            @foreach($user->licenses as $license)
                <tr>
                    <td><a href="{{ url('/licenses/' . $license->id) }}">{{ $license->license_key }}</a></td>
                    <td>{{ count( $license->activations ) .'/' . $license->site_limit }}</td>
                    <td><span class="{{ $license->isExpired() ? 'warning' : '' }}">{{ $license->expires_at->format('Y-m-d') }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p><a href="/licenses/create?license[user_id]={{ $user->id }}">&#43; Add new license for user</a></p>
        <!-- / end licenses -->

        <div class="medium-margin"></div>

        <h3>Payments</h3>
        <table class="table">
            <tr>
                <th>Date</th>
                <th>Total</th>
                <th></th>
                <th></th>
            </tr>
            @forelse( $user->payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                    <td class="@if( $payment->subtotal < 0 ) red @endif">
                        {{ $payment->getFormattedTotal() }}

                        @if( $payment->subtotal < 0 )
                            &nbsp; <small class="muted">(refund)</small>
                        @endif
                    </td>
                    <td>
                        @if( $payment->isEligibleForRefund())
                            <form method="POST" action="/payments/{{ $payment->id }}" data-confirm="Are you sure you want to refund this payment?">
                                {!! csrf_field() !!}
                                <input type="hidden" name="_method" value="DELETE" />
                                <input class="button button-small button-danger" type="submit" value="Refund" />
                            </form>
                        @endif
                    </td>
                    <td>
                        <a href="/payments/{{ $payment->id }}/invoice">Invoice</a> &nbsp;
                        <a href="{{ $payment->getStripeUrl() }}">Stripe</a> &nbsp;
                        <a href="{{ $payment->getMoneybirdUrl() }}">Moneybird</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">There are no payments for this subscription.</td>
                </tr>
            @endforelse
        </table>

        <!-- / end payments -->

        <div class="medium-margin"></div>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>
    </div>
@stop
