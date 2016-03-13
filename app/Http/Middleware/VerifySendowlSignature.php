<?php namespace App\Http\Middleware;

use Closure;

class VerifySendowlSignature {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// Proceed if signature verification is disabled
		if( ! env('VERIFY_SIGNATURES', true ) ) {
			return $next($request);
		}

		$sendowl_config     = config( 'services.sendowl' );
		$message            = sprintf( "buyer_email=%s&buyer_name=%s&order_id=%s&product_id=%d&secret=%s",
			$request->input( 'buyer_email' ),
			$request->input( 'buyer_name' ),
			$request->input( 'order_id' ),
			$request->input( 'product_id' ),
			$sendowl_config['api_secret'] );
		$key                = $sendowl_config['api_key'] . '&' . $sendowl_config['api_secret'];
		$expected_signature = base64_encode( hash_hmac( 'sha1', $message, $key, true ) );

		// Compare signature with expected signature and only proceed if valid.
		if ( hash_equals( $expected_signature, $request->input( 'signature' ) ) ) {
			return $next($request);
		}

		return response( 'Invalid signature', '401' );

	}

}
