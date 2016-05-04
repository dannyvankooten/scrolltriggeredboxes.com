<p>Hi {{ $user->name }},</p>

<p>Someone requested a link to reset their password. If this someone was not you, please just ignore this email.</p>

<p>Click the following link to set a new password: {{ url('password/reset/'.$token.'?email='.$user->email, 'account' ) }} .</p>

<p>Hope that helps!</p>

<p style="font-style: italic;">
    Danny, Harish & Arne<br />
    Boxzilla<br />
    {{ url('/') }}
</p>
