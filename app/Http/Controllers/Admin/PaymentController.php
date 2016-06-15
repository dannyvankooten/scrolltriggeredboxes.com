<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Services\Payments\Charger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class PaymentController extends Controller {

    /**
     * @param int $id
     * @param Redirector $redirector
     * @param Charger $charger
     * 
     * @return RedirectResponse
     */
    public function destroy( $id, Redirector $redirector, Charger $charger  ) {

        $payment = Payment::findOrFail( $id );
        $charger->refund( $payment );

        return $redirector->back();
    }

}