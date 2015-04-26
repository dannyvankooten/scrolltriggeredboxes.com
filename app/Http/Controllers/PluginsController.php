<?php namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin, App\Activation, App\License;
use Illuminate\Support\Facades\Storage;

class PluginsController extends Controller {

	public function __construct() {
		$this->middleware('auth.user', [ 'only' => 'download' ]);
		$this->middleware('auth.user+license', [ 'only' => 'download' ]);
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		$plugins = Plugin::all();

		return view('plugins.index', [ 'plugins' => $plugins ]);
	}

	/**
	 * Show the application welcome screen to the user.
	 * @param string $url
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

		return view( 'plugins.general', [ 'plugin' => $plugin ]);
	}

	/**
	 * @param string $url
	 * @param Request $request
	 * @return Response
	 */
	public function download($url, Request $request )
	{
		// get plugin
		$plugin = Plugin::where('url', $url)->firstOrFail();

		// get storage
		$storage = Storage::disk('local');

		$version = preg_replace( "/[^0-9\.]/", "" ,$request->input( 'version', $plugin->version ) );

		// check if plugin file exists
		$file = sprintf( 'app/plugins/%s/%s-%s.zip', $plugin->slug, $plugin->slug, $version );

		$exists = $storage->exists( $file );
		if( $exists ) {
			return response()->download( storage_path( $file ) );
		}

		return response('File unavailable.', 404);
	}

}
