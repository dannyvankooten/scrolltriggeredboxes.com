@extends('layouts.master')

@section('title','Payments - Boxzilla')

@section('content')
<div class="container">

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
                <td>@if($payment->license)<a href="/licenses/{{ $payment->license->id }}">{{ substr( $payment->license->license_key, 0, 10 ) . '..' }}</a>@endif</td>
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
                <td colspan="3">No payments.</td>
            </tr>
        @endforelse
    </table>

    <div class="medium-margin">
        <p><a href="/">&leftarrow; Back to account overview</a></p>
    </div>


</div>
@endsection