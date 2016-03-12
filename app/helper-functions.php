<?php

/*********************
 * Global Helper Functions
 *********************/



/**
 * @param string $path
 *
 * @return string
 */
function domain_url( $path = '/' ) {
	return '//' . rtrim( config( 'app.domain' ), '/' ) . '/' . ltrim( $path, '/' );
}