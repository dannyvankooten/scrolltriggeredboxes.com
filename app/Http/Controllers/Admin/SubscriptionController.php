<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Subscription;
use App\Services\Charger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class SubscriptionController extends Controller {

    /**
     * @param int $id
     * @param Request $request
     * @param Redirector $redirector
     *
     * @return RedirectResponse
     */
    public function update( $id, Request $request, Redirector $redirector ) {

        /** @var Subscription $subscription */
        $subscription = Subscription::findOrFail($id);
        $subscription->fill( $request->input('subscription') );

        // update next charge date
        $subscription->next_charge_at = $subscription->license->expires_at->modify('-1 week');
        $subscription->save();

        // if a payment is due, try to charge right away
        if( $subscription->isPaymentDue() ) {
            $charger = new Charger();
            $charger->subscription( $subscription );
        }

        return $redirector->back()->with('message', 'Changes saved!');
    }

}