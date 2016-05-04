<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use App\License;
use Illuminate\Http\Request;

class LicenseGuard implements Guard {

    /**
     * The currently authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    /**
     * @var License
     */
    protected $license;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $attempted = false;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * LicenseGuard constructor.
     *
     * @param Request $request
     */
    public function __construct( Request $request ) {
        $this->request = $request;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    /**
     * Set the current user.
     *
     * @param  Authenticatable  $user
     * @return void
     */
    public function setUser( Authenticatable $user)
    {
        $this->user = $user;
    }


    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return $this->attempt($credentials['key']);
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function attempt( $key )
    {
        $this->attempted = true;

        if( empty( $key ) ) {
            $this->errorMessage = 'Please provide a valid license key.';
            return false;
        }

        /** @var License $license */
        $license = License::where('license_key', $key)->with('user')->first();

        if( ! $license ) {
            $this->errorMessage = sprintf( "Your license seems to be invalid. Please check <a href=\"%s\">your account</a> for the correct license key.", domain_url( '/', 'account' ) );
            return false;
        }

        if( $license->isExpired() ) {
            $this->errorMessage = sprintf( "Your license expired on %s.", $license->expires_at->format('F j, Y') );
            return false;
        }

        $this->login($license);

        return true;
    }

    /**
     * @param License $license
     */
    public function login( License $license ) {
        $this->license = $license;
        $this->user = $license->user;
    }

    /**
     * @return License
     */
    public function license() {
        $this->attemptFromRequest();
        return $this->license;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        $this->attemptFromRequest();
        return $this->user;
    }

    /**
     * @return string
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    protected function getTokenForRequest()
    {
        $token = $this->request->input('license_key');

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        if (empty($token)) {
            $token = $this->request->getPassword();
        }

        return urldecode( $token );
    }

    /**
     * Attempt to log in given current request
     */
    public function attemptFromRequest() {
        if( $this->attempted ) {
            return;
        }

        $this->attempted = true;

        $key = $this->getTokenForRequest();
        $this->attempt( $key );
    }
}