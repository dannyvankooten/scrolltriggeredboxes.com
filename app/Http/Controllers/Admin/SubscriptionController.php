<?php


namespace App\Http\Controllers\Admin;

use App\Subscription;
use App\Services\Payments\Charger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Routing\Redirector;

class SubscriptionController extends AdminController {

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

            if( $subscription->active ) {
                $this->log->info( sprintf( 'Re-activated subscription %d for user %s.', $subscription->id, $subscription->user->email ) );
            } else {
                // if we just deactivated subscription, check if we need to refund last payment.
                if( $request->request->get('process_refund', 0) ) {
                    $lastPayment = $subscription->payments[0];
                    if( $lastPayment->isEligibleForRefund() ) {
                        $charger->refund( $lastPayment );
                    }
                }

                $this->log->info( sprintf( 'Deactivated subscription %d for user %s.', $subscription->id, $subscription->user->email ) );
            }
        }

        if( ! empty( $data['interval'] ) ) {
            $subscription->interval = $data['interval'];
        }

        if( ! empty( $data['amount'] )  ) {
            $subscription->amount = floatval( $data['amount'] );
        }

        // update next charge date
        $subscription->next_charge_at = $subscription->license->expires_at->modify('-5 days');
        $subscription->save();
        
        return $redirector->to('/licenses/'. $subscription->license->id )->with('message', 'Changes saved!');
    }

}