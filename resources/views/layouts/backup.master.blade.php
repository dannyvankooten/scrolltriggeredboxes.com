<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--main style start-->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!--main style end-->
</head>

<body>
<!--top part start-->
<header class="navbar navbar-default topNav" id="top" role="banner">
    <div class="container">
        <div class="navbar-header">
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse"><span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
            <a href="{{ url('/') }}" class="navbar-brand"><img src="{{ asset('images/logo.png') }}" alt="logo"><span>Scroll Triggered Boxes</span></a> </div>

        <!--Menu start-->
        <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{ url('/plugins') }}"><span class="glyphicon glyphicon-modal-window"></span> Plugins</a></li>
                <!--<li><a id="drop1" href="#" role="button" class="dropdown-toggle"  href="#">Pages</a>
                    <ul class="dropdown-menu" >
                        <li><a href="about.html">About</a></li>
                        <li><a href="comingsoon.html">Coming Soon</a></li>
                        <li><a href="404page.html">404 page</a></li>
                    </ul>
                </li>-->
                <li><a href="https://wordpress.org/plugins/scroll-triggered-boxes/" rel="nofollow"><span class="glyphicon glyphicon-download-alt"></span> Download</a></li>
                <li><a href="https://transactions.sendowl.com/cart?merchant_id=13535" rel="nofollow"><span class="glyphicon glyphicon-shopping-cart"></span> Cart</a></li>
            </ul>
        </nav>
        <!--Menu end-->
    </div>
</header>

@yield('content')

<!--footer start-->
<footer class="footer">
    <div class="container">
        @include('parts.footermenu')
        <p>&copy; 2013-{{ date('Y') }}. &nbsp;&nbsp; <a href="{{ url('/') }}">Scroll Triggered Boxes</a>, a plugin by <a href="https://dannyvankooten.com">Danny van Kooten</a>.</p>
    </div>

</footer>
<!--footer end-->


<!--Javascriptfile start -->
<script type="text/javascript" src="https://transactions.sendowl.com/assets/sendowl.js" ></script>


@yield('foot')

</body>
</html>
