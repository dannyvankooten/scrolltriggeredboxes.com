<p>Hey {{ $user->name }},</p>
<p>Thank you for purchasing a new license!</p>

<h3>Your license key</h3>
<p>You can use the following license key to configure the plugin for update checks.</p>
<p><code style="font-size: 20px;">{{ $license->license_key  }}</code></p>
<br />

<h3>Downloading plugins</h3>
<p>To download any of the premium plugins, please <a href="{{ domain_url('/', 'account') }}">log-in to the account area on our site</a>.</p>
<br />

<p>Last but not least: if you have any questions, please don't hesitate to let us know. We're here to help!</p>

<br />
<p style="font-style: italic; ">
    Danny, Harish & Arne<br />
    Boxzilla<br />
    {{ url('/') }}
</p>
