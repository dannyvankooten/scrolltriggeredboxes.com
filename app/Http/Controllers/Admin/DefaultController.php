<?php namespace App\Http\Controllers\Admin;

use App\Activation;
use App\Http\Controllers\Controller;
use App\License;
use App\Payment;
use App\Subscription;
use App\Totals;
use App\User;

use Illuminate\Http\Request;

class DefaultController extends Controller {

    // show users overview
    public function overview( Request $request ) {
        $recentUsers = User::query()->take(5)->orderBy('created_at', 'desc')->get();
        $recentActivations = Activation::query()->with(['license', 'license.activations'])->take(5)->orderBy('created_at', 'desc')->get();
        $recentPayments = Payment::query()->with(['user'])->take(5)->orderBy('created_at', 'desc')->get();
        $totals = Totals::query( $request->query->getInt( 'days', 30 ) );
        
        return view( 'admin.overview', [
            'recentUsers' => $recentUsers,
            'recentActivations' => $recentActivations,
            'recentPayments'    => $recentPayments,
            'totals' => $totals,
            'timeframeOptions' => array( 30, 60, 90, 180, 360 ),
        ]);
    }





}