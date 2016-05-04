@extends('layouts.master')

@section('title','Invoice - Boxzilla')

@section('content')
<div class="container">

    <div class="breadcrumbs bordered small-padding">
        <a href="/">Account</a> &rightarrow; <a href="/payments">Payments</a> &rightarrow; Invoice
    </div>


    <h1 class="page-title">Invoice not ready yet</h1>
    <p>We're sorry but we have not yet generated your invoice for this payment. </p>
    <p>The invoice should be ready within the hour. If not, please contact us and we'll get you your invoice as soon as humanly possible.</p>

    <div class="">
        <p><a href="/payments">&leftarrow; Back to payments overview</a></p>
    </div>


</div>
@endsection