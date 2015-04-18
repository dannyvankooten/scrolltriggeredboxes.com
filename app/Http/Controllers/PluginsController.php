<?php namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin, App\Activation, App\License;

class PluginsController extends Controller {

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('plugins.index');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function show($url)
	{
		// get plugin
		$plugin = Plugin::where('url', $url)->firstOrFail();

		// get content from file system
		$view = view();

		if( $view->exists('plugins.' . $url) ) {
			return view('plugins.' . $url, [ 'plugin' => $plugin ]);
		}

		abort(404);
	}

}
