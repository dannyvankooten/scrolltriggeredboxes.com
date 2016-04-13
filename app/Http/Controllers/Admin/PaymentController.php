<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Services\Charger;

class PaymentController extends Controller {

    /**
     * @param $id
     * @return
     */
    public function delete( $id ) {
        $payment = Payment::findOrFail( $id );
        $charger = new Charger();
        $charger->refund( $payment );
        return redirect()->back();
    }

}