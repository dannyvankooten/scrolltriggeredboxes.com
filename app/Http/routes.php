<?php

Route::group(['domain' => sprintf( 'account.%s', env('APP_DOMAIN') )], function () {

	// plugins
	// TODO: Move to API
	Route::get( '/plugins/{plugin_id_or_slug}/download', 'PluginsController@download' );
	Route::get( '/plugins/{plugin_id_or_slug}/download/sendowl', 'PluginsController@downloadFromSendowl' );

	// auth
	Route::get( '/auth/login/purchase', 'Auth\AuthController@getLoginFromPurchase' );
	Route::get( '/auth/login', 'Auth\AuthController@getLogin' );
	Route::post( '/auth/login', 'Auth\AuthController@postLogin' );
	Route::get( '/auth/logout', 'Auth\AuthController@getLogout' );

	// account
	Route::get( '/', 'AccountController@overview' );
	Route::get( '/licenses/{id}', 'AccountController@license' );

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
	// Controllers Within The "App\Http\Controllers\API" Namespace
	Route::get( '/licenses/create', 'LicenseController@create' );

	// global licenses
	Route::post( '/login', 'AuthController@login' );
	Route::get( '/logout', 'AuthController@logout' );

	// individual plugins
	//Route::post('/licenses/{key}/activations/{plugin_id_or_slug}', 'LicenseController@activate');
	//Route::delete('/licenses/{key}/activations/{plugin_id_or_slug}', 'LicenseController@deactivate');

	Route::get( '/plugins', 'PluginController@index' );
	Route::get( '/plugins/{id}', 'PluginController@get' );
	Route::get( '/plugins/{id}/download', 'PluginController@download' );

	Route::any( '/helpscout', 'HelpScoutController@get' );
} );

Route::group(['domain' => sprintf( 'admin.%s', env('APP_DOMAIN') )], function () {
	Route::get( '/', function() {
		return redirect( '/licenses' );
	} );
	Route::get( '/licenses', 'Admin\LicenseController@overview' );
	Route::get( '/licenses/{id}', 'Admin\LicenseController@detail' );
	Route::get( '/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index' );
});
