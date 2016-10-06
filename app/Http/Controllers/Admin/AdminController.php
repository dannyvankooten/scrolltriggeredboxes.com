<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log;
use App\User;

class AdminController extends Controller {

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var User;
     */
    protected $admin;

    /**
     * AdminController constructor.
     *
     * @param Guard $guard
     * @param Log $log
     */
    public function __construct( Guard $guard, Log $log ) {
        $this->admin = $guard->user();
        $this->log = $log;
    }


}