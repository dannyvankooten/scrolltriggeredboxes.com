<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin;

class PluginController extends Controller {


	public function __construct() {
		$this->middleware('auth.license', [ 'only' => 'download' ]);
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
			$response['data'][] = ( $wpFormat ? $plugin->toWpArray() : $plugin->toArray() );
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
	public function download($id_or_slug, Request $request) {

		// then, retrieve plugin that user is trying to activate
		/** @var Plugin $plugin */
		$plugin = Plugin::where('id', $id_or_slug)->orWhere('url', $id_or_slug)->firstOrFail();

		// is a specific version specified? if not, use latest.
		$version = preg_replace( "/[^0-9\.]/", "" , $request->input( 'version', '' ) );

		// make sure /downloads directory exists
		$downloads_dir = storage_path( 'downloads' );

		// generate filename
		$filename = sprintf( '%s/%s%s.zip', $downloads_dir, $plugin->slug, ! empty( $version ) ? '-' . $version : '' );

		$exists = file_exists( $filename );
		$last_modified = $exists ? filemtime( $filename ) : 0;
		$some_time_ago = time() - 1500;

		// if file does not exist or is older than 15 minutes, re-download & process
		if( ! $exists || $last_modified < $some_time_ago ) {

			if( ! is_dir( $downloads_dir ) ) {
				mkdir( $downloads_dir );
			}

			// download file
			$client = new GuzzleHttp\Client();

			try {
				$res = $client->request( 'GET', $plugin->getDownloadUrl( $version ) );
			} catch( GuzzleHttp\Exception\ClientException $e ) {
				abort( $e->getCode() );
				exit;
			}

			file_put_contents( $filename, $res->getBody() );

			// open zip & rename index directory because WordPress expects plugin slug as directory name.
			$zip = new \ZipArchive;
			$zip->open( $filename );

			$current_base_directory = $zip->getNameIndex(0);
			$new_base_directory = $plugin->slug . '/';

			for( $i = 0; $i < $zip->numFiles; $i++ ){
				$name = $zip->getNameIndex( $i );
				$newName = str_replace( $current_base_directory, $new_base_directory, $name );
				$zip->renameIndex( $i, $newName );
			}

			$zip->close();
		}

		return response()->download( $filename );
	}
}
