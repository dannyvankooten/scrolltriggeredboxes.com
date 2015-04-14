<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Plugin, App\Activation, App\License;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller {

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function plugin( $plugin_id_or_slug, Request $request )
	{
		// get plugin
		$plugin = Plugin::where('id', $plugin_id_or_slug)->orWhere('slug', $plugin_id_or_slug)->firstOrFail();

		// may we access this file?
		$sendowl_config = config('services.sendowl');
		$expires = $request->input('expires');
		$signature = $request->input('signature');

		if( ! $expires || ! $signature ) {
			return response( 'Access to this file has been denied. Please contact support.', 403 );
		}

		// calculate signature
//		$message = "expires=" . $expires . "&secret=" . $sendowl_config['api_secret'];
//		$key = $sendowl_config['api_key'] .'&'. $sendowl_config['api_secret'];
//		$expected_signature = hash_hmac('sha1', $message, $key );
//		if( $expected_signature != $signature ) {
//			return response( 'Access to this file has been denied. Please contact support.', 403 );
//		}

		// get storage
		$storage = Storage::disk('local');
		$plugins_folder = 'app/plugins/';

		// check if plugin file exists
		$file = $plugin->slug . '/' . $plugin->slug . '-'. $plugin->version . '.zip';
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

		return response('File unavailable.', 404);
	}

}