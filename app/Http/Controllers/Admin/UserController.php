<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller {

    /**
     * @return mixed
     */
    public function overview() {
        $users = User::get();
        return view( 'admin.users.overview', [ 'users' => $users ] );
    }

    /**
     * @return \Illuminate\View\View
     */
    public function detail($id) {
        $user = User::findOrFail($id);
        return view( 'admin.users.detail', [ 'user' => $user ] );
    }

}