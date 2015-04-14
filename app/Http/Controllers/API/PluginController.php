<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin, App\Activation, App\License;
use Illuminate\Support\Facades\Storage;

class PluginController extends Controller {


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
		$plugin = Plugin::where('id', $id_or_slug)->orWhere('slug', $id_or_slug)->firstOrFail();

		// build response
		$response = [
			'slug' => $plugin->slug,
			'version' => $plugin->version
		];

		return response()->json($response);
	}

	/**
	 * @param $id_or_slug
	 * @param Request $request
	 * @return mixed
	 */
	public function download($id_or_slug, Request $request) {
		// then, retrieve plugin that user is trying to activate
		$plugin = Plugin::where('id', $id_or_slug)->orWhere('slug', $id_or_slug)->firstOrFail();

		// now, check request license and activation
		// if key or site not given, abandon.
		$key = $request->input('license');
		$url = $request->input('url');
		$domain = parse_url( $url, PHP_URL_HOST );

		if( ! $key || ! $url ) {
			return response('You do not have access to this download.', 403);
		}

		// find activation for this plugin w/ license & domain combination
		$activation = Activation::where('plugin_id', $plugin->id)->where('domain', $domain)->with('license')->first();

		// no activation found for this domain
		if( ! $activation ) {
			return response('You do not have access to this download.', 403);
		}

		// check if license is still valid
		if( $activation->license->isExpired() ) {
			return response('Your license has expired.', 403);
		}

		// is a specific version specified? if not, use latest.
		$version = $request->input('version');
		if( ! $version ) {
			$version = $plugin->version;
		}

		// get storage
		$storage = Storage::disk('local');
		$plugins_folder = 'app/plugins/';

		// check if file exists
		$file = $plugin->slug . '/' . $plugin->slug . '-'. $version . '.zip';
		$exists = $storage->exists( $plugins_folder . $file );
		if( $exists ) {
			return response()->download( storage_path( $plugins_folder . $file ) );
		}

		// serve fallback file
		$file = $plugin->slug . '/' . $plugin->slug . '.zip';
		$exists = $storage->exists( $plugins_folder . $file );

		if( $exists ) {
			return response()->download( storage_path( $plugins_folder . $file ) );
		}

		// serve fallback
		return response( 'Plugin package is temporarily unavailable.', 404 );
	}
}
