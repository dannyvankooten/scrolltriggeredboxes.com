@extends('layouts.admin')

@section('title','View License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/users">Users</a> &rightarrow;
            <a href="/users/{{$license->user->id }}">{{ $license->user->email }}</a> &rightarrow;
            <a href="/licenses/{{ $license->id }}">License {{ $license->id }}</a> &rightarrow;
            Edit Subscription
        </div>

        <h1>Edit Subscription</h1>

        <form method="post" action="/subscriptions/{{ $subscription->id }}">
            <input type="hidden" name="_method" value="PUT" />
            {!! csrf_field() !!}

            <div class="form-group">
                <label>Interval <span class="big red">*</span></label>
                <div class="form-element">
                    <select name="subscription[interval]">
                        <option value="month" {{ $subscription->interval == 'month' ? 'selected' : '' }}>Month</option>
                        <option value="year" {{ $subscription->interval == 'year' ? 'selected' : '' }}>Year</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Amount <span class="big red">*</span></label>
                <div class="form-element">
                    <input type="text" name="subscription[amount]" value="{{ $subscription->amount }}" required pattern="\d+(\.\d{2})?" />
                </div>
            </div>

            <div class="form-group">
                <label>Active?</label>
                <input type="hidden" name="subscription[active]" value="0" />
                <label class="unstyled">
                    <input type="checkbox" name="subscription[active]" value="1" {{ $subscription->active ? 'checked="checked"' : '' }} />
                    This subscription is active and will be charged on {{ $subscription->next_charge_at->format('Y-m-d') }}.
                </label>
            </div>

            <div class="form-group">
                <input type="submit" value="Update" />
            </div>

        </form>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>
    </div>
@stop
