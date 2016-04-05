@extends('layouts.master')

@section('title','Invoices - Boxzilla')

@section('content')
<div class="container">

    <h1>Your invoices</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        @foreach (Auth::user()->invoices() as $invoice)
        <tr>
            <td>{{ $invoice->date()->toFormattedDateString() }}</td>
            <td>{{ $invoice->total() }}</td>
            <td><a href="/invoices/{{ $invoice->id }}">Download</a></td>
        </tr>
        @endforeach
    </table>
</div>
@stop