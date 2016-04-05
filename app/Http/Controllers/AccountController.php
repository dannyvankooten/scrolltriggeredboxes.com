<?php

namespace App\Http\Controllers;

use App\Activation;
use App\Plugin;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\License;

class AccountController extends Controller {

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
		$plugins = Plugin::where('type','premium')->get();

		return view( 'account.overview', [ 'user' => $user, 'plugins' => $plugins ] );
	}

	/**
	 * @param $id
	 *
	 * @return \Illuminate\View\View
	 */
	public function license($id) {
		$license = License::with('activations')->findOrFail($id);
		$user = $this->auth->user();

		// check if license belongs to user
		if( $license->user->id != $user->id ) {
			abort( 403 );
		}

		return view( 'account.license', [ 'license' => $license ] );
	}

	/**
	 * @param int $license_id
	 * @param int $activation_id
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function deleteActivation( $license_id, $activation_id ) {
		$activation = Activation::find($activation_id)->firstOrFail();
		$user = $this->auth->user();

		// check if activation belongs to user
		if( $activation->license->id !== $license_id || $activation->license->user->id !== $user->id ) {
			abort(403);
		}

		$activation->delete();
		return redirect()->back();
	}

	/**
	 *
	 */
	public function buy() {
		return view('account.buy');
	}

	
	public function postBuy( Request $request ) {
		$user = $this->auth->user();

		// TODO: Pass quantity
		$subscription = $user->newSubscription('main', 'boxzilla-monthly')->create( $request->input( 'stripe_token' ) );

		// TODO: Create new license
	}

	public function invoices() {
		return view('account.invoices');
	}

	public function downloadInvoice( $id ) {
		return $this->auth->user()->downloadInvoice($id, [
			'vendor'  => 'Your Company',
			'product' => 'Your Product',
		]);
	}

}