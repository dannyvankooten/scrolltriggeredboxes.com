<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>@yield('title', 'Scroll Triggered Boxes')</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Custom styles for this template -->

    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <meta name="twitter:site" content="@dannyvankooten">
    <meta name="twitter:creator" content="@dannyvankooten">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @yield('head')
</head>

<body>

<div class="header clearfix">
    <div class="container">
        <nav class="header-nav" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
            <ul class="pull-right">
                <li role="presentation"><a href="/pricing" class="{{ (Request::is('pricing') ? 'active' : '') }}">Pricing</a></li>
                <li role="presentation"><a href="/plugins" class="{{ (Request::is('plugins') ? 'active' : '') }}">Plugins</a></li>
                <li role="presentation"><a href="/contact" class="{{ (Request::is('contact') ? 'active' : '') }}">Contact</a></li>
            </ul>
        </nav>
        <h2 class="site-title"><a href="/">Scroll Triggered Boxes</a></h2>
    </div>
</div>

@yield('masthead')

<div role="main" itemprop="mainContentOfPage">
    @yield('content')
</div>

<footer class="footer">
    <div class="container">
        <p>&copy; 2013 - {{ date('Y') }} <a href="/">Scroll Triggered Boxes</a>. A plugin by <a href="https://dannyvankooten.com" rel="external author">Danny van Kooten</a>. </p>
    </div>
</footer>

<script src="{{ asset('js/plugins.js') }}"></script>
<script type="text/javascript" src="https://transactions.sendowl.com/assets/sendowl.js" ></script>

@yield('foot')
</body>
</html>
