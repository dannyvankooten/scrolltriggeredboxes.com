<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\LicenseGuard;
use App\Services\PluginDownloader;
use Doctrine\DBAL\Query\QueryBuilder;
use GuzzleHttp;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin;
use App\Activation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PluginController extends Controller {

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * @var LicenseGuard
	 */
	protected $auth;

	/**
	 * PluginController constructor.
	 *
	 * @param Log $log
	 * @param LicenseGuard $auth
	 */
	public function __construct( Log $log, LicenseGuard $auth ) {
		$this->log = $log;
		$this->auth = $auth;
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

		$key = $request->input('key', '');
		$activation = false;

		if( ! empty( $key ) ) {
			$activation = Activation::where('key', $key)->first();
		} else {
			// for Backwards Compatibility with early activations, allow auth by license key here.
			$license = $this->auth->license();

			if( $license ) {
				$activation = $license->activations->first();
			}
		}

		if( ! $activation ) {
			return new Response( 'Download is unavailable without a valid key.', 403 );
		}

		/** @var Plugin $plugin */
		$plugin = Plugin::where('id', $id)
			->orWhere('sid', $id)
			->firstOrFail();

		// is a specific version specified? if not, use latest.
		$version = preg_replace( '/[^0-9\.]/', "" , $request->input( 'version', $plugin->getVersion() ) );

		$downloader = new PluginDownloader( $plugin );
		$file = $downloader->download( $version );
		$filename = $plugin->slug . '.zip';

		$this->log->info( sprintf( 'Plugin download: %s v%s for license #%d', $plugin->sid, $version, $activation->license->id ) );

		$response = new BinaryFileResponse( $file, 200 );
		$response->setContentDisposition( 'attachment', $filename );
		return $response;
	}
}
