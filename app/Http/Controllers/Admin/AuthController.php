<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests\APIRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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