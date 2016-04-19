<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\PluginDownloader;
use GuzzleHttp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin;

class PluginController extends Controller {


	public function __construct() {
		$this->middleware('auth.license', [ 'only' => 'download' ]);
	}

	/**
	 * @param Request $request
	 */
	public function index( Request $request ) {

		$pluginQuery = Plugin::query()->where('status', 'published');

		if( $request->input('ids') ) {
			$pluginQuery->whereIn( 'id',explode(',', $request->input('ids') ) );
		}

		$plugins = $pluginQuery->get();

		$response = [
			'data' => []
		];

		$wpFormat = $request->input('format','') === 'wp';

		foreach( $plugins as $plugin ) {
			$response['data'][] = ( $wpFormat ? $plugin->toWpArray() : $plugin->toArray() );
		}
		return response()->json($response);
	}

	/**
	 * Get a plugin by its ID or slug
	 *
	 * @param mixed $id_or_slug
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
	 * @param mixed $id_or_slug
	 * @param Request $request
	 * @return mixed
	 */
	public function download($id_or_slug, Request $request) {

		/** @var Plugin $plugin */
		$plugin = Plugin::where('id', $id_or_slug)->orWhere('url', $id_or_slug)->firstOrFail();

		// is a specific version specified? if not, use latest.
		$version = preg_replace( '/[^0-9\.]/', "" , $request->input( 'version', '' ) );

		$downloader = new PluginDownloader( $plugin );
		$filename = $downloader->download( $version );

		return response()->download( $filename );
	}
}
