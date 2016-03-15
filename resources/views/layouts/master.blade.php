<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Boxzilla')</title>

    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <meta name="twitter:site" content="@boxzillaplugin">

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
        <h2 class="site-title pull-left">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo-small.png') }}" class="logo" width="64" height="64" />
                <span>Boxzilla</span>
            </a>
        </h2>
        <input type="checkbox" id="toggle" />
        <nav class="header-nav" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
            <label for="toggle" class="glyphicon toggle" onclick></label>
            <ul class="menu">
                <li role="presentation"><a href="/">Account</a></li>

                @if (Auth::check())
                <li role="presentation"><a href="/auth/logout">Log out</a></li>
                @endif
            </ul>
        </nav>
    </div>
</div>

@yield('masthead')

<div role="main" itemprop="mainContentOfPage">
    @yield('content')
</div>

<footer class="footer">

    <!-- Start sub footer -->
    <div class="footer-2">
        <div class="container">
            <p class="margined-elements">
                <a href="{{ domain_url('/about') }}">About</a>
                <a href="{{ domain_url('/refund-policy') }}">Refund Policy</a>
                <a href="{{ domain_url('/kb') }}">Documentation</a>
                <a href="{{ domain_url('/contact') }}">Contact</a>
            </p>
            <p style="font-style: italic;">
                <a class="unstyled" href="{{ domain_url() }}">Boxzilla</a> is a WordPress plugin built by &nbsp;
                <a href="https://ibericode.com" rel="external author">
                    <img src="{{ asset('img/ibericode-logo-white.png') }}" height="25">
                </a>
            </p>
        </div>
    </div>
    <!-- End sub footer -->


</footer>

@yield('foot')
</body>
</html>
