<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Services\Invoicer\Invoicer;
use App\Services\Payments\StripeAgent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends AdminController {

    // delete a payment (refund)
    public function destroy( $id, Redirector $redirector, StripeAgent $agent  ) {
        /** @var Payment $payment */
        $payment = Payment::with(['user'])->findOrFail( $id );
        $agent->refundPayment( $payment );
        $this->log->info( sprintf( '%s refunded %s to user %s.', $this->admin->getFirstName(), $payment->getFormattedTotal(), $payment->user->email ) );
        return $redirector->back();
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