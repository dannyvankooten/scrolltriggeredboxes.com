<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;

class PasswordController extends Controller {

	use ResetsPasswords;

	/**
	 * @var string
	 */
	protected $redirectTo = '/';

	/**
	 * Create a new password controller instance.
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}
	
}
