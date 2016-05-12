<?php

/** @var Illuminate\Routing\Router $router */

$domain = config('app.domain');
$router->group(['domain' => sprintf( 'account.%s', $domain ), 'middleware' => ['web']], function () use( $router ) {

	// auth
	$router->get('/login', 'Auth\AuthController@showLoginForm');
	$router->post('/login', 'Auth\AuthController@login');
	$router->get('/logout', 'Auth\AuthController@logout');

	// checkout
	$router->get( '/register', 'AccountController@register' );
	$router->post( '/register', 'AccountController@create' );

	// account
	$router->get( '/', 'AccountController@overview' );
	$router->get( '/welcome', 'AccountController@welcome' );
	$router->get( '/edit', 'AccountController@editCredentials' );
	$router->post( '/edit', 'AccountController@updateCredentials' );
	$router->get( '/edit/billing', 'AccountController@editBillingInfo' );
	$router->post( '/edit/billing', 'AccountController@updateBillingInfo' );
	$router->get( '/edit/payment', 'AccountController@editPaymentMethod' );
	$router->post( '/edit/payment', 'AccountController@updatePaymentMethod' );

	// licenses
	$router->get('/licenses', 'LicenseController@overview');
	$router->get('/licenses/new', 'LicenseController@create' );
	$router->post('/licenses/new', 'LicenseController@store' );
	$router->get('/licenses/{id}', 'LicenseController@details' );
	$router->post('/licenses/{id}', 'LicenseController@update' );

	// plugins
	$router->get('/plugins', 'PluginController@overview' );
	$router->get('/plugins/{id}/download', 'PluginController@download' )->name('plugins_download');

	$router->get('/payments', 'PaymentController@overview' );
	$router->get('/payments/{id}/invoice', 'PaymentController@invoice' );
	
	// TODO: allow login out a license from the account page
	//$router->delete('/account/licenses/{license_id}/activations/{activation_id}', 'AccountController@deleteActivation');

	// auth
	$router->get('/password/reset', 'Auth\PasswordController@getReset');
	$router->post('/password/reset', 'Auth\PasswordController@postReset');
	$router->get('/password/email', 'Auth\PasswordController@getEmail');
	$router->post('/password/email', 'Auth\PasswordController@postEmail');
	//$router->controller( 'password', 'Auth\PasswordController' );

	// redirects
	$router->get( '/kb', function () {
		return redirect( 'http://scrolltriggeredboxes.readme.io/v1.0' );
	} );



});

// API url's
$router->group( [ 'domain' => sprintf( 'api.%s', $domain ), 'prefix' => '/v1', 'namespace' => 'API\\v1', 'middleware' => ['api'] ], function () use( $router ) {

	$router->post( '/license/activations', 'LicenseController@create' );
	$router->delete( '/license/activations', 'LicenseController@delete' );

	$router->get( '/plugins', 'PluginController@index' );
	$router->get( '/plugins/{id}', 'PluginController@get' );
	$router->get( '/plugins/{id}/download', 'PluginController@download' );

	$router->any( '/helpscout', 'HelpScoutController@get' );
} );

// Admin url's
$router->group(['domain' => sprintf( 'admin.%s', $domain ), 'middleware' => ['admin'] ], function () use( $router ) {
	$router->get( '/', function() {
		return redirect( '/licenses' );
	} );

	$router->get( '/users', 'Admin\UserController@overview' );
	$router->get( '/users/{id}', 'Admin\UserController@detail' );

	$router->get( '/licenses', 'Admin\LicenseController@overview' );
	$router->get( '/licenses/{id}', 'Admin\LicenseController@detail' );

	$router->post( '/subscriptions/{id}', 'Admin\SubscriptionController@update' );

	$router->delete( '/payments/{id}', 'Admin\PaymentController@delete' );
});
