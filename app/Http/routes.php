<?php

// pages
Route::get('/', 'PagesController@getIndex');
Route::get('/pricing', 'PagesController@getPricing');
Route::get('/contact', 'PagesController@getContact');
Route::get('/refund-policy', 'PagesController@getRefundPolicy');
Route::get('/plugins', 'PluginsController@index');
Route::get('/plugins/{slug}', 'PluginsController@show');

// auth
Route::get('/auth/login/purchase', 'Auth\AuthController@getLoginFromPurchase');
Route::get('/auth/login', 'Auth\AuthController@getLogin' );
Route::post('/auth/login', 'Auth\AuthController@postLogin' );
Route::get('/auth/logout', 'Auth\AuthController@getLogout' );

// account
Route::get('/account', 'AccountController@overview');
Route::get('/account/licenses/{id}', 'AccountController@license');

// todo: allow login out a license from the account page
//Route::delete('/account/licenses/{license_id}/activations/{activation_id}', 'AccountController@deleteActivation');

// download URL for SendOwl
Route::get('/download/plugins/{plugin_id_or_slug}', 'DownloadController@plugin' );

// API Url's
Route::group(['prefix' => '/api', 'namespace' => 'API'], function()
{
	// Controllers Within The "App\Http\Controllers\API" Namespace
	Route::get('/licenses/create', 'LicenseController@create');
	//Route::get('/license', 'LicenseController@get');

	// global licenses
	Route::post('/login', 'AuthController@login');
	Route::get('/logout', 'AuthController@logout');

	// individual plugins
	//Route::post('/licenses/{key}/activations/{plugin_id_or_slug}', 'LicenseController@activate');
	//Route::delete('/licenses/{key}/activations/{plugin_id_or_slug}', 'LicenseController@deactivate');

	Route::get('/plugins/{id_or_slug}', 'PluginController@get');
	Route::get('/plugins/{id_or_slug}/download', 'PluginController@download');
});

// auth
Route::controller( 'password', 'Auth\PasswordController' );