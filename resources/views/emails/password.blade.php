<p>Hi {{ $user->name }},</p>

<p>Someone requested a password reset link. If this someone was not you, please just ignore this email.</p>
<p>Click here to reset your password: https{{ domain_url('password/reset/'.$token.'?email='.$user->email, 'account' ) }} .</p>
<p>Hope that helps!</p>
<p>
    Danny, Harish & Arne<br />
    Scroll Triggered Boxes<br />
    {{ url('/') }}
</p>
