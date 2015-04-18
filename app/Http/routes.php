<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// public pages
Route::get('/', 'SiteController@index');
Route::get('/pricing', 'SiteController@pricing');
Route::get('/plugins', 'PluginsController@index');
Route::get('/plugins/{slug}', 'PluginsController@show');

// account
Route::get('/account', 'AccountController@overview');
Route::get('/account/licenses/{id}', 'AccountController@license');
Route::delete('/account/licenses/{license_id}/activations/{activation_id}', 'AccountController@deleteActivation');


// download URL for SendOwl
Route::get('/download/plugin/{plugin_id_or_slug}', 'DownloadController@plugin' );

// API Url's
Route::group(['prefix' => '/api', 'namespace' => 'API'], function()
{
	// Controllers Within The "App\Http\Controllers\API" Namespace
	Route::get('/licenses/create', 'LicenseController@create');
	Route::get('/licenses/{key}', 'LicenseController@get');
	Route::post('/licenses/{key}/activations/{plugin_id_or_slug}', 'LicenseController@activate');
	Route::delete('/licenses/{key}/activations/{plugin_id_or_slug}', 'LicenseController@deactivate');

	Route::get('/plugins/{id_or_slug}', 'PluginController@get');
	Route::get('/plugins/{id_or_slug}/download', 'PluginController@download');
});

// auth
Route::controller('/','AuthController');