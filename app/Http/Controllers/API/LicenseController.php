<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB, App\License, App\User, App\Activation, App\SendowlProduct, App\Plugin;

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
	public function create(Request $request)
	{
		$email = $request->input('buyer_email');
		if( ! $email ) {
			abort( 403 );
		}

		// query user by email
		$user = User::where('email', $email)->first();
		if( ! $user ) {
			$user = new User();
			$user->email = $email;
			$user->password = Hash::make( str_random( 16 ) );
			$user->save();
		}

		// was a key previously generated for this order?
		$license = License::where('sendowl_order_id', $request->input('order_id'))->first();
		if( ! $license ) {
			// generate a truly unique key
			$key_exists = true;
			while( $key_exists ) {
				$key = $this->generate_key();
				$key_exists = DB::table('licenses')->where('license_key', $key)->first();
			}

			// create new license with this key
			$license = new License([
				'license_key' => $key,
				'expires_at' => new \DateTime("+1 year"),
				'sendowl_order_id' => $request->input('order_id')
			]);

			// attach license to user
			$license->user()->associate($user);

			// save the license
			$license->save();
		}

		// query user by email

		// get local information about SendOwl product
		$product = SendowlProduct::where('id', $request->input('product_id'))->first();

		// does licence grant access to plugin associated with this product already?
		$license->grantAccessTo($product->plugin);

		// if this product its site_limit is higher than the one previous set (from another product in the same bundle), use this one. <3
		if( $product->site_limit > $license->site_limit ) {
			$license->site_limit = $product->site_limit;
			$license->save();
		}

		return $license->license_key;
	}

	/**
	 * Get a license by its key
	 *
	 * @param  string $key
	 * @return Response
	 */
	public function get($key)
	{
		$license = License::where('license_key',$key)->with('activations', 'activations.plugin')->firstOrFail();
		return response()->json($license);
	}

	/**
	 * Activates the given plugin for a given site
	 *
	 * @param string $key
	 * @param string $plugin_id_or_slug
	 * @return Response
	 */
	public function activate($key, $plugin_id_or_slug, Request $request)
	{
		$data = array( 'success' => false );

		// first, retrieve license key
		$license = License::where('license_key', $key)->with('activations')->with('plugins')->firstOrFail();

		// then, retrieve plugin that user is trying to activate
		$plugin = Plugin::where('id', $plugin_id_or_slug)->orWhere('slug', $plugin_id_or_slug)->first();

		// check if license allows access to this plugin
		if( ! $plugin || ! $license->grantsAccessTo($plugin) ) {
			$data['message'] = "The license key used does not seem to grant access to this plugin.";
			return response()->json( $data, 200);
		}

		// get url & parse domain
		$url = $request->input('url');
		$domain = parse_url( $url, PHP_URL_HOST );

		if( ! $url ) {
			$data['message'] = "Something went wrong while trying to active the license for this domain. Please contact support.";
			return response()->json( $data, 200);
		}

		// check if this site is already activated
		$activation = $license->findDomainActivationForPlugin($domain, $plugin);
		if( is_object( $activation ) ) {
			$activation->touch();
		} else {

			if( $license->allowsActivationForPlugin($plugin) ) {
				$activation = new Activation([
					'url' => $url,
					'domain' => $domain
				]);
				$activation->plugin()->associate($plugin);
				$activation->license()->associate($license);
				$activation->save();

			} else {
				$data['message'] = "Your license is expired or at its activation limit.";
				return response()->json( $data );
			}

		}

		$data['message'] = sprintf( "Your license was activated, you have %d site activations left.", $license->getActivationsLeftForPlugin($plugin) );
		$data['success'] = true;
		return response()->json( $data );
	}

	/**
	 * Deactivates the given plugin for a given site
	 *
	 * @param string $key
	 * @param string $plugin_id_or_slug
	 * @return Response
	 */
	public function deactivate($key, $plugin_id_or_slug, Request $request)
	{
		// first, retrieve license key
		$license = License::where('license_key', $key)->with('activations')->with('plugins')->firstOrFail();

		// then, retrieve plugin that user is trying to activate
		$plugin = Plugin::where('id', $plugin_id_or_slug)->orWhere('slug', $plugin_id_or_slug)->first();

		// parse domain from URL
		$url = $request->input('url');
		$domain = parse_url( $url, PHP_URL_HOST );

		// now, delete activation if it actually exists
		$activation = $license->findDomainActivationForPlugin($domain, $plugin);
		if( $activation ) {
			$activation->delete();
		}

		return response()->json( [ 'success' => true ] );
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
