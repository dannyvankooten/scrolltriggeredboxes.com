<div class="jumbotron">
    <div class="container">
        <p>Logged in as <strong>{{ Auth::user()->name }}</strong> <small>({{ Auth::user()->email }})</small>. <small>(<a href="/logout">logout</a>)</small></p>
    </div>
</div>