<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class SetApiAccessControlHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $origins = [
            domain_url( '', 'account' )
        ];

        /** @var Response $response */
        $response = $next($request);

        foreach( $origins as $origin ) {
            $response->headers->set( 'Access-Control-Allow-Origin', rtrim( $origin, '/' ) );
        }

        return $response;
    }
}
