<?php namespace App\Http\Controllers\Admin;

use App\Activation;
use App\Http\Controllers\Controller;
use App\License;
use App\Payment;
use App\Subscription;
use App\Totals;
use App\User;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DefaultController extends Controller {

    // show users overview
    public function overview( Request $request ) {
        $recentLicenses = License::query()->with('user')->take(5)->orderBy('created_at', 'desc')->get();
        $recentActivations = Activation::query()->with(['license', 'license.activations'])->take(5)->orderBy('created_at', 'desc')->get();
        $recentPayments = Payment::query()->with(['user'])->take(5)->orderBy('created_at', 'desc')->get();
        $expiringLicenses = License::query()->with('user')->where('expires_at', '>=', Carbon::now())->take(5)->orderBy('expires_at', 'asc')->get();
        $totals = Totals::query( $request->query->getInt( 'days', 30 ) );
        
        return view( 'admin.overview', [
            'expiringLicenses' => $expiringLicenses,
            'recentLicenses' => $recentLicenses,
            'recentActivations' => $recentActivations,
            'recentPayments'    => $recentPayments,
            'totals' => $totals,
            'timeframeOptions' => array( 30, 60, 90, 180, 360 ),
        ]);
    }





}