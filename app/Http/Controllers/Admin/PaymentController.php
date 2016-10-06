<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Services\Invoicer\Invoicer;
use App\Services\Payments\Charger;
use App\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends AdminController {

    // delete a payment (refund)
    public function destroy( $id, Redirector $redirector, Charger $charger  ) {
        /** @var Payment $payment */
        $payment = Payment::with(['user'])->findOrFail( $id );
        $refund = $charger->refund( $payment );

        $this->log->info( sprintf( '%s refunded %s to user %s.', $this->admin->getFirstName(), $payment->getFormattedTotal(), $payment->user->email ) );

        return $redirector->back();
    }

    // create new payment
    public function store( Request $request, Redirector $redirector, Charger $charger ) {
        $data = $request->request->get('payment');
        $subscription = Subscription::findOrFail( $data['subscription_id'] );

        if( ! $charger->chargeable( $subscription ) ) {
            return $redirector->back()->with('error', 'User has no valid payment method.');
        }

        $payment = $charger->subscription( $subscription );

        $this->log->info( sprintf( '%s charged %s to user %s.', $this->admin->getFirstName(), $payment->getFormattedTotal(), $payment->user->email ) );

        return $redirector->back()->with('message', 'Payment created.');
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
        /** @var Payment $payment */
        $payment = Payment::findOrFail($id);
        return new RedirectResponse($invoicer->getInvoiceUrl($payment));
    }
}