@if(Auth::check())
    <nav class="nav-bar header-nav" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
        <div class="container">
            <ul class="nav nav-inline">
                <li><a href="/plugins" class="{{ Request::is('plugins') ? 'active' : '' }}">Plugins</a></li>
                <li><a href="/licenses" class="{{ Request::is('licenses') ? 'active' : '' }}">Licenses</a></li>
                <li><a href="/payments" class="{{ Request::is('payments') ? 'active' : '' }}">Payments</a></li>
                <li><a href="/edit" class="{{ Request::is('edit') ? 'active' : '' }}">Account</a></li>
                <li class="pull-right hide-on-mobile"><a class="" href="/logout">Logout</a></li>
            </ul>
        </div>
    </nav>
@endif