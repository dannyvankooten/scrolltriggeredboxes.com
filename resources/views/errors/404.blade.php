<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>404</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--main style start-->

    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/404.css') }}">
    <!--main style end-->
    <!--google font style-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,600,700,800,300' rel='stylesheet' type='text/css'>
    <!--font-family: 'Open Sans', sans-serif;-->

    <!--Font Awesome start-->
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
</head>

<body>
<!--content start-->
<section class="container img404">
    <p class="chain_broken"><i class="fa fa-chain-broken fa-6"></i></p>
    <h3 class="txt404">sorry the page you are looking for does not exist.</h3>
    <p class="txt404Sub">You can explore our site back to the navigation below.</p>
    <div class="separator"><span class="sepIcon in404"></span></div>
</section>
<!--content end-->

<!--footer link start-->
<div class="container">
    @include('parts.footermenu')
</div>
<!--footer link end-->
<script type="text/javascript" src="https://transactions.sendowl.com/assets/sendowl.js" ></script>
<script src="{{ asset('js/modernizr.custom.js') }}"></script>
</body>
</html>
