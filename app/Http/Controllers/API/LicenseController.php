<?php namespace App\Http\Controllers\API;

use App\Events\UserCreated;
use App\Http\Requests;
use App\Http\Controllers\Controller;
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
			$user = new User();
			$user->email = $request->input('buyer_email');
			$user->name = $request->input('buyer_name');
			$raw_password = str_random( 16 );
			$user->password = Hash::make( $raw_password );
			$user->save();
			event(new UserCreated($user, $raw_password));
		}

		// get local information about SendOwl product
		$plan = Plan::where('sendowl_product_id', $request->input('product_id'))->firstOrFail();

		// was a key previously generated for this order?
		$license = License::where('sendowl_order_id', $request->input('order_id'))->first();
		if( ! $license ) {
			// generate a truly unique key
			$key_exists = true;
			while( $key_exists ) {
				$key = $this->generate_key();
				$key_exists = License::where('license_key', $key)->first();
			}

			// create new license with this key
			$license = new License([
				'license_key' => $key,
				'expires_at' => new \DateTime("+1 year"),
				'sendowl_order_id' => $request->input('order_id')
			]);

			// attach license to user
			$license->user()->associate($user);
			$license->plan()->associate($plan);

			// save the license
			$license->save();
		}

		// does licence grant access to plugin associated with this product already?
		$license->grantAccessTo($plan->plugins);

		// if this product its site_limit is higher than the one previous set (from another product in the same bundle), use this one. <3
		if( $plan->site_limit > $license->site_limit ) {
			$license->site_limit = $plan->site_limit;
			$license->save();
		}

		return $license->license_key;
	}

	/**
	 * Generate a random serial key of 25 characters
	 * Format: XXXXX-XXXXX-XXXXX-XXXXX
	 *
	 * @return string
	 */
	private function generate_key() {
		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		$serial = '';

		for ($i = 0; $i < 4; $i++) {
			for ($j = 0; $j < 5; $j++) {
				$serial .= $tokens[rand(0, 35)];
			}

			if ($i < 3) {
				$serial .= '-';
			}
		}

		return $serial;
	}

}
