<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Boxzilla')</title>

    <link href="{{ asset('css/main.css') }}" rel="stylesheet">

    <meta name="twitter:site" content="@boxzillaplugin">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>
        // util
        document.documentElement.className = document.documentElement.className.replace('no-js','js');

        // google analytics
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-15758173-52', 'auto');
        ga('send', 'pageview');
    </script>

    @yield('head')

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
</head>

<body>
<div id="page-wrap">

    <div id="header" class="header clearfix">
        <div class="container">
            <h2 class="site-title pull-left">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('img/logo-text-white.png') }}" height="40" alt="boxzilla plugin logo" />
                </a>
            </h2>

            @if(Auth::check())
                <nav class="header-nav pull-right" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
                    <ul class="nav nav-inline">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">Menu</a>
                            <ul class="dropdown-menu">
                                <li><a href="/plugins">Download plugins</a></li>
                                <li><a href="/licenses">View your licenses</a></li>
                                <li><a href="/payments">View your payments</a></li>
                                <li><a href="/edit">Edit account information</a></li>
                                <li><a href="/edit/billing">Edit billing information</a></li>
                                <li><a href="/edit/payment">Edit payment method</a></li>
                                <li><a href="/licenses/new">Purchase a new license</a></li>
                                <li class="last" style="border-top: 2px solid #eee;"><a href="/logout">Log out</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            @elseif(!Request::is('register'))
                <nav class="header-nav pull-right" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
                    <ul class="nav nav-inline">
                        <li><a href="{{ domain_url( '/' ) }}">Back to main site</a></li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>

    <div id="content" class="main-container medium-margin" role="main" itemprop="mainContentOfPage">

        <div class="container">
            @if (session('message'))
            <div class="notice notice-success">
                {!! session('message') !!}
            </div>
            @endif

            @if (session('error'))
                <div class="notice notice-warning">
                    {!! session('error') !!}
                </div>
            @endif
        </div>

        @yield('content')
    </div>

    <footer id="footer" class="footer medium-padding">
        <div class="container">

            <ul class="small-margin nav nav-inline">
                <li><a href="{{ url('/') }}">Account</a></li>
                <li><a href="{{ domain_url('/about') }}">About</a></li>
                <li><a href="{{ domain_url('/terms') }}">Terms</a></li>
                <li><a href="{{ domain_url('/', 'kb') }}">Documentation</a></li>
                <li><a href="{{ domain_url('/contact') }}">Contact</a></li>
            </ul>

            <p class="medium-margin" style="font-style: italic;">
                <a class="unstyled" href="{{ domain_url() }}">Boxzilla</a> is a WordPress plugin built by &nbsp;
                <a href="https://ibericode.com" rel="external author">
                    <img src="{{ asset('img/ibericode-logo-white.png') }}" height="25" style="vertical-align: bottom;">
                </a>
            </p>
        </div>
    </footer>
</div>

<script>
    // expand footer
    var pageWrap = document.getElementById('page-wrap');
    var footer = document.getElementById('footer');
    if( pageWrap.clientHeight < window.innerHeight ) {
        footer.style.height = footer.clientHeight + ( window.innerHeight - pageWrap.clientHeight ) + "px";
    }

    // font awesome
    var linkElement = document.createElement('link');
    linkElement.rel = "stylesheet";
    linkElement.href = "{{ asset('css/font-awesome.min.css') }}";
    document.head.appendChild(linkElement);
</script>
<script src="{{ asset('js/main.js') }}" type="text/javascript"></script>

@yield('foot')

</body>
</html>
