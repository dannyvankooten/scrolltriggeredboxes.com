<?php

namespace App\Http\Controllers;

use App\Services\Charger;
use App\Subscription;
use App\User;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\License;
use Stripe\Stripe;
use DateTime;
use Hash;

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

	/**
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateBillingInfo( Request $request ) {
		$user = $this->auth->user();

		// validate new values
		$this->validate( $request, [
			'user.email' => 'required|email',
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

		Stripe::setApiKey(config('services.stripe.secret'));
		$token = $request->input('token');
		$customer = \Stripe\Customer::create([
			"source" => $token,
			"description" => "User #{$user->id}",
			'email' => $user->email,
		]);

		$user->card_last_four = $customer->sources->data[0]->last4;
		$user->stripe_customer_id = $customer->id;
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
	 * TODO: Refactor this method into jobs
	 * TODO: Validate request before proceeding
	 * TODO: Force user to set password
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function create( Request $request ) {

		// validate new values
		$this->validate( $request, [
			'token' => 'required',
			'user.email' => 'required|email',
			'user.country' => 'required',
			'user.vat_number' => 'sometimes|vat_number'
		], array(
			'email' => 'Please enter a valid email address.',
			'vat_number' => 'Please enter a valid VAT number.'
		));

		// create user
		$user = new User($request->input('user'));
		$user->password = Hash::make( str_random() );
		$user->save();

		// login user
		$this->auth->loginUsingId( $user->id );

		// create customer in Stripe
		Stripe::setApiKey(config('services.stripe.secret'));
		$token = $request->input('token');
		$customer = \Stripe\Customer::create([
			"source" => $token,
			"description" => "User #{$user->id}",
			'email' => $user->email,
		]);

		// update user
		$user->card_last_four = $customer->sources->data[0]->last4;
		$user->stripe_customer_id = $customer->id;
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
		$charger = new Charger();
		try {
			$success = $charger->subscription( $subscription );
		} catch( Exception $e ) {
			return redirect('/')->with('error', $e->getMessage());
		}

		return redirect('/')->with('message', "You're all set!");
	}
}