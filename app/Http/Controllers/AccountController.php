<?php

namespace App\Http\Controllers;

use App\Jobs\EmailLicenseDetails;
use App\Services\Charger;
use App\Subscription;
use App\User;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\License;
use DateTime;

class AccountController extends Controller {

	/**
	 * @var Guard
	 */
	protected $auth;

	/**
	 * AccountController constructor.
	 *
	 * @param Guard $auth
	 */
	public function __construct( Guard $auth ) {
		$this->auth = $auth;
		$this->middleware('auth.user', [ 'except' => [ 'register', 'create' ] ]);
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
	 */
	public function updateCredentials( Request $request ) {
		$user = $this->auth->user();

		if( ! $user->verifyPassword( $request->input('current_password') ) ) {
			return redirect()->back()->withErrors(['The given current password does not match with your actual password.']);
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
			$user->password = $user->setPassword( $new_password );
		}

		$user->save();
		return redirect()->back()->with('message', 'Changes saved!');
	}

	/**
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateBillingInfo( Request $request ) {
		$user = $this->auth->user();

		// validate new values
		$this->validate( $request, [
			'user.country' => 'required',
			'user.vat_number' => 'sometimes|vat_number'
		], array(
			'vat_number' => 'Please supply a valid VAT number.'
		));

		$user->fill( $request->input('user') );
		$user->save();
		return redirect()->back()->with('message', 'Changes saved!');
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updatePaymentMethod( Request $request ) {
		$user = $this->auth->user();

		$this->validate( $request, [
			'payment_token' => 'required'
		]);

		$user->fill($request->input('user'));

		$charger = new Charger();
		$user = $charger->customer($user, $request->input('payment_token'));

		$user->save();

		return redirect()->back()->with('message', 'Changes saved!');
	}

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function overview() {
		$user = $this->auth->user();
		return view( 'account.overview', [ 'user' => $user ] );
	}

	/**
	 *
	 */
	public function register() {
		return view('account.register');
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function create( Request $request ) {

		// validate new values
		$this->validate( $request, [
			'payment_token' => 'required',
			'user.email' => 'required|email|unique:users,email',
			'user.country' => 'required',
			'user.vat_number' => 'sometimes|vat_number',
			'password' => 'required|confirmed|between:6,60'
		], array(
			'email' => 'Please enter a valid email address.',
			'vat_number' => 'Please enter a valid VAT number.',
			'unique' => 'That email address is already in use. <a href="/login">Did you mean to log-in instead</a>?'
		));

		// create user
		$user = new User($request->input('user'));
		$user->setPassword($request->input('password'));
		$user->save();

		// log user in automatically
		$this->auth->loginUsingId( $user->id );

		// create customer in Stripe
		$charger = new Charger();
		$charger->customer($user, $request->input('payment_token'));
		$user->save();

		// proceed with charge
		$interval = $request->input('interval') == 'month' ? 'month' : 'year';
		$quantity = (int) $request->input('quantity', 1);

		$discount_percentage = $quantity > 5 ? 30 : $quantity > 1 ? 20 : 0;
		$item_price = $interval == 'month' ? 5 : 50;

		// calculate amount based on number of activations & discount
		$amount = $item_price * $quantity;
		if( $discount_percentage > 0 ) {
			$amount = $amount * ( ( 100 - $discount_percentage ) / 100 );
		}

		// First, create license.
		$license = new License();
		$license->license_key = License::generateKey();
		$license->user()->associate( $user );
		$license->site_limit = $quantity;
		$license->expires_at = new \DateTime("now");
		$license->save();

		// Then, create subscription
		$subscription = new Subscription([
			'interval' => $interval,
			'active' => 1,
			'next_charge_at' => new DateTime("now")
		]);
		$subscription->amount = $amount;
		$subscription->license()->associate( $license );
		$subscription->user()->associate( $user );
		$subscription->save();

		// finally, charge subscription so that license starts
		$charger->subscription( $subscription );

		// dispatch job to send license details over email
		$this->dispatch( new EmailLicenseDetails( $license ) );

		return redirect('/')->with('message', "You're all set!");
	}
}