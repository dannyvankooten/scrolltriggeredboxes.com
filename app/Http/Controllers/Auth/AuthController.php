<?php namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller {

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var Guard
	 */
	protected $auth;

	public function __construct( Guard $auth, User $user ) {
		$this->user = $user;
		$this->auth = $auth;

		//$this->middleware('guest', ['except' => ['logout']]);
	}

	/**
	 * Handle an authentication attempt.
	 *
	 * @return Response
	 */
	public function postLogin( Request $request )
	{
		if ($this->auth->attempt($request->only('email', 'password'), $request->input('remember_me'))) {
			return redirect('/');
		} else {
			return redirect()->back()->withErrors([
				'email' => 'The credentials you entered did not match our records.'
			]);
		}
	}

	/**
	 * @return \Illuminate\View\View
	 */
	public function getLogin( ) {
		return view( 'auth.login' );
	}

	/**
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function getLogout() {
		$this->auth->logout();
		return redirect('/');
	}

}