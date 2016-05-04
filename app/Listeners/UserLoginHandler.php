<?php

namespace App\Listeners;

use DateTime;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class UserLoginHandler
{
    /**
     * UserLoginHandler constructor.
     * 
     * @param Request $request
     */
    public function __construct( Request $request ) {
        $this->request = $request;
    }

    public function handle( Login $login )
    {
        // store user ip
        $login->user->ip_address = $this->request->getClientIp();
        $login->user->last_login_at = new DateTime('now');
        $login->user->save();
    }

}