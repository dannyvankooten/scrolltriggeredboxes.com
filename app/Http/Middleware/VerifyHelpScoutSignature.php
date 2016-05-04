<?php namespace App\Http\Middleware;

use Closure;
use HelpScoutApp\DynamicApp as HelpScoutApp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
	 * @param  Request  $request
	 * @param  Closure  $next
	 *
	 * @return Response
	 */
	public function handle( Request $request, Closure $next )
	{
		if( config('app.env') !== 'production' ) {
			return $next($request);
		}

		if( ! $this->helpscout->isSignatureValid() ) {
			return new Response( 'Invalid signature', 401 );
		}

		return $next($request);
	}

}
