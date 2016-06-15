<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LicenseController extends Controller {
	
	// show license overview
	public function overview( Request $request ) {

		$query = License::query();
		$query->with('user');
		$query->orderBy('created_at', 'desc');

		$filters = $request->query->get('filter', []);

		// apply filters
		foreach( $filters as $filter => $value ) {
			if( ! empty( $value ) ) {
				$value = str_replace( '*', '%', $value );
				$query->where( $filter, 'LIKE', $value );
			}
		}

		$licenses = $query->get();
		return view( 'admin.licenses.overview', [ 'licenses' => $licenses ] );
	}

	// show license details
	public function detail($id) {
		$license = License::with(['activations', 'user'])->findOrFail($id);
		return view( 'admin.licenses.detail', [ 'license' => $license ] );
	}

	// form for creating new license
	public function create() {
		$license = new License();
		$license->license_key = License::generateKey();
		$license->expires_at = new \DateTime('+1 year');
		$license->user_id = '';
		$license->site_limit = 1;
		return view('admin.licenses.create', [ 'license' => $license ]);
	}

	// form for editing a license
	public function edit($id) {
		/** @var License $license */
		$license = License::findOrFail($id);
		return view( 'admin.licenses.edit', [ 'license' => $license ]);
	}

	// store new license
	public function store( Request $request, Redirector $redirector ) {
		$data = $request->request->get('license');
		$license = new License();
		$license->license_key = License::generateKey();
		$license->expires_at = ! empty( $data['expires_at'] ) ? $data['expires_at'] : strtotime('+1 year');
		$license->site_limit = ! empty( $data['site_limit'] ) ? (int) $data['site_limit'] : 1;
		$license->user_id = (int) $data['user_id'];
		$license->save();
		return $redirector->to('/licenses/' . $license->id)->with('message', 'License created');
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