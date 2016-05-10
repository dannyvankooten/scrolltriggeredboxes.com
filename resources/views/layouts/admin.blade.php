<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Boxzilla')</title>

    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">

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
        <h2 class="site-title pull-left">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo-text-white.png') }}" height="40" alt="boxzilla" />
            </a>
        </h2>
        <nav class="header-nav pull-right" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
            <ul class="nav nav-inline">
                <li role="presentation"><a href="/users">Users</a></li>
                <li role="presentation"><a href="/licenses">Licenses</a></li>
            </ul>
        </nav>
    </div>
</div>

<div role="main" class="big-margin" itemprop="mainContentOfPage">

    @if (session('message'))
        <div class="notice notice-success">
            {!! session('message') !!}
        </div>
    @endif

    @yield('content')
</div>

<footer class="muted">
    <div class="container">
        <em>There is no value in anything until it is finished</em> &mdash; The Great Khan
    </div>
</footer>

@yield('foot')
</body>
</html>
