<?php namespace App\Http\Controllers;

class PagesController extends Controller {

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return view('pages.home');
	}

	public function getPricing()
	{
		return view('pages.pricing');
	}

	public function getContact() {
		return view('pages.contact');
	}

	public function getRefundPolicy() {
		return view('pages.refund-policy');
	}

	public function getAbout()
	{
		return view('pages.about');
	}

}
