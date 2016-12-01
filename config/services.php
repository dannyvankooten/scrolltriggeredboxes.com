<?php

return [

	'braintree' => [
	    'environment' => env('BRAINTREE_ENVIRONMENT', ''),
        'merchant_id' => env('BRAINTREE_MERCHANT_ID', ''),
        'public_key' => env('BRAINTREE_PUBLIC_KEY', ''),
        'private_key' => env('BRAINTREE_PRIVATE_KEY', ''),
    ],

	'github' => [
	    'access_token' => env( 'GITHUB_TOKEN', '' ),
    ],

	'mailgun' => [
		'domain' => env('MAILGUN_DOMAIN', ''),
		'secret' => env('MAILGUN_SECRET', ''),
	],

    'stripe' => [
		'key' => env('STRIPE_KEY', ''),
		'secret' => env('STRIPE_SECRET', ''),
	],

	'helpscout' => [
		'secret' => env('HELPSCOUT_SECRET_KEY', '')
	],

	'moneybird' => [
		'administration' => env( 'MONEYBIRD_ADMINISTRATION_ID', '' ),
		'token' => env( 'MONEYBIRD_ACCESS_TOKEN', '' )
	]

];
