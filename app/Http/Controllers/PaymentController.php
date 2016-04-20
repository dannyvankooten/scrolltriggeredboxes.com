<?php

namespace App\Http\Controllers;

use App\Payment;
use App\Services\Invoicer\Invoicer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

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

    /**
     * Overview of payments by the logged-in users
     *
     * @return mixed
     */
    public function overview() {
        $user = $this->auth->user();
        $payments = Payment::where('user_id', $user->id)
            ->with('subscription.license')
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('payments.overview', [ 'payments' => $payments ]);
    }

    /**
     * Download PDF invoice
     *
     * @param int $id
     * @param Invoicer $invoicer
     *
     * @return RedirectResponse
     */
    public function invoice( $id, Invoicer $invoicer ) {

        /** @var User $user */
        $user = $this->auth->user();

        /** @var Payment $payment */
        $payment = Payment::findOrFail($id);
        
        // check if payment belongs to user
        if( ! $payment->belongsToUser( $user ) ) {
            abort( 403 );
        }

        // check if payment has invoice
        if( ! $invoicer->hasInvoice( $payment ) ) {
            return view('payments.invoice-not-ready');
        }

        return new RedirectResponse($invoicer->getInvoiceUrl($payment));
    }

}
