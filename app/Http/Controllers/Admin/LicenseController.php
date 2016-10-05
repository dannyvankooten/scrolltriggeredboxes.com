<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License;
use App\User;
use Carbon\Carbon;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Http\Request;

class LicenseController extends AdminController {
	
	// show license overview
	public function overview( Request $request ) {
	    $orderColumns = [
	        'key' => 'created_at',
            'owner' => 'user_id',
            'activations' => 'site_limit',
            'expires' => 'expires_at'
        ];
        $orderBy = $request->query->get('by', 'key');
        $orderBy = isset( $orderColumns[ $orderBy ] ) ? $orderColumns[ $orderBy ] : $orderColumns['key'];

		$query = License::query();
		$query->with(['user', 'activations']);
		$query->orderBy( $orderBy, $request->query->get('order', 'desc' ));

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
	public function create( Request $request ) {
		$license = new License();
		$license->license_key = License::generateKey();
		$license->expires_at = new \DateTime('+1 year');
		$license->user_id = $request->input('license.user_id', '');
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

        // find user
	    $userId = (int) $data['user_id'];;
        /** @var User $user */
	    $user = User::findOrFail($userId);

		// create license
		$license = new License();
		$license->license_key = License::generateKey();
		$license->expires_at = ! empty( $data['expires_at'] ) ? $data['expires_at'] : strtotime('+1 year');
		$license->site_limit = ! empty( $data['site_limit'] ) ? (int) $data['site_limit'] : 1;
		$license->user_id = (int) $data['user_id'];
		$license->save();

        $this->log->info( sprintf( 'New %d-site license created for user %s.', $license->id, $user->email ) );

		return $redirector->to('/licenses/' . $license->id)->with('message', 'License created');
	}

	// update license details
	public function update( $id, Request $request, Redirector $redirector ) {
		/** @var License $license */
		$license = License::with(['user'])->findOrFail($id);

		$data = $request->request->get('license');

		if( ! empty( $data['site_limit'] ) && $data['site_limit'] != $license->site_limit ) {
			$license->site_limit = (int) $data['site_limit'];
            $this->log->info( sprintf( 'License #%d activation limit changed to %d for user %s.', $license->id, $license->site_limit, $license->user->email ) );
		}

		if( ! empty( $data['expires_at'] ) ) {
		    $newExpiryDate = Carbon::createFromFormat( 'Y-m-d' , $data['expires_at'] );

            // only update when it changed
            if( $license->expires_at->diffInDays($newExpiryDate) > 0) {
                $license->expires_at = $newExpiryDate;

                // update subscription next charge date
                if( $license->subscription ) {
                    $license->subscription->next_charge_at = $license->expires_at->modify('-1 week');
                    $license->subscription->save();
                }

                $this->log->info( sprintf( 'License #%d expiration date changed to %s for user %s.', $license->id, $license->expires_at->format("Y-m-d"), $license->user->email ) );

            }
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
        $license = License::with(['user', 'subscription'])->findOrFail($id);
		$license->subscription->delete();
		$license->delete();

        $this->log->info( sprintf( 'License #%d deleted for user %s.', $license->id, $license->user->email ) );

        return $redirector->to('/users/'. $license->user->id)->with('message', 'License deleted.');
	}



}