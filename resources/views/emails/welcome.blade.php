<p>Welcome, {{ $user->name }}!</p>

<p>You can use the following credentials to log in to <a href="{{ url('/account?email=' . $user->email) }}">our account area on scrolltriggeredboxes.com</a>, where you can manage your license key(s) and download all add-on plugins.</p>
<br />
<table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <th align="left">Email:</th>
        <td>{{ $user->email }}</td>
    </tr>
    <tr>
        <th align="left">Password:</th>
        <td>{{ $password }}</td>
    </tr>
</table>
<br />
<p>If you have any questions related to your purchase or our plugins, please just reply to this email.</p>
<p>
    Danny, Harish & Arne<br />
    Scroll Triggered Boxes<br />
    {{ url('/') }}
</p>
