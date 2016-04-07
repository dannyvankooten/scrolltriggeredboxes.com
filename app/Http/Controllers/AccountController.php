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
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateBillingInfo( Request $request ) {
		$user = $this->auth->user();
		// todo: validate vat number
		// todo: verify email address before changing
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
	public function overview( ) {
		$user = $this->auth->user();

		return view( 'account.overview', [ 'user' => $user ] );
	}
}