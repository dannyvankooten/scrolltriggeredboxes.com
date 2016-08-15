@extends('layouts.master')

@section('title','Payments - Boxzilla')

@section('content')
<div class="container">

    <div class="breadcrumbs bordered small-padding">
        <a href="/">Account</a> &rightarrow; Payments
    </div>

    <h1 class="page-title">Payments</h1>
    <table class="table">
        <tr>
            <th>Date</th>
            <th>License</th>
            <th>Total</th>
            <th></th>
        </tr>
        @forelse( $payments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                <td><a href="/licenses/{{ $payment->subscription->license->id }}">{{ substr( $payment->subscription->license->license_key, 0, 10 ) . '..' }}</a></td>
                <td>
                    {{ $payment->getFormattedTotal() }}
                    @if( $payment->tax > 0.00 )
                        &nbsp; <small class="muted">(incl. {{ $payment->getCurrencySign() . $payment->tax }} tax)</small>
                    @elseif( $payment->subtotal < 0 )
                        &nbsp; <small class="muted">(refund)</small>
                    @endif
                </td>
                <td>
                    <a class="button button-small" href="/payments/{{ $payment->id }}/invoice">Invoice</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">There are no payments for this subscription.</td>
            </tr>
        @endforelse
    </table>

    <div class="medium-margin">
        <p><a href="/">&leftarrow; Back to account overview</a></p>
    </div>


</div>
@endsection