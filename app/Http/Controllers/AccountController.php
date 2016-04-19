<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateInvoiceContact;
use App\Services\Charger;
use App\Services\Purchaser;
use App\User;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;


class AccountController extends Controller {

	/**
	 * @var SessionGuard
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

		/** @var User $user */
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
			'user.name' 		=> 'required',
			'user.country' 		=> 'required',
			'user.vat_number' 	=> 'sometimes|vat_number'
		], array(
			'vat_number' => 'Please supply a valid VAT number.'
		));

		$user->fill( $request->input('user') );
		$user->save();

		$this->dispatch(new UpdateInvoiceContact($user));

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
	 * @param Purchaser $purchaser
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function create( Request $request, Purchaser $purchaser ) {

		// validate new values
		$this->validate( $request, [
			'user.name' 		=> 'required',
			'user.email' 		=> 'required|email|unique:users,email',
			'user.country' 		=> 'required',
			'user.vat_number' 	=> 'sometimes|vat_number',
			'password' 			=> 'required|confirmed|between:6,60',
			'payment_token' 	=> 'required',
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
		$this->auth->loginUsingId($user->id);

		// create customer in Stripe
		$purchaser->user($user, $request->input('payment_token'));

		// proceed with charge
		$quantity = (int) $request->input('quantity', 1);
		$interval = $request->input('interval') == 'month' ? 'month' : 'year';
		$purchaser->license($user, $quantity, $interval);

		return redirect('/')->with('message', "You're all set!");
	}
}