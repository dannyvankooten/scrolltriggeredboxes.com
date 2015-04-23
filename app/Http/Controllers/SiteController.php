<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SiteController extends Controller {

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{


		return view('pages.home');
	}

	public function pricing()
	{
		return view('pages.pricing');
	}

}
