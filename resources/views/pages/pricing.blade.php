@extends('layouts.master')

@section('title','Pricing - Scroll Triggered Boxes')

@section('content')
    <div class="jumbotron">
        <div class="container">
            <h1>Pricing</h1>
            <p>The Scroll Triggered Boxes core plugin <strong>is and always will be free</strong>.</p>
            <p>On top of that, there are several paid plans available which give you a range of benefits.</p>
            <br />
            <div class="row">
                <div class="col-sm-4 sm-bottom-margin">
                    <div class="pricing-block">
                        <h2>Free</h2>
                        <ul class="list-unstyled">
                            <li class="price">$0</li>
                            <li>Scroll Triggered Boxes core plugin</li>
                            <li>Access to all free add-ons</li>
                            <li><a href="https://wordpress.org/plugins/scroll-triggered-boxes/" class="btn btn-lg btn-default"><span class="glyphicon glyphicon-download-alt"></span> Download</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4 sm-bottom-margin">
                    <div class="pricing-block standout">
                        <h2>Personal</h2>
                        <ul class="list-unstyled">
                            <li class="price">$49</li>
                            <li>Scroll Triggered Boxes core plugin</li>
                            <li>Access to <a href="{{ url('/plugins') }}">all premium add-ons</a></li>
                            <li>1 year of plugin updates on 1 site</li>
                            <li>1 year of priority email support</li>
                            <li><a href="https://transactions.sendowl.com/subscriptions/1019/36F66557/purchase" class="btn btn-lg btn-primary"><span class="glyphicon glyphicon-shopping-cart"></span> Purchase</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4 sm-bottom-margin">
                    <div class="pricing-block">
                        <h2>Developer</h2>
                        <ul class="list-unstyled">
                            <li class="price">$89</li>
                            <li>Scroll Triggered Boxes core plugin</li>
                            <li>Access to <a href="{{ url('/plugins') }}">all premium add-ons</a></li>
                            <li>1 year of plugin updates on 10 sites</li>
                            <li>1 year of priority email support </li>
                            <li><a href="https://transactions.sendowl.com/products/169391/230F7370/purchase" class="btn btn-lg btn-primary"><span class="glyphicon glyphicon-shopping-cart"></span> Purchase</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container content">
        <div class="row marketing">
            <div class="col-lg-6">
                <h4>Do you have a refund policy?</h4>
                <p>Definitely! We trust you will enjoy our product so much that <a href="{{ url('/refund-policy') }}">you can get a full refund within 30 days of your purchase</a>, no questions asked.</p>

                <br />

                <h4>Do I receive updates of the plugins?</h4>
                <p>Yes! After your payment, you will instantly receive a license key which can be used to configure automatic update checks on one or multiple sites (depending on your plan).</p>

                <br />

                <h4>Do you offer support when I need help?</h4>
                <p>Of course we do. In fact, we're known for our top-notch support.</p>
                <p>After purchasing your plan, you will get a dedicated email address that guarantees a fast response.</p>

                <br />

                <h4>Can I upgrade my plan later on?</h4>
                <p>Yes, just <a href="{{ url('/contact') }}">send us an email</a> and we will make it happen.</p>

                <br />



            </div>

            <div class="col-lg-6">

                <h4>Is the price a one-time fee?</h4>
                <p>No. By default, your license will automatically renew itself each year for 50% of the initial purchase price.</p>

                <p>You can however opt-out of license renewals at any time you want, but that also means you will no longer receive plugin updates after your license expires.</p>
                <p>For a multitude of reasons, we believe no one should be on outdated software. This is why we choose to renew licenses by default.</p>

                <br />
                <h4>What happens after my license expires?</h4>
                <p>If you cancel your subscription then your license will expire a year after your purchase (or last payment).</p>
                <p>The plugin will keep on working as it is at that moment but you will no longer have access to plugin updates, new plugins or priority support over email.</p>
                <br />
                <h4>I have another question.</h4>
                <p>Please <a href="{{ url('/contact') }}">send us an email</a> and we will answer it.</p>

            </div>
        </div>
    </div>
@stop

@section('foot')
    <script type="text/javascript">
        (function(d) {
            var pricingBlocks = document.querySelectorAll('.pricing-block');
            var largestHeight = 0;
            for(var i=0; i<pricingBlocks.length; i++) {
                if( pricingBlocks[i].clientHeight > largestHeight ) {
                    largestHeight = pricingBlocks[i].clientHeight;
                }
            }

            for(var i=0; i<pricingBlocks.length; i++) {
                var topMargin = ( largestHeight - pricingBlocks[i].clientHeight ) / 2;
                if( topMargin > 0 ) {
                    pricingBlocks[i].style.marginTop = topMargin + "px";
                }
            }
        })(window.document);
    </script>
 @endsection
