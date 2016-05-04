<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\PluginDownloader;
use GuzzleHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PluginController extends Controller {


	public function __construct() {
		$this->middleware('auth.license', [ 'only' => 'download' ]);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function index( Request $request ) {

		$pluginQuery = Plugin::query()->where('status', 'published');

		if( $request->input('ids') ) {
			$pluginQuery->whereIn( 'id',explode(',', $request->input('ids') ) );
		} else if( $request->input('sids') ) {
			$pluginQuery->whereIn( 'sid', explode(',', $request->input('sids') ) );
		}

		/** @var Plugin[] $plugins */
		$plugins = $pluginQuery->get();

		$response = [
			'data' => []
		];

		$wpFormat = $request->input('format','') === 'wp';

		foreach( $plugins as $plugin ) {
			$response['data'][] = ( $wpFormat ? $plugin->toWpArray() : $plugin->toArray() );
		}
		return new JsonResponse($response);
	}

	/**
	 * Get a plugin by its ID or slug
	 *
	 * @param int|string $id
	 *
	 * @return JsonResponse
	 */
	public function get($id)
	{
		/** @var Plugin $plugin */
		$plugin = Plugin::where('id', $id)->orWhere('sid', $id)->firstOrFail();

		// build response
		$response = [
			'data' => $plugin->toWpArray()
		];

		return new JsonResponse($response);
	}

	/**
	 * @param int|string $id
	 * @param Request $request
	 *
	 * @return BinaryFileResponse
	 */
	public function download($id, Request $request) {

		/** @var Plugin $plugin */
		$plugin = Plugin::where('id', $id)->orWhere('sid', $id)->firstOrFail();

		// is a specific version specified? if not, use latest.
		$version = preg_replace( '/[^0-9\.]/', "" , $request->input( 'version', '' ) );

		$downloader = new PluginDownloader( $plugin );
		$file = $downloader->download( $version );
		$filename = $plugin->slug . '.zip';

		$response = new BinaryFileResponse( $file, 200 );
		$response->setContentDisposition( 'attachment', $filename );
		return $response;
	}
}
