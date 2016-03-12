<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin;
use Illuminate\Support\Facades\Storage;

class PluginController extends Controller {


	public function __construct() {
		$this->middleware('auth.license', ['only' => 'download']);
		$this->middleware('auth.license+site', ['only' => 'download']);
	}

	/**
	 */
	public function index( Request $request ) {

		$pluginQuery = Plugin::query()->where('status','published');

		if( $request->input('ids') ) {
			$pluginQuery->whereIn( 'id',explode(',', $request->input('ids') ) );
		}

		$plugins = $pluginQuery->get();

		$response = [
			'data' => []
		];

		$wpFormat = $request->input('format','') === 'wp';

		foreach( $plugins as $plugin ) {
			$response['data'][] = ( $wpFormat ? $plugin->toWPArray() : $plugin->toArray() );
		}
		return response()->json($response);
	}

	/**
	 * Get a plugin by its ID or slug
	 *
	 * @param $id_or_slug
	 *
	 * @return Response
	 */
	public function get($id_or_slug)
	{
		// then, retrieve plugin that user is trying to activate
		$plugin = Plugin::where('id', $id_or_slug)->orWhere('url', $id_or_slug)->firstOrFail();

		// build response
		$response = [
			'data' => $plugin->toWpArray()
		];

		return response()->json($response);
	}

	/**
	 * @param int $id
	 * @param Request $request
	 * @return mixed
	 */
	public function download($id, Request $request) {

		// then, retrieve plugin that user is trying to activate
		$plugin = Plugin::find($id)->firstOrFail();

		// is a specific version specified? if not, use latest.
		$version = preg_replace( "/[^0-9\.]/", "" ,$request->input( 'version', $plugin->version ) );

		// check if plugin file exists
		$file = sprintf( 'app/plugins/%s/%s-%s.zip', $plugin->slug, $plugin->slug, $version );
		$storage = Storage::disk('local');
		$exists = $storage->exists( $file );
		if( $exists ) {
			return response()->download( storage_path( $file ) );
		}

		// serve fallback
		return response( 'Plugin package is temporarily unavailable.', 404 );
	}
}
