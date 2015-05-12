<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/
	'sendowl' => [
		'api_key' => env('SENDOWL_API_KEY', ''),
		'api_secret' => env('SENDOWL_API_SECRET', '')
	],

	'mailgun' => [
		'domain' => env('MAILGUN_DOMAIN', ''),
		'secret' => env('MAILGUN_SECRET', ''),
	],

	'pushbullet' => [
		'api_key' => env('PUSHBULLET_API_KEY', ''),
		'api_url' => 'https://api.pushbullet.com/v2'
	],

	'mandrill' => [
		'secret' => '',
	],

	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],

	'stripe' => [
		'model'  => 'App\User',
		'key' => '',
		'secret' => '',
	],

];
