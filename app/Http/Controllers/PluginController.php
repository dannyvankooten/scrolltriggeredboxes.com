<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Plugin;
use App\Services\PluginDownloader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PluginController extends Controller {

	/**
	 * @var Guard
	 */
	protected $auth;

	/**
	 * AccountController constructor.
	 *
	 * @param Guard $auth
	 */
	public function __construct( Guard $auth ) {
		$this->auth = $auth;
		$this->middleware('auth.user');
	}

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function overview( ) {
		$user = $this->auth->user();
		$plugins = Plugin::all();
		return view( 'plugins.overview', [ 'user' => $user, 'plugins' => $plugins ] );
	}

	/**
	 * @param int $id
	 * @param Request $request
	 *
	 * @return BinaryFileResponse
	 */
	public function download( $id, Request $request ) {
		/** @var Plugin $plugin */
		$plugin = Plugin::findOrFail($id);

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