<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Boxzilla')</title>

    <link href="{{ asset('css/normalize.css') }}" rel="stylesheet">
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
            <a href="{{ url('/') }}"><span>Boxzilla</span></a>
        </h2>
        <nav class="header-nav pull-right" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
            <ul class="nav nav-inline">
                <li role="presentation"><a href="/">Home</a></li>
            </ul>
        </nav>
    </div>
</div>

@yield('masthead')

<div class="main-container medium-margin" role="main" itemprop="mainContentOfPage">
    @yield('content')
</div>

<footer class="footer padded">
    <div class="container">
        <ul class="nav nav-inline">
            <li><a href="{{ domain_url('/about') }}">About</a></li>
            <li><a href="{{ domain_url('/refund-policy') }}">Refund Policy</a></li>
            <li><a href="{{ domain_url('/kb') }}">Documentation</a></li>
            <li><a href="{{ domain_url('/contact') }}">Contact</a></li>
        </ul>
        <p style="font-style: italic;">
            <a class="unstyled" href="{{ domain_url() }}">Boxzilla</a> is a WordPress plugin built by &nbsp;
            <a href="https://ibericode.com" rel="external author">
                <img src="{{ asset('img/ibericode-logo-white.png') }}" height="25">
            </a>
        </p>
    </div>
</footer>

<script>
    document.documentElement.className = document.documentElement.className.replace('no-js','js');
    var linkElement = document.createElement('link');
    linkElement.rel = "stylesheet";
    linkElement.href = "{{ asset('css/font-awesome.min.css') }}";
    document.head.appendChild(linkElement);
</script>

@yield('foot')
</body>
</html>
