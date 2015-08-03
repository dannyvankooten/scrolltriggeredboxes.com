<?php namespace App\Http\Controllers\API\v1;

use App\Events\UserCreated;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Jobs\CreateUser;
use App\Jobs\PurchasePlan;
use DB, App\License, App\User, App\Activation, App\Plan, App\Plugin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class LicenseController extends Controller {

	/**
	 * Create a new license key for a new SendOwl order or add plugin access to an existing one (for bundle orders)
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function create( Request $request)
	{
		if(env('VERIFY_SIGNATURES', true)) {
			$sendowl_config     = config( 'services.sendowl' );
			$message            = sprintf( "buyer_email=%s&buyer_name=%s&order_id=%s&product_id=%d&secret=%s",
				$request->input( 'buyer_email' ),
				$request->input( 'buyer_name' ),
				$request->input( 'order_id' ),
				$request->input( 'product_id' ),
				$sendowl_config['api_secret'] );
			$key                = $sendowl_config['api_key'] . '&' . $sendowl_config['api_secret'];
			$expected_signature = base64_encode( hash_hmac( 'sha1', $message, $key, true ) );
			if ( $expected_signature != $request->input( 'signature' ) ) {
				abort( 403 );
			}
		}

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
