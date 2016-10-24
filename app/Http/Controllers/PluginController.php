<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log;
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
	 * @var Log
	 */
	protected $log;

	/**
	 * AccountController constructor.
	 *
	 * @param Log $log
	 * @param Guard $auth
	 */
	public function __construct( Guard $auth, Log $log ) {
		$this->auth = $auth;
		$this->log = $log;
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
		/** @var User $user */
		$user = $this->auth->user();

		/** @var Plugin $plugin */
		$plugin = Plugin::findOrFail($id);

		// is a specific version specified? if not, use latest.
		$version = preg_replace( '/[^0-9\.]/', "" , $request->input( 'version', $plugin->getVersion() ) );

		$downloader = new PluginDownloader( $plugin );
		$file = $downloader->download( $version );
		$filename = $plugin->slug . '.zip';

		$this->log->info( sprintf( 'Plugin download: %s v%s for user %s (#%d)', $plugin->sid, $version, $user->email, $user->id ) );

		$response = new BinaryFileResponse( $file, 200 );
		$response->setContentDisposition( 'attachment', $filename );
		return $response;
	}
}