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
	$router->get('/password/reset/{token}', 'Auth\PasswordController@getReset');
	$router->post('/password/reset', 'Auth\PasswordController@postReset');
	$router->get('/password/email', 'Auth\PasswordController@getEmail');
	$router->post('/password/email', 'Auth\PasswordController@postEmail');

});

// API url's
$router->group( [ 'domain' => sprintf( 'api.%s', $domain ), 'prefix' => '/v1', 'namespace' => 'API\\v1', 'middleware' => ['api'] ], function () use( $router ) {
	$router->get( '/license', 'LicenseController@getLicense' );
	$router->post( '/license/activations', 'LicenseController@createActivation' );
	$router->delete( '/license/activations/{activationKey}', 'LicenseController@deleteActivation' );

	// for BC with old licenses that did not get an activation key
	$router->delete( '/license/activations', 'LicenseController@deleteActivation' );

	$router->get( '/plugins', 'PluginController@index' );
	$router->get( '/plugins/{id}', 'PluginController@get' );
	$router->get( '/plugins/{id}/download', 'PluginController@download' );

	$router->get('/vat/validate/{number}', 'VatController@validate' );

	$router->any( '/helpscout', 'HelpScoutController@get' );
    $router->post( '/stripe', 'StripeController@get' );
} );

// Admin url's
$router->group(['domain' => sprintf( 'admin.%s', $domain ), 'middleware' => ['admin'] ], function () use( $router ) {

	$router->get('/', 'Admin\DefaultController@overview' );

	$router->get('/users', 'Admin\UserController@overview' );
	$router->post('/users', 'Admin\UserController@store' );
	$router->get('/users/create', 'Admin\UserController@create' );
	$router->get('/users/{id}', 'Admin\UserController@detail' );

	$router->get( '/licenses', 'Admin\LicenseController@overview' );
	$router->get( '/licenses/create', 'Admin\LicenseController@create' );
	$router->post( '/licenses', 'Admin\LicenseController@store' );
	$router->get( '/licenses/{id}', 'Admin\LicenseController@detail' );
	$router->get( '/licenses/{id}/edit', 'Admin\LicenseController@edit' );
	$router->put( '/licenses/{id}', 'Admin\LicenseController@update' );
	$router->delete( '/licenses/{id}', 'Admin\LicenseController@destroy' );

	$router->delete( '/payments/{id}', 'Admin\PaymentController@destroy' );
    $router->get( '/payments/{id}/invoice', 'Admin\PaymentController@invoice' );

	$router->delete( '/activations/{id}', 'Admin\ActivationController@destroy' );

});
