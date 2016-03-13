<?php namespace App\Http\Middleware;

use Closure;
use HelpScoutApp\DynamicApp as HelpScoutApp;

class VerifyHelpScoutSignature {

	/**
	 * @var HelpScoutApp
	 */
	protected $helpscout;

	/**
	 * VerifyHelpScoutSignature constructor.
	 *
	 * @param HelpScoutApp $helpscout
	 */
	public function __construct( HelpScoutApp $helpscout) {
		$this->helpscout = $helpscout;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next )
	{
		if( ! env('VERIFY_SIGNATURES', true ) ) {
			return $next($request);
		}

		if( ! $this->helpscout->isSignatureValid() ) {
			return response( 'Invalid signature', '401' );
		}

		return $next($request);
	}

}
