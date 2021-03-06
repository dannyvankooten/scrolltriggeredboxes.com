<?php

/*********************
 * Global Helper Functions
 *********************/
use App\Services\Payments\Gateways\BraintreeGateway;


/**
 * @param string $path
 * @param string $subdomain
 *
 * @return string
 */
function domain_url( $path = '/', $subdomain = '' ) {

	$domain = config( 'app.domain' );
	if( ! empty( $subdomain ) ) {
		$domain = sprintf( '%s.%s', $subdomain, $domain );
	}

	/** @var Illuminate\Http\Request $request */
	$request = app('request');
	return $request->getScheme() . '://' . rtrim( $domain, '/' ) . '/' . ltrim( $path, '/' );
}

/**
 * @return string
 */
function braintree_client_token() {
    return app(BraintreeGateway::class)->generateClientToken();
}