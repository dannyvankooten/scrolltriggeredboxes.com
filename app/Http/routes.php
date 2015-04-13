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
Route::get('/', 'WelcomeController@index');
Route::get('/api/licenses/create', 'LicenseController@create');
Route::get('/api/licenses/{key}', 'LicenseController@get');
Route::post('/api/licenses/{key}/sites', 'LicenseController@toggleSite');