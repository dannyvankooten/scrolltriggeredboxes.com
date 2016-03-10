<?php namespace App\Http\Controllers;

use App\Contentful\Repositories\PluginRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin;
use Illuminate\Support\Facades\Storage;

class PluginsController extends Controller {

	public function __construct() {
		$this->middleware('auth.user', [ 'only' => 'download' ]);
		$this->middleware('auth.user+license', [ 'only' => 'download' ]);
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
