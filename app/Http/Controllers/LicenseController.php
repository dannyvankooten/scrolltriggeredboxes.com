<?php

namespace App\Http\Controllers;

use App\Activation;;
use App\Services\Charger;
use App\Services\Purchaser;
use App\Subscription;
use App\User;
use Exception;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\License;
use Illuminate\Routing\Redirector;

class LicenseController extends Controller {

	/**
	 * @var SessionGuard
	 */
	protected $auth;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * AccountController constructor.
	 *
	 * @param Guard $auth
	 * @param Log $log
	 */
	public function __construct( Guard $auth, Log $log  ) {
		$this->auth = $auth;
		$this->log = $log;

		$this->middleware('auth.user');
	}

	public function overview() {
		return view('license.overview', [ 'user' => $this->auth->user() ]);
	}

	/**
	 * @return \Illuminate\View\View
	 */
	public function create( ) {
		return view('license.new');
	}

	/**
	 * @param Request $request
	 * @param Purchaser $purchaser
	 * @param Redirector $redirector
	 *
	 * @return RedirectResponse
	 */
	public function store( Request $request, Purchaser $purchaser, Redirector $redirector  ) {

		/** @var User $user */
		$user = $this->auth->user();
		$quantity = (int) $request->input('quantity', 1);
		$interval = $request->input('interval') == 'month' ? 'month' : 'year';

		try {
			$license = $purchaser->license($user, $quantity, $interval);
		} catch( Exception $e ) {
			$errorMessage = $e->getMessage();
			$errorMessage .= ' Please <a href="/edit/payment">review your payment method</a>.';
			return $redirector->back()->with('error', $errorMessage );
		}

		$this->log->info( sprintf( 'New license key for %s (per %s, %d activations)', $user->email, $interval, $quantity ) );

		return $redirector
			->to('/licenses/' . $license->id )
			->with('message', 'You now have a new license!');
	}


	/**
	 * @param $id
	 *
	 * @return \Illuminate\View\View
	 */
	public function details($id) {
		/** @var License $license */
		$license = License::with(['activations', 'subscription'])->findOrFail($id);
		
		/** @var User $user */
		$user = $this->auth->user();

		// check if license belongs to user
		if( ! $license->belongsToUser( $user ) ) {
			abort( 403 );
		}

		return view( 'license.details', [ 'license' => $license ] );
	}

	/**
	 * @param int $id
	 * @param Request $request
	 * @param Redirector $redirector
	 * @param Charger $charger
	 *
	 * @return RedirectResponse
	 */
	public function update($id, Request $request, Redirector $redirector, Charger $charger ) {
		/** @var License $license */
		$license = License::with('subscription')->findOrFail($id);

		/** @var User $user */
		$user = $this->auth->user();
		
		// check if license belongs to user
		if( ! $license->belongsToUser( $user ) ) {
			abort( 403 );
		}

		/** @var Subscription $subscription */
		$subscription = $license->subscription;

		$data = $request->input('subscription');
		if( isset( $data['active'] ) ) {
			$subscription->active = $data['active'];

			// update next charge date
			$subscription->next_charge_at = $license->expires_at->modify('-1 week');
			$subscription->save();
		}

		// if a payment is due, try to charge right away
		if( $subscription->isActive() && $subscription->isPaymentDue() ) {
			try {
				$charger->subscription( $subscription );
			} catch( Exception $e ) {
				$errorMessage = $e->getMessage();
				$errorMessage .= ' Please <a href="/edit/payment">review your payment method</a>.';
				return $redirector->back()->with('error', $errorMessage );
			}
		}

		return $redirector->back()->with('message', 'Changes saved!');
	}

	/**
	 * @param int $license_id
	 * @param int $activation_id
	 * @param Redirector $redirector
	 *
	 * @return RedirectResponse
	 */
	public function deleteActivation( $license_id, $activation_id, Redirector $redirector  ) {
		/** @var Activation $activation */
		$activation = Activation::with(['license', 'license.user'])->findOrFail($activation_id);

		/** @var User $user */
		$user = $this->auth->user();

		// check if activation belongs to user
		if( ! $activation->license->belongsToUser($user) ) {
			abort(403);
		}

		$activation->delete();
		return $redirector->back();
	}
	
}