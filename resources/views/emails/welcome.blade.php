<p>Welcome, {{ $user->name }}!</p>
<br />
<p>You can use the following credentials to log in to <a href="{{ url('/account') }}">our account area on scrolltriggeredboxes.com</a>.</p>
<table>
    <tr>
        <th>Email</th>
        <td>{{ $user->email }}</td>
    </tr>
    <tr>
        <th>Password</th>
        <td>{{ $password }}</td>
    </tr>
</table>
<p>If you have any questions, please just reply to this email.</p>
<p>Danny, Ines & Harish</p>