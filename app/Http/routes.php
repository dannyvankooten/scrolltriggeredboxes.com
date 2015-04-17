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
Route::get('/', 'SiteController@index');
Route::get('/plugins', 'PluginsController@index');
Route::get('/plugins/{slug}', 'PluginsController@show');



// download URL for SendOwl
Route::get('/download/plugin/{plugin_id_or_slug}', 'DownloadController@plugin' );

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
