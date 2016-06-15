<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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

	// edit license
	public function edit($id) {
		/** @var License $license */
		$license = License::findOrFail($id);
		return view( 'admin.licenses.edit', [ 'license' => $license ]);
	}

	// update license details
	public function update( $id, Request $request, Redirector $redirector ) {
		/** @var License $license */
		$license = License::findOrFail($id);

		$data = $request->request->get('license');

		if( ! empty( $data['site_limit'] ) ) {
			$license->site_limit = (int) $data['site_limit'];
		}

		if( ! empty( $data['expires_at'] ) ) {
			$license->expires_at = \DateTime::createFromFormat( 'Y-m-d' , $data['expires_at'] );
		}

		$license->save();
		return $redirector->back()->with('message', 'Changes saved.');
	}

	/**
	 * @param $id
	 * @param Redirector $redirector
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 *
	 * @throws \Exception
	 */
	public function destroy( $id, Redirector $redirector ) {
		/** @var License $license */
		$license = License::findOrFail($id);
		$license->subscription->delete();
		$license->delete();
		return $redirector->to('/users/'. $license->user->id)->with('message', 'License deleted.');
	}



}