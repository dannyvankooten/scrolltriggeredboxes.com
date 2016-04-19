<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\LicenseGuard;
use Illuminate\Http\Request;

use App\License;
use App\Activation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller {

    /**
     * @var LicenseGuard
     */
    protected $auth;

    /**
     * AuthController constructor.
     *
     * @param LicenseGuard $auth
     */
    public function __construct( LicenseGuard $auth ) {
        $this->middleware( [ 'throttle', 'auth.license' ] );
        $this->auth = $auth;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create( Request $request ) {
        /** @var License $license */
        $license = $this->auth->license();

        // check if license is expired
        if( $license->isExpired() ) {
            return response()->json([
                'error' => [
                    'message' => sprintf( "Your license has expired.", $license->site_limit )
                ]
            ]);
        }

        // check if this site is already activated
        $siteUrl = $request->input('site_url');
        $domain = $this->getDomainFromSiteUrl($siteUrl);

        $activation = $license->findDomainActivation($domain);

        if( ! $activation ) {

            // check if license is at limit
            if( $license->isAtSiteLimit() ) {
                return response()->json([
                    'error' => [
                        'message' => sprintf( "Your license is at its activation limit of %d sites.", $license->site_limit )
                    ]
                ]);
            }

            // activate license on given site
            $activation = new Activation([
                'url' => $siteUrl,
                'domain' => $domain
            ]);
            $activation->license()->associate($license);

            Log::info( "Activated license #{$license->id} on {$domain}" );
        }

        $activation->touch();
        $activation->save();

        return response()->json([
            'data' => [
                'message' => sprintf( "Your license was activated, you have %d site activations left.", $license->getActivationsLeft() )
            ],

        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function delete( Request $request ) {

        /** @var License $license */
        $license = $this->auth->license();

        // now, delete activation (aka logout)
        $siteUrl = $request->input('site_url');
        $domain = $this->getDomainFromSiteUrl($siteUrl);
        $activation = $license->findDomainActivation( $domain );
        
        if( $activation ) {
            Log::info( "Deactivated license #{$license->id} on {$domain}" );
            $activation->delete();
        }

        return response()->json([
            'data' => [
                'message' => 'Your license was successfully deactivated. You can use it on any other domain now.'
            ]
        ]);
    }

    /**
     * @param string $siteUrl
     *
     * @return string
     */
    protected function getDomainFromSiteUrl( $siteUrl ) {
        $siteUrl = 'http://' . str_replace( array( 'http://', 'https://', '://' ), '', $siteUrl );
        $domain = parse_url( $siteUrl, PHP_URL_HOST );
        return $domain;
    }

}