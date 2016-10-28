<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\LicenseGuard;
use App\Services\Payments\StripeAgent;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\License;
use App\Activation;
use Illuminate\Http\Response;

class LicenseController extends Controller {

    /**
     * @var LicenseGuard
     */
    protected $auth;

    /**
     * @var Log
     */
    protected $log;

    /**
     * AuthController constructor.
     *
     * @param LicenseGuard $auth
     * @param Log $log
     */
    public function __construct( LicenseGuard $auth, Log $log ) {
        $this->middleware( [ 'throttle', 'auth.license' ] );
        $this->auth = $auth;
        $this->log = $log;
    }

    /**
     * Get license details
     *
     * @param StripeAgent $agent
     * @return JsonResponse
     */
    public function getLicense( StripeAgent $agent ) {
        /** @var License $license */
        $license = $this->auth->license();

        return new JsonResponse([
            'data' => [
                'valid' => $license->isValid(),
                'activations' => count( $license->activations ),
                'activation_limit' => $license->site_limit,
                'expires_at' => $license->expires_at->toIso8601String()
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createActivation( Request $request ) {
        /** @var License $license */
        $license = $this->auth->license();

        // check if this domain is already activated
        $siteUrl = trim( $request->input('site_url') );
        $domain = $this->getDomainFromSiteUrl($siteUrl);

        /** @var Activation $activation */
        $activation = $license->findDomainActivation($domain);

        if( ! $activation ) {

            // check if license expired
            if( $license->isExpired() ) {
                return new JsonResponse([
                    'error' => [
                        'code' => 'license_expired',
                        'message' => sprintf( "Your license expired on %s. Please <a href=\"%s\">check your payment method</a>.", $license->expires_at->format('F j, Y'), domain_url( '/edit/payment', 'account' ) )

                    ]
                ], 401 );
            }

            // check if license is at limit
            if( $license->isAtSiteLimit() ) {
                return new JsonResponse([
                    'error' => [
                        'code' => 'license_at_limit',
                        'message' => sprintf( "Your license is at its activation limit of %d sites.", $license->site_limit )
                    ]
                ], 401 );
            }

            // activate license on given site
            $activation = new Activation();
            $activation->url = $siteUrl;
            $activation->domain = $domain;
            $activation->key = Activation::generateKey();
            $activation->license_id = $license->id;

            $this->log->info( "Activated license #{$license->id} on {$domain}" );
        }

        $activation->touch();
        $activation->save();

        return new JsonResponse([
            'data' => [
                'key' => $activation->key,
                'message' => sprintf( "Your license was activated, you have %d site activations left.", $license->getActivationsLeft() )
            ]
        ], 201 );
    }

    /**
     * @param Request $request
     * @param string $activationKey
     *
     * @return JsonResponse
     */
    public function deleteActivation( Request $request, $activationKey = '' ) {

        /** @var License $license */
        $license = $this->auth->license();

        if( $activationKey ) {
            $activation = Activation::where('key', $activationKey)->first();
        } else {
            // for BC for licenses that didn't get an activation key yet
            $siteUrl = trim( $request->input('site_url') );
            $domain = $this->getDomainFromSiteUrl($siteUrl);
            $activation = $license->findDomainActivation($domain);
        }

        if( $activation ) {
            $this->log->info( "Deactivated license #{$license->id} on {$activation->domain}" );
            $activation->delete();
        }

        return new JsonResponse([
            'data' => [
                'message' => 'Your license was successfully deactivated. You can use it on any other domain now.'
            ]
        ], 200 );
    }

    /**
     * @param string $siteUrl
     *
     * @return string
     */
    protected function getDomainFromSiteUrl( $siteUrl ) {

        // set default http:// scheme as it's irrelevant
        $siteUrl = 'http://' . str_replace( array( 'http://', 'https://', '://' ), '', $siteUrl );

        // parse the host
        $domain = parse_url( $siteUrl, PHP_URL_HOST );

        // strip www prefix
        $domain = str_ireplace( 'www.', '', $domain );

        // TODO: Parse top level subdomain only

        return $domain;
    }

}