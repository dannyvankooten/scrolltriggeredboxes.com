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
	public function show($slug)
	{
		// get plugin
		//$plugin = Plugin::where('slug', $slug)->firstOrFail();

		// get content from file system
		$view = view();

		if( $view->exists('plugins.'.$slug) ) {
			return view('plugins.'.$slug);
		}

		abort(404);
	}

}
