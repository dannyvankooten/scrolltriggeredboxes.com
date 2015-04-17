<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {

	/**
	 * Handle an authentication attempt.
	 *
	 * @return Response
	 */
	public function authenticate( Request $request )
	{
		$email = $request->input('email');
		$password = $request->input('password');
		$remember = $request->input('remember_me');

		if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
			return redirect()->intended('dashboard');
		}
	}

}