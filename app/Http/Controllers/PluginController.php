<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Plugin;

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
}