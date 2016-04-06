<?php

namespace App\Http\Controllers;

use App\Activation;
use App\Plugin;
use App\Subscription;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\License;
use Stripe\Stripe;

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
		$this->middleware('auth.user');
	}

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function overview( ) {
		$user = $this->auth->user();
		$plugins = Plugin::where('type','premium')->get();

		return view( 'account.overview', [ 'user' => $user, 'plugins' => $plugins ] );
	}

	/**
	 * @param $id
	 *
	 * @return \Illuminate\View\View
	 */
	public function license($id) {
		$license = License::with('activations')->findOrFail($id);
		$user = $this->auth->user();

		// check if license belongs to user
		if( $license->user->id != $user->id ) {
			abort( 403 );
		}

		return view( 'account.license', [ 'license' => $license ] );
	}

	/**
	 * @param int $license_id
	 * @param int $activation_id
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function deleteActivation( $license_id, $activation_id ) {
		$activation = Activation::find($activation_id)->firstOrFail();
		$user = $this->auth->user();

		// check if activation belongs to user
		if( $activation->license->id !== $license_id || $activation->license->user->id !== $user->id ) {
			abort(403);
		}

		$activation->delete();
		return redirect()->back();
	}

	/**
	 *
	 */
	public function buy() {
		return view('account.buy');
	}


	public function postBuy( Request $request ) {
		$user = $this->auth->user();

		// Setup payment gateway
		// TODO: Move to service provider
		Stripe::setApiKey(config('services.stripe.secret'));

		// Create subscription
		$subscription = Subscription::create([
			'amount' => 10.50,
			'interval' => 'monthly',
			'user_id' => $user->id,
		]);

		// create customer in Stripe
		$token = $request->input('token');
		if( ! empty( $token ) ) {
			$customer = \Stripe\Customer::create([
				"source" => $token,
				"description" => "User #{$user->id}",
				'email' => $user->email,
			]);

			// store customer ID
			$user->card_last_four = $customer->sources->data[0]->last4;
			$user->stripe_customer_id = $customer->id;
			$user->save();
		}

		// charge customer
		$charge = \Stripe\Charge::create([
			"amount" => $subscription->amount * 100, // amount in cents
			"currency" => "USD",
			"customer" => $user->stripe_customer_id
		]);

		// create license
		// TODO: Create new license here


		dd( $charge );
		
		// TODO: Create new license
	}

	public function invoices() {


		$subscription =
		die('Done');

		return view('account.invoices');
	}


}