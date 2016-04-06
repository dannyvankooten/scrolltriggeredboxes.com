<?php

Route::group(['domain' => sprintf( 'account.%s', env('APP_DOMAIN') )], function () {

	// auth
	Route::get( '/auth/login/purchase', 'Auth\AuthController@getLoginFromPurchase' );
	Route::get( '/auth/login', 'Auth\AuthController@getLogin' );
	Route::post( '/auth/login', 'Auth\AuthController@postLogin' );
	Route::get( '/auth/logout', 'Auth\AuthController@getLogout' );

	// account
	Route::get( '/', 'AccountController@overview' );

	Route::get( '/edit', 'AccountController@editBillingInfo' );
	Route::post( '/edit', 'AccountController@updateBillingInfo' );

	Route::get( '/edit/payment', 'AccountController@editPaymentMethod' );
	Route::post( '/edit/payment', 'AccountController@updatePaymentMethod' );

	Route::get( '/licenses/{id}', 'AccountController@license' );

	// buy
	Route::get( '/purchase', 'LicenseController@purchase' );
	Route::post('/purchase', 'LicenseController@process' );

	//Route::get('/invoices', 'AccountController@invoices' );
	//Route::get('/invoices/{id}', 'AccountController@downloadInvoice');

	// todo: allow login out a license from the account page
	//Route::delete('/account/licenses/{license_id}/activations/{activation_id}', 'AccountController@deleteActivation');

	// auth
	Route::controller( 'password', 'Auth\PasswordController' );

	// redirects
	Route::get( '/kb', function () {
		return redirect( 'http://scrolltriggeredboxes.readme.io/v1.0' );
	} );

});

// API Url's
Route::group( [ 'domain' => sprintf( 'api.%s', env('APP_DOMAIN') ), 'prefix' => '/v1', 'namespace' => 'API\\v1' ], function () {

	// global licenses
	Route::post( '/login', 'AuthController@login' );
	Route::get( '/logout', 'AuthController@logout' );

	Route::get( '/plugins', 'PluginController@index' );
	Route::get( '/plugins/{id}', 'PluginController@get' );
	Route::get( '/plugins/{id}/download', 'PluginController@download' )->name('plugins_download');

	Route::any( '/helpscout', 'HelpScoutController@get' );
} );

Route::group(['domain' => sprintf( 'admin.%s', env('APP_DOMAIN') )], function () {
	Route::get( '/', function() {
		return redirect( '/licenses' );
	} );

	Route::get( '/licenses', 'Admin\LicenseController@overview' );
	Route::get( '/licenses/{id}', 'Admin\LicenseController@detail' );
});
