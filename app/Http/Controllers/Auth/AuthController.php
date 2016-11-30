<?php namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class AuthController extends Controller {

	use AuthenticatesUsers, ThrottlesLogins;

	/**
	 * Where to redirect users after login / registration.
	 *
	 * @var string
	 */
	protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     * @param Request $request
     */
	public function __construct( Request $request ) {
        // set redirectTo from POST
        $this->redirectTo = $request->request->get('redirect_to', '/' );
        
        // don't protect anything in this controller
		$this->middleware($this->guestMiddleware(), ['except' => 'logout']);
	}

}