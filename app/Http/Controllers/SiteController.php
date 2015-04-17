<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SiteController extends Controller {

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('home');
	}

}
