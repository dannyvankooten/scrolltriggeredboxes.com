<?php

namespace App\Http\Controllers;

use App\Payment;
use App\Services\Invoicer\Invoicer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class PaymentController extends Controller {

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * AccountController constructor.
     *
     * @param Guard $auth
     */
    public function __construct( Guard $auth ) {
        $this->auth = $auth;
        $this->middleware('auth.user');
    }

    public function overview() {
       // TODO list all payments + invoices for this users
    }

}
