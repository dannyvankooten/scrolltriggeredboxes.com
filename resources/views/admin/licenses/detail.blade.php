@extends('layouts.admin')

@section('title','View License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/users">Users</a> &rightarrow; <a href="/users/{{$license->user->id }}">{{ $license->user->email }}</a> &rightarrow; License {{ $license->id }}
        </div>

        <div class="medium-margin"></div>

        <h3>License &nbsp;<a href="/licenses/{{ $license->id }}/edit" title="Edit license details"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></h3>
        <table class="table table-striped">
            <tr>
                <th>Key</th>
                <td><code>{{ $license->license_key }}</code></td>
            </tr>
            <tr>
                <th>Activations</th>
                <td>{{ count($license->activations) . '/' . $license->site_limit }}</td>
            </tr>
            <tr>
                <th>Created</th>
                <td>{{ $license->created_at->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th>Expire{{ $license->isExpired() ? 'd' : 's' }}</th>
                <td><span class="{{ $license->isExpired() ? 'warning' : '' }}">{{ $license->expires_at->format('Y-m-d') }}</span></td>
            </tr>
        </table>

        <div class="medium-margin"></div>

        <h3>Activations <small>({{ count($license->activations) }}/{{ $license->site_limit }})</small></h3>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Domain</th>
                @if( $license->activation && $license->activation[0]->plugin )<th>Plugin</th>@endif
                <th>Activated on</th>
                <th></th>
            </tr>
            </thead>
            @foreach($license->activations as $activation)
                <tr>
                    <td><a href="{{ $activation->url }}">{{ $activation->domain }}</a></td>
                    <td>{{ $activation->updated_at->format('F j, Y') }}</td>
                    <td><form method="post" action="/activations/{{ $activation->id }}" data-confirm="Delete site activation?">{!! csrf_field() !!}<input type="hidden" name="_method" value="DELETE" /><input type="submit" value="Delete" class="button button-danger button-small" /></form></td>
                </tr>
            @endforeach
            @if( count( $license->activations ) == 0 )
                <tr>
                    <td colspan="2">This license is not activated on any sites.</td>
                </tr>
            @endif
        </table>

        <div class="medium-margin"></div>


        @if( $license->subscription )
        <h3>Subscription &nbsp; <a href="/subscriptions/{{ $license->subscription->id }}/edit" title="Edit subscription details"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></h3>
        <table class="table row-scoped">
            <tr>
                <th>Status</th>
                <td>
                    <span class="{{ $license->subscription->active ? "success" : "warning" }}">{{ $license->subscription->active ? "Active" : "Inactive" }}</span>

                    <form method="post" action="/subscriptions/{{ $license->subscription->id }}" class="pull-right" data-confirm="Are you sure you want to {{ $license->subscription->active ? "deactivate" : "reactivate" }} this subscription?">
                        {!! csrf_field() !!}
                        <input type="hidden" name="subscription[active]" value="{{ $license->subscription->active ? 0 : 1 }}" />
                        <input type="submit" value="{{ $license->subscription->active ? "Deactivate" : "Reactivate" }}" class="button button-small button-neutral" />

                        @if($license->subscription->active)
                            <button type="submit" name="process_refund" value="1" class="button button-small button-danger" data-confirm="Are you sure you want to deactivate this subscription and refund the last payment?">Refund & Deactivate</button>
                        @endif

                        <input type="hidden" name="_method" value="PUT" />
                    </form>
                </td>
            </tr>
            <tr>
                <th>Interval</th>
                <td>{{ ucfirst( $license->subscription->interval ) . "ly" }}</td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>{{ $license->subscription->getFormattedAmount() }}</td>
            </tr>
            <tr>
                <th>Next Charge</th>
                <td>
                    {{ $license->subscription->next_charge_at->format('Y-m-d') }}

                    <form method="post" action="/payments" class="pull-right" data-confirm="Are you sure you want to charge this subscription now?">
                        {!! csrf_field() !!}
                        <input type="hidden" name="payment[subscription_id]" value="{{ $license->subscription->id }}" />
                        <input type="submit" value="Charge Now" class="button button-small" />
                    </form>

                </td>
            </tr>

        </table>

        <div class="medium-margin"></div>

        <h3>Payments</h3>
        <table class="table">
            <tr>
                <th>Date</th>
                <th>Total</th>
                <th></th>
                <th></th>
            </tr>
            @forelse( $license->subscription->payments as $payment)
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
                        <a href="{{ $payment->getStripeUrl() }}"> Stripe</a> &nbsp;
                        <a href="{{ $payment->getMoneybirdUrl() }}">Moneybird</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">There are no payments for this subscription.</td>
                </tr>
            @endforelse
        </table>
        @endif

        <div class="medium-margin"></div>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>


    </div>
@stop
