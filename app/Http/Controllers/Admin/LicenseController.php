<?php namespace App\Http\Controllers\Admin;

use App\Activation;
use App\Plugin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\License;

class LicenseController extends Controller {

	public function __construct() {
		$this->middleware('auth.admin');
	}


	/**
 * @return \Illuminate\View\View
 */
	public function overview() {

		$licenses = License::with('user')->get();

		return view( 'admin.licenses.overview', [ 'licenses' => $licenses ] );
	}

	/**
	 * @return \Illuminate\View\View
	 */
	public function detail($id) {

		$license = License::with(['activations', 'user'])->findOrFail($id);

		return view( 'admin.licenses.detail', [ 'license' => $license ] );
	}



}