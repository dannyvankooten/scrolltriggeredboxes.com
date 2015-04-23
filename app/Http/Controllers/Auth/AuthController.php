<?php namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
			return redirect('/account');
		} else {
			return redirect()->back()->withErrors([
				'email' => 'The credentials you entered did not match our records.'
			]);
		}
	}

	public function getLogin( ) {
		return view( 'auth.login' );
	}

	public function getLogout() {
		$this->auth->logout();
		return redirect('/');
	}

}