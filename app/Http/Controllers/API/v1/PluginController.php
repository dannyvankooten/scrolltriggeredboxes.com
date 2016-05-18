<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\LicenseGuard;
use App\Services\PluginDownloader;
use GuzzleHttp;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PluginController extends Controller {

	/**
	 * @var LicenseGuard
	 */
	protected $auth;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * PluginController constructor.
	 *
	 * @param Log $log
	 * @param LicenseGuard $auth
	 */
	public function __construct( LicenseGuard $auth, Log $log ) {
		$this->auth = $auth;
		$this->log = $log;
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

		// TODO: Check if license is activated on the actual site requesting the download.

		/** @var Plugin $plugin */
		$plugin = Plugin::where('id', $id)->orWhere('sid', $id)->firstOrFail();

		// is a specific version specified? if not, use latest.
		$version = preg_replace( '/[^0-9\.]/', "" , $request->input( 'version', $plugin->getVersion() ) );

		$downloader = new PluginDownloader( $plugin );
		$file = $downloader->download( $version );
		$filename = $plugin->slug . '.zip';

		$this->log->info( sprintf( 'Plugin download: %s v%s for license #%d', $plugin->sid, $version, $this->auth->license()->id ) );

		$response = new BinaryFileResponse( $file, 200 );
		$response->setContentDisposition( 'attachment', $filename );
		return $response;
	}
}
