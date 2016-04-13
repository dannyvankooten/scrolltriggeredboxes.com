<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Subscription;
use App\Services\Charger;
use Illuminate\Http\Request;

class SubscriptionController extends Controller {

    /**
     * @param int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update( $id, Request $request ) {

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

        return redirect()->back()->with('message', 'Changes saved!');
    }

}