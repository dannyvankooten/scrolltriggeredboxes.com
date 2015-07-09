<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests\APIRequest;
use App\License, App\Activation;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller {

	public function __construct() {
		$this->middleware( 'auth' );
	}

	/**
	 * Handle an authentication attempt.
	 *
	 * @return Response
	 */
	public function login( Request $request ) {


	}


	public function logout( Request $request ) {


	}

}