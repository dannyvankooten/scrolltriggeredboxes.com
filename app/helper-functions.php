<?php

/*********************
 * Global Helper Functions
 *********************/



/**
 * @param string $path
 *
 * @return string
 */
function domain_url( $path = '/', $subdomain = '' ) {

	$domain = config( 'app.domain' );
	if( ! empty( $subdomain ) ) {
		$domain = sprintf( '%s.%s', $subdomain, $domain );
	}
	return '//' . rtrim( $domain, '/' ) . '/' . ltrim( $path, '/' );
}