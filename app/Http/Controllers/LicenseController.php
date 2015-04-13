<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB, App\License, App\Site;

use Illuminate\Http\Request;

class LicenseController extends Controller {

	/**
	 * Generate a random serial key
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

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $request)
	{
		// was a key previously generated for this order?
		$license = License::where('sendowl_order_id', $request->input('order_id'))->first();
		if( $license ) {
			return $license->license_key;
		}

		// generate a truly unique key
		$key_exists = true;
		while( $key_exists ) {
			$key = $this->generate_key();
			$key_exists = DB::table('licenses')->where('license_key', $key)->first();
		}

		$license = new License([
			'license_key' => $key,
			'email' => $request->input('buyer_email'),
			'sendowl_order_id' => $request->input('order_id'),
			'sendowl_product_id' => $request->input('product_id'),
			'expires_at' => new \DateTime("+1 year")
		]);

		$license->save();

		return $license->license_key;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function get($key)
	{
		$license = License::where('license_key',$key)->with('sites')->firstOrFail();

		return response()->json( $license->toArray() );
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function toggleSite($key, Request $request)
	{
		// first, retrieve license key
		$license = License::where('license_key',$key)->with('sites')->firstOrFail();

		// check if site is known already
		$site = $license->getSite( $request->input('url'), $request->input('plugin') );
		if( ! $site ) {
			$site = new Site([
				'url' => $request->input('url'),
				'plugin' => $request->input('plugin')
			]);
			$license->sites()->save($site);
		}

		$activate = ( !! $request->input('activate') );
		if( $activate ) {
			if ( ! $site->active && ! $license->isAtLimit() ) {
				$site->active = true;
			}
		} else {
			$site->active = false;
		}

		// save site
		$site->save();
		return response()->json( $site );
	}

}
