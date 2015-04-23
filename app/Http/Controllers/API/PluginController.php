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
		$plugin = Plugin::where('id', $id_or_slug)->orWhere('url', $id_or_slug)->firstOrFail();

		$download_url = url( '/api/plugins/' . $plugin->id .'/download' );
		// build response
		$response = [
			'name' => $plugin->name,
			'slug' => $plugin->slug,
			'version' => $plugin->version,
			'new_version' => $plugin->version,
			'download_link' => $download_url,
			'package' => $download_url,
			'author' => $plugin->author,
			'sections' => [
				'changelog' => $plugin->changelog,
				'description' => $plugin->description
			],
			'requires' => $plugin->requires,
			'tested' => $plugin->tested,
			'homepage' => url( '/plugins/' . $plugin->url ),
			'url' => url( '/plugins/' . $plugin->url ),
			'last_updated' => $plugin->updated_at->format( 'F, Y' ),
			'upgrade_notice' => $plugin->upgrade_notice,
			'banners' => [
				'high' => asset( $plugin->image_path )
			]
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
