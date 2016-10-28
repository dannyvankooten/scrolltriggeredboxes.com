<p>Uh oh, we tried to process your subscription payment of <strong>{{ $payment->getFormattedTotal() }}</strong> on your card ending in <strong>{{ $payment->user->card_last_four }}</strong> but it was declined.</p>

<p>We'll try charging your card again in 3 days before we suspend your plugin license.</p>
<p>If you'd like to update your payment method or cancel your subscription, please <a href="{{ domain_url( '/edit/payment', 'account' ) }}">login to your account</a> and go to "Edit Payment Method".</p>

<p>If you have any questions or feedback, please get in touch by replying to this email.</p>

<p>- The Boxzilla Team</p>