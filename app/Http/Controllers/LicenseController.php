<?php

namespace App\Http\Controllers;

use App\Activation;;
use App\Services\Payments\StripeAgent;
use App\Services\Payments\PaymentException;
use App\Services\Purchaser;
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
	public function store( Request $request, Broker $broker, Purchaser $purchaser, Redirector $redirector  ) {

        // validate request
        $this->validate( $request, [
            'plan' 			    => 'required|in:personal,developer',
        ]);

		/** @var User $user */
		$user = $this->auth->user();
		$plan = $request->input('plan', 'personal');
		$interval = $request->input('interval') == 'month' ? 'month' : 'year';

        if($user->payment_method === 'paypal') {
            $approvalUrl = $broker->setupSubscription( $plan, $interval);
            return $redirector->away($approvalUrl);
        }

		try {
			$license = $purchaser->license($user, $plan, $interval);
		} catch( PaymentException $e ) {
			$errorMessage = $e->getMessage();
			$errorMessage .= ' Please <a href="/edit/payment">review your payment method</a>.';

			// write to log
			$price = $purchaser->calculatePrice($plan, $interval);
			$this->log->error( sprintf( 'Payment of USD%s for %s failed: %s', $price, $user->email, $e->getMessage() ) );

			return $redirector->back()->with('error', $errorMessage );
		}

		$this->log->info( sprintf( 'New license key for %s (per %s, %s plan)', $user->email, $interval, $plan ) );

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
		$license = License::with(['activations'])->findOrFail($id);
		
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
     *
     * @param StripeAgent $agent
     * @return RedirectResponse
     */
	public function update($id, Request $request, Redirector $redirector, StripeAgent $agent ) {
		/** @var License $license */
		$license = License::findOrFail($id);

		/** @var User $user */
		$user = $this->auth->user();
		
		// check if license belongs to user
		if( ! $license->belongsToUser( $user ) ) {
			abort( 403 );
		}

		$data = $request->input('license');
        if( ! empty( $data['status'] ) ) {
            try {
                $license->isActive() ? $agent->cancelSubscription($license) : $agent->createSubscription($license);
            } catch( PaymentException $e ) {
                $errorMessage = 'We had some trouble with your payment. <br />Please <a href="/edit/payment">review your payment method</a>.';
                return $redirector->back()->with('error', $errorMessage);
            }
        }

        $license->save();

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