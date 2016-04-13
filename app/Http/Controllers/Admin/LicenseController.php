<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License;

class LicenseController extends Controller {

	/**
	 * LicenseController constructor.
	 */
	public function __construct() {
		
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