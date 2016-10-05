<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Logging\Log;

class AdminController extends Controller {

    /**
     * @var Log
     */
    protected $log;

    /**
     * PaymentController constructor.
     * @param Log $log
     */
    public function __construct( Log $log ) {
        $this->log = $log;
    }


}