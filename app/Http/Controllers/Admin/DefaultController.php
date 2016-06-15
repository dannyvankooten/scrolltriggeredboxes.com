<?php namespace App\Http\Controllers\Admin;

use App\Activation;
use App\Http\Controllers\Controller;
use App\License;
use App\Payment;
use App\Subscription;
use App\User;

use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    // show users overview
    public function overview() {
        $userCount = User::query()->count();
        $licenseCount = License::query()->count();
        $activationCount = Activation::query()->count();

        $recentUsers = User::query()->take(5)->orderBy('created_at', 'desc')->get();
        $recentActivations = Activation::query()->with(['license', 'license.activations'])->take(5)->orderBy('created_at', 'desc')->get();
        $recentPayments = Payment::query()->with(['user'])->take(5)->orderBy('created_at', 'desc')->get();
        $upcomingPayments = Subscription::query()->with('user')->where('active', 1)->where('next_charge_at', '>=', new \DateTime('now'))->orderBy('next_charge_at', 'asc' )->take(5)->get();

        return view( 'admin.overview', [
            'userCount' => $userCount,
            'licenseCount' => $licenseCount,
            'activationCount' => $activationCount,
            'recentUsers' => $recentUsers,
            'recentActivations' => $recentActivations,
            'recentPayments'    => $recentPayments,
            'upcomingPayments' => $upcomingPayments,
        ]);
    }



}