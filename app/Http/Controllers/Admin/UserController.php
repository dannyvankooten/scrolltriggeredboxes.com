<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller {

    // show users overview
    public function overview( Request $request ) {

        $query = User::query();

        $filters = $request->query->get('filter', []);

        // apply filters
        foreach( $filters as $filter => $value ) {
            if( ! empty( $value ) ) {

                $value = str_replace( '*', '%', $value );
                $query->where( $filter, 'LIKE', $value );
            }
        }

        $users = $query->get();

        return view( 'admin.users.overview', [ 'users' => $users ] );
    }

    // show user details
    public function detail($id) {
        $user = User::findOrFail($id);
        return view( 'admin.users.detail', [ 'user' => $user ] );
    }

}