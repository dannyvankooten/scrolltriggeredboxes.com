<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

	public function getPurchaseConfirmation() {
		return view('pages.purchase-confirmation');
	}

	public function getRefundPolicy() {
		return view('pages.refund-policy');
	}

}
