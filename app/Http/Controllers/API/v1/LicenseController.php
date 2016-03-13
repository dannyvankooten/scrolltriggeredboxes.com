<?php namespace App\Http\Controllers\API\v1;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Jobs\CreateUser;
use App\Jobs\PurchasePlan;
use App\User, App\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LicenseController extends Controller {

	/**
	 * LicenseController constructor.
	 */
	public function __construct() {
		$this->middleware('sendowl.signature');
	}

	/**
	 * Create a new license key for a new SendOwl order or add plugin access to an existing one (for bundle orders)
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function create( Request $request)
	{
		// query user by email
		$user = User::where('email', $request->input('buyer_email'))->first();
		if( ! $user ) {
			$command = new CreateUser( $request->input('buyer_email'), $request->input('buyer_name' ) );
			$this->dispatch( $command );
			$user = $command->getUser();
		}

		// get local information about SendOwl product
		$plan = Plan::where('sendowl_product_id', $request->input('product_id'))->firstOrFail();

		$command = new PurchasePlan( $plan, $user, $request->input('order_id') );
		$this->dispatch( $command );

		return $command->getLicense()->license_key;
	}

}
