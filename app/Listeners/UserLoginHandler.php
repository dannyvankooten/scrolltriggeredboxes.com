<?php

namespace App\Listeners;

use DateTime;
use App\User;

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
        /** @var User $user */
        $user = $login->user;
        $user->ip_address = $this->request->getClientIp();
        $user->last_login_at = new DateTime('now');
        $user->save();
    }

}