<p>Hey {{ $user->name }},</p>
<p>Thank you for purchasing a new license!</p>

<h3>Downloading plugins</h3>
<p>To download any of the premium add-on plugins, <a href="{{ domain_url('/', 'account') }}">log-in to our account area</a> using <strong>{{ $user->email }}</strong> and your chosen password.</p>
<br />

<h3>Your license key</h3>
<p>You can use the following key to configure the plugin for update checks.</p>
<p><code style="font-size: 20px;">{{ $license->license_key  }}</code></p>
<br />

<p>If you have any questions, don't hesitate to let us know. We're here to help!</p>

<br />
<p style="font-style: italic; ">
    Danny, Harish & Arne<br />
    Boxzilla<br />
    {{ url('/') }}
</p>
