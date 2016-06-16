<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Subscription;
use App\Services\Payments\Charger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Routing\Redirector;

class SubscriptionController extends Controller {

    // form for editing a subscription
    public function edit($id) {
        /** @var Subscription $subscription */
        $subscription = Subscription::findOrFail($id);
        $license = $subscription->license;
        return view( 'admin.subscriptions.edit', [ 'subscription' => $subscription, 'license' => $license ]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param Redirector $redirector
     * @param Charger $charger
     *
     * @return RedirectResponse
     */
    public function update( $id, Request $request, Redirector $redirector, Charger $charger ) {

        /** @var Subscription $subscription */
        $subscription = Subscription::findOrFail($id);

        $data = $request->request->get('subscription');

        if( isset( $data['active'] ) ) {
            $subscription->active = (int) $data['active'];
        }

        if( ! empty( $data['interval'] ) ) {
            $subscription->interval = $data['interval'];
        }

        if( ! empty( $data['amount'] )  ) {
            $subscription->amount = floatval( $data['amount'] );
        }

        // update next charge date
        $subscription->next_charge_at = $subscription->license->expires_at->modify('-1 week');
        $subscription->save();
        
        return $redirector->to('/licenses/'. $subscription->license->id )->with('message', 'Changes saved!');
    }

}