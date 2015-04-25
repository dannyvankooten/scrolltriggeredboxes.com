@extends('layouts.page')

@section('title','Refund Policy - Scroll Triggered Boxes')

@section('content.main')
    <h1>Refund Policy</h1>

    <p>
        We care about our product and our customers which is why we want you to be completely happy with any product or service you buy from us.
        If you have any question, concern or problem, please let us know by replying to your purchase confirmation email.
    </p>

    <h2>30 day money back guarantee</h2>
    <p>
        Because we believe our product will make you happy, we offer a <strong>full refund within 30 days of your purchase</strong>. No questions asked.
    </p>
    <p>
        No refunds are provided after more than 30 days following your purchase.
    </p>

    <h3>Applying for a refund</h3>
    <p>
        To apply for a refund, simply contact support and tell us why you would like to have a refund.
        Please include a clear explanation why you are unhappy so we can try and help resolve any problems you might have.
    </p>

    <p><strong>In short:</strong> it is safe to try any of the <a href="{{ url('/pricing') }}">available premium plans</a> as you can always get a full refund later.</p>

@stop

