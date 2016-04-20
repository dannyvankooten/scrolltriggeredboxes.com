<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Services\Charger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class PaymentController extends Controller {

    /**
     * @param int $id
     * @param Redirector $redirector
     *
     * @return RedirectResponse
     */
    public function delete( $id, Redirector $redirector  ) {

        $payment = Payment::findOrFail( $id );
        $charger = new Charger();
        $charger->refund( $payment );

        return $redirector->back();
    }

}