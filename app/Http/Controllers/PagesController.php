<?php namespace App\Http\Controllers;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Incraigulous\Contentful\Facades\Contentful;


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

}
