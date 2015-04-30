<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Scroll Triggered Boxes')</title>

    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <meta name="twitter:site" content="@dannyvankooten">
    <meta name="twitter:creator" content="@dannyvankooten">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-15758173-52', 'auto');
        ga('send', 'pageview');
    </script>

    @yield('head')
</head>

<body>

<div class="header clearfix">
    <div class="container">
        <h2 class="site-title pull-left"><a href="/"><img src="{{ asset('img/logo-small.png') }}" class="logo" width="64" height="64" /> Scroll Triggered Boxes</a></h2>
        <input type="checkbox" id="toggle" />
        <nav class="header-nav" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
            <label for="toggle" class="glyphicon toggle" onclick></label>
            <ul class="menu">
                <li role="presentation"><a href="/pricing" class="{{ (Request::is('pricing') ? 'active' : '') }}">Pricing</a></li>
                <li role="presentation"><a href="/plugins" class="{{ (Request::is('plugins') ? 'active' : '') }}">Plugins</a></li>
                <li role="presentation"><a href="/contact" class="{{ (Request::is('contact') ? 'active' : '') }}">Contact</a></li>
            </ul>
        </nav>
    </div>
</div>

@yield('masthead')

<div role="main" itemprop="mainContentOfPage">
    @yield('content')
</div>

<footer class="footer">

    <!-- start footer -->
    <div class="footer-1">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3>Scroll Triggered Boxes</h3>
                    <ul class="unstyled">
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ url('/pricing') }}">Pricing</a></li>
                        <li><a href="{{ url('/plugins') }}">Plugins</a></li>
                        <li><a href="{{ url('/account') }}">Account</a></li>
                        <li><a href="{{ url('/kb') }}">Documentation</a></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <!-- Begin MailChimp Signup Form -->
                    <div id="mc_embed_signup">
                        <form action="//dannyvankooten.us1.list-manage.com/subscribe/post?u=a2d08947dcd3683512ce174c5&amp;id=e3e1e0f8d8" method="post" name="mc-embedded-subscribe-form" target="_blank">
                            <div class="form-inline">
                                <h3>Subscribe to our mailing list</h3>

                                <p>Everything related to Scroll Triggered Boxes, straight from your inbox. We do not spam.</p>

                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon">@</div>
                                        <input type="email" name="EMAIL"  class="form-control" placeholder="Your email address..">
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <input type="submit" value="Subscribe" name="subscribe" class="btn btn-cta">
                                </div>


                            </div>
                            <div style="position: absolute; left: -5000px;"><input type="text" name="b_a2d08947dcd3683512ce174c5_e3e1e0f8d8" tabindex="-1" value=""></div>

                        </form>
                    </div>

                    <!--End mc_embed_signup-->
                </div>
            </div>
        </div>
    </div>
        <!-- End top footer -->

    <!-- Start sub footer -->
    <div class="footer-2">
        <div class="container">
            <p>&copy; 2013 - {{ date('Y') }} <a href="/">Scroll Triggered Boxes</a>. A plugin by <a href="https://dannyvankooten.com" rel="external author">Danny van Kooten</a>. <span class="pull-right"><a href="{{ url('/refund-policy') }}">Refund Policy</a> &middot; <a href="#top">Back to top</a></span></p>
        </div>
    </div>
    <!-- End sub footer -->


</footer>


<script type="text/javascript" src="https://transactions.sendowl.com/assets/sendowl.js" ></script>
<script src="{{ asset('js/plugins.js') }}"></script>

@yield('foot')
</body>
</html>
