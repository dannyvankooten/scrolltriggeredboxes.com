<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Services\Payments\Charger;
use App\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\Request;

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

    // create new payment
    public function store( Request $request, Redirector $redirector, Charger $charger ) {
        $data = $request->request->get('payment');
        $subscription = Subscription::findOrFail( $data['subscription_id'] );
        $payment = $charger->subscription( $subscription );
        return $redirector->back()->with('message', 'Payment created.');
    }

}