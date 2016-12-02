<?php

namespace App\Http\Controllers;

use App\Jobs\EmailLicenseDetails;
use App\Jobs\UpdateInvoiceContact;
use App\Jobs\UpdateGatewayCustomer;
use App\Jobs\UpdateGatewaySubscriptions;
use App\Services\Payments\Agent;
use App\Services\Purchaser;
use App\User;
use Exception;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class AccountController extends Controller {

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
	public function __construct( Guard $auth, Log $log ) {
		$this->auth = $auth;
		$this->log = $log;

		$this->middleware('auth.user', [ 'except' => [ 'register', 'create' ] ]);
		$this->middleware('guest', [ 'only' => [ 'register', 'create' ] ]);
	}

	/**
	 *
	 */
	public function editBillingInfo() {
		return view( 'account.edit-billing-info', [ 'user' => $this->auth->user() ] );
	}

	/**
	 *
	 */
	public function editPaymentMethod() {
		return view( 'account.edit-payment-method', [ 'user' => $this->auth->user() ] );
	}

	public function editCredentials() {
		return view( 'account.edit-credentials', [ 'user' => $this->auth->user() ] );
	}

	/**
	 * @param Request $request
	 * @param Redirector $redirector
	 *
	 * @return RedirectResponse
	 */
	public function updateCredentials( Request $request, Redirector $redirector ) {

		/** @var User $user */
		$user = $this->auth->user();

		if( ! $user->verifyPassword( $request->input('current_password') ) ) {
			return $redirector->back()->withErrors(['The given current password does not match with your actual password.']);
		}

		// validate request
		$this->validate( $request, [
			'user.email' => 'required|email|unique:users,email,'. $user->id,
			'current_password' => 'required',
			'new_password' => 'sometimes|confirmed|between:6,60'
		], [
			'unique' => 'That email address is already taken'
		]);

		// update email address
		$user->email = $request->input('user.email');

		// only update password if given
		$new_password = $request->input('new_password', null);
		if( $new_password ) {
			$user->setPassword( $new_password );
		}

		$user->save();
		return $redirector->back()->with('message', 'Changes saved!');
	}

	/**
	 * @param Request $request
	 * @param Redirector $redirector
	 *
	 * @return RedirectResponse
	 */
	public function updateBillingInfo( Request $request, Redirector $redirector  ) {

		/** @var User $user */
		$user = $this->auth->user();

		// validate new values
		$this->validate( $request, [
			'user.name' 		=> 'required',
			'user.country' 		=> 'required',
			'user.vat_number' 	=> 'sometimes|vat_number'
		], array(
			'vat_number' => 'Please supply a valid VAT number.'
		));

        $vatNumber = $request->input('user.vat_number');
        $country = $request->input('user.country');
        $vatInfoChanged = $user->vat_number != $vatNumber || $user->country != $country;

		$user->name = $request->input('user.name');
		$user->country = $country;
		$user->vat_number = $vatNumber;
		$user->address = $request->input( 'user.address' );
		$user->city = $request->input('user.city');
		$user->zip = $request->input('user.zip');
		$user->state = $request->input('user.state');
		$user->company = $request->input('user.company');
		$user->save();

		$this->dispatch(new UpdateInvoiceContact($user));
		$this->dispatch(new UpdateGatewayCustomer($user));

        if($vatInfoChanged) {
            $this->dispatch(new UpdateGatewaySubscriptions($user));
        }

		return $redirector->back()->with('message', 'Changes saved!');
	}

	/**
	 * @param Request $request
	 * @param Redirector $redirector
	 * @param Agent $agent
	 *
	 * @return RedirectResponse
	 */
	public function updatePaymentMethod( Request $request, Redirector $redirector, Agent $agent  ) {

        /** @var User $user */
        $user = $this->auth->user();
        $this->validate($request, [
            'payment_method' => 'required|in:stripe,braintree',
            'payment_token' => 'required',
        ]);

        $paymentMethod = $request->input('payment_method');
        $paymentToken = $request->input('payment_token');
        $user->payment_method = $paymentMethod;

        try {
            $user = $agent->updatePaymentMethod($user, $paymentToken);
        } catch (Exception $e) {
            $this->log->error(sprintf('Error updating payment method for user %s: %s', $user->email, $e->getMessage()));
            return $redirector->back()->with('error', $e->getMessage());
        }

        $user->card_last_four = $request->input('user.card_last_four');
        $user->paypal_email = $request->input('user.paypal_email');
        $user->save();
        return $redirector->back()->with('message', 'Changes saved!');
	}

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function overview() {
		/** @var User $user */
		$user = $this->auth->user();
		return view( 'account.overview', [ 'user' => $user ] );
	}

    /**
     * @return View
     */
	public function register() {
		return view('account.register');
	}

	/**
	 * @return View
	 */
	public function welcome() {
		/** @var User $user */
		$user = $this->auth->user();
		$license = $user->licenses->first();
		return view('account.welcome', [ 'user' => $user, 'license' => $license ]);
	}

    /**
     * @param Request $request
     * @param Purchaser $purchaser
     * @param Redirector $redirector
     *
     * @param Agent $agent
     * @return RedirectResponse
     */
	public function create( Request $request, Purchaser $purchaser, Redirector $redirector, Agent $agent ) {

		// validate new values
		$this->validate( $request, [
			'user.name' 		=> 'required',
			'user.email' 		=> 'required|email|unique:users,email',
			'user.country' 		=> 'required',
			'user.vat_number' 	=> 'sometimes|vat_number',
			'password' 			=> 'required|confirmed|min:6',
            'payment_method'    => 'required|in:stripe,braintree',
			'payment_token' 	=> 'required',
			'plan' 			    => 'required|in:personal,developer',
		], array(
			'email' => 'Please enter a valid email address.',
			'vat_number' => 'Please enter a valid VAT number.',
			'unique' => 'That email address is already in use. <a href="/login">Did you mean to log-in instead</a>?'
		));

		// create user
		$user = new User($request->input('user'));
		$user->setPassword($request->input('password'));
        $user->payment_method = $request->input('payment_method');
        $user->save();

		// log user in automatically
        $this->auth->loginUsingId($user->id);
        $this->log->info( sprintf( 'New user registration: #%d  %s <%s>', $user->id, $user->name, $user->email ) );

		// create customer for payments
		try {
            $user = $agent->updatePaymentMethod($user, $request->input('payment_token'));
		} catch( Exception $e ) {
			$this->log->error( 'Payment customer creation failed: ' . $e->getMessage() );
			return $redirector->to('/edit/payment')->with('error', $e->getMessage());
		}

        $user->save();

		// proceed with payment + creating license
		$plan = $request->input('plan', 'personal');
		$interval = $request->input('interval', 'year') === 'month' ? 'month' : 'year';
        $license = $purchaser->license($user, $plan, $interval);

        try {
            $agent->createSubscription($license);
        } catch( Exception $e ) {
            $errorMessage = $e->getMessage();
            $errorMessage .= ' Please review your payment method.';
            $this->log->error( sprintf( 'Failed to create %s subscription for %s', $user->payment_method, $user->email, $e->getMessage() ) );
            return $redirector->to('/edit/payment')->with('error', $errorMessage );
        }

        // all good!
        $license->save();
        $this->dispatch(new EmailLicenseDetails($license));
        $this->log->info( sprintf( 'New license key for %s (per %s, %s plan)', $user->email, $interval, $plan ) );

		return $redirector->to('/welcome')->with('message', "Success. You're in!");
	}
}