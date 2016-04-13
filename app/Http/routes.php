<?php

$domain = config('app.domain');

Route::group(['domain' => sprintf( 'account.%s', $domain ), 'middleware' => ['web']], function () {

	// auth
	$this->get('/login', 'Auth\AuthController@showLoginForm');
	$this->post('/login', 'Auth\AuthController@login');
	$this->get('/logout', 'Auth\AuthController@logout');

	// checkout
	Route::get( '/register', 'AccountController@register' );
	Route::post( '/register', 'AccountController@create' );

	// account
	Route::get( '/', 'AccountController@overview' );

	Route::get( '/edit', 'AccountController@editBillingInfo' );
	Route::post( '/edit', 'AccountController@updateBillingInfo' );

	Route::get( '/edit/payment', 'AccountController@editPaymentMethod' );
	Route::post( '/edit/payment', 'AccountController@updatePaymentMethod' );

	// licenses
	Route::get('/licenses', 'LicenseController@overview');
	Route::get('/licenses/new', 'LicenseController@_new' );
	Route::post('/licenses/new', 'LicenseController@process' );
	Route::get('/licenses/{id}', 'LicenseController@details' );
	Route::post('/licenses/{id}', 'LicenseController@update' );

	// plugins
	Route::get('/plugins', 'PluginController@overview' );
	Route::get('/plugins/{id}/download', 'PluginController@download' )->name('plugins_download');

	//Route::get('/invoices', 'AccountController@invoices' );
	//Route::get('/invoices/{id}', 'AccountController@downloadInvoice');

	// TODO: allow login out a license from the account page
	//Route::delete('/account/licenses/{license_id}/activations/{activation_id}', 'AccountController@deleteActivation');

	// auth
	Route::controller( 'password', 'Auth\PasswordController' );

	// redirects
	Route::get( '/kb', function () {
		return redirect( 'http://scrolltriggeredboxes.readme.io/v1.0' );
	} );

});

// API url's
Route::group( [ 'domain' => sprintf( 'api.%s', $domain ), 'prefix' => '/v1', 'namespace' => 'API\\v1', 'middleware' => ['api'] ], function () {

	// global licenses
	Route::post( '/login', 'AuthController@login' );
	Route::get( '/logout', 'AuthController@logout' );

	Route::get( '/plugins', 'PluginController@index' );
	Route::get( '/plugins/{id}', 'PluginController@get' );
	Route::get( '/plugins/{id}/download', 'PluginController@download' );

	Route::any( '/helpscout', 'HelpScoutController@get' );
} );

// Admin url's
Route::group(['domain' => sprintf( 'admin.%s', $domain ), 'middleware' => ['admin'] ], function () {
	Route::get( '/', function() {
		return redirect( '/licenses' );
	} );

	Route::get( '/licenses', 'Admin\LicenseController@overview' );
	Route::get( '/licenses/{id}', 'Admin\LicenseController@detail' );
});
