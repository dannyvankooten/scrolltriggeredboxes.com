<p>Hey {{ $user->name }},</p>
<p>
    This is a reminder that we will try to process a payment for your Boxzilla license ending in <strong>{{ substr($license->license_key, -4) }}</strong> on <strong>{{ $license->expires_at->format('M j, Y')  }}</strong>.
</p>
<p>
    If this payment succeeds, your license will automatically be extended with another {{$license->interval}}. This means that you can just keep on using the plugin without any further action from your side.
</p>

<p>If you wish to cancel your subscription instead, then you can do so from <a href="{{ domain_url('/licenses/'. $license->id, 'account') }}">your account area</a>.</p>
<p>Have any questions? Let us know by replying to this email.</p>

<p>
    Danny, Harish & Arne <br />
    - The Boxzilla Team
</p>