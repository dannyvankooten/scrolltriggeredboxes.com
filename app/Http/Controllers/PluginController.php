<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Plugin;
use App\Services\PluginDownloader;

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
	 *
	 * @return Response
	 */
	public function download( $id ) {
		/** @var Plugin $plugin */
		$plugin = Plugin::where('id', $id)->firstOrFail();

		$downloader = new PluginDownloader( $plugin );
		$filename = $downloader->download();

		return response()->download( $filename );
	}
}