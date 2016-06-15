<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Activation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class ActivationController extends Controller {

    /**
     * @param int $id
     * @param Redirector $redirector
     *
     * @return RedirectResponse
     */
    public function destroy( $id, Redirector $redirector  ) {
        $activation = Activation::findOrFail( $id );
        $activation->delete();
        return $redirector->back()->with('message', 'Site activation deleted.');
    }

}