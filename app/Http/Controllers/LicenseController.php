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
use DateTime;

class LicenseController extends Controller {

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

	public function overview() {
		return view('license.overview', [ 'user' => $this->auth->user() ]);
	}

	/**
	 * @return \Illuminate\View\View
	 */
	public function _new( ) {
		return view('license.new');
	}

	public function process( Request $request  ) {
		$user = $this->auth->user();

		$interval = $request->input('interval') == 'month' ? 'month' : 'year';
		$quantity = (int) $request->input('quantity', 1);

		$discount_percentage = $quantity > 5 ? 30 : $quantity > 1 ? 20 : 0;
		$item_price = $interval == 'month' ? 5 : 50;

		// calculate amount based on number of activations & discount
		$amount = $item_price * $quantity;
		if( $discount_percentage > 0 ) {
			$amount = $amount * ( ( 100 - $discount_percentage ) / 100 );
		}

		// Setup payment gateway
		Stripe::setApiKey(config('services.stripe.secret'));

		try {
			$charge = \Stripe\Charge::create([
				"amount" => $amount * 100, // amount in cents
				"currency" => "USD",
				"customer" => $user->stripe_customer_id
			]);
		} catch(\Stripe\Error\Card $e) {
			// The card has been declined
			// TODO: Do something!
			die('Uh oh. ' . $e);
		}

		// Success!

		$license = License::create([
			'license_key' => License::generateKey(),
			'expires_at' => new \DateTime("+1 $interval"),
			'user_id' => $user->id,
			'site_limit' => $quantity
		]);

		// Create subscription
		$subscription = Subscription::create([
			'amount' => $amount,
			'interval' => $interval,
			'user_id' => $user->id,
			'license_id' => $license->id,
			'active' => 1,
			'next_charge_at' => (new DateTime("+1 $interval"))->modify('-1 week')
		]);

		return redirect('/')->with('message', 'You now have a new license!');
	}


	/**
	 * @param $id
	 *
	 * @return \Illuminate\View\View
	 */
	public function details($id) {
		$license = License::with('activations')->findOrFail($id);
		$user = $this->auth->user();

		// check if license belongs to user
		if( $license->user->id != $user->id ) {
			abort( 403 );
		}

		return view( 'license.details', [ 'license' => $license ] );
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




}