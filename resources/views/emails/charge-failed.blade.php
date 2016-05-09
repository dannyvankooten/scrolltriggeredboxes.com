<p>Uh oh, we tried to process your subscription payment of <strong>${{ $subscription->getAmountInclTax() }}</strong> on your card ending in <strong>{{ $subscription->user->card_last_four }}</strong> and it was declined.</p>

<p>We'll try charging your card again in 4 days. If you'd like to update your credit card or cancel your subscription, please <a href="{{ domain_url( '/edit/payment', 'account' ) }}">login to your account and go to "Edit Payment Method"</a>.</p>

<p>If you have any questions or feedback, please get in touch by replying to this email.</p>

<p>- The Boxzilla Team</p>