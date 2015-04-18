<?php namespace App\Http\Controllers;

use App\Activation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

use App\License;

class AccountController extends Controller {

	public function __construct() {
		$this->middleware('auth');
	}

	public function overview( ) {
		$user = Auth::user();
		return view( 'account.overview', [ 'user' => $user ] );
	}

	/**
	 * @param $id
	 *
	 * @return \Illuminate\View\View
	 */
	public function license($id) {
		$license = License::find($id)->with('activations')->firstOrFail();
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