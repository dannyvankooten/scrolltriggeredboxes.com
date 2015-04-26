<?php namespace App\Http\Controllers;

use App\Activation;
use App\Plugin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

use App\License;

class AccountController extends Controller {

	public function __construct() {
		$this->middleware('auth.user');
	}

	public function overview( ) {
		$user = Auth::user();
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
		$user = Auth::user();

		// check if license belongs to user
		if( $license->user->id != $user->id ) {
			abort( 403 );
		}

		return view( 'account.license', [ 'license' => $license ] );
	}

	public function deleteActivation( $license_id, $activation_id ) {
		$activation = Activation::find($activation_id)->firstOrFail();
		$user = Auth::user();

		// check if activation belongs to user
		if( $activation->license->id !== $license_id || $activation->license->user->id !== $user->id ) {
			abort(403);
		}

		$activation->delete();
		return redirect()->back();
	}

}