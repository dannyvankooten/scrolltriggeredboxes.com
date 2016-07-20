<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Routing\Redirector;

use Illuminate\Http\Request;

class UserController extends Controller {

    // show users overview
    public function overview( Request $request ) {

        $query = User::query();
        $query->orderBy('created_at', 'desc');

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

    // show create user form
    public function create() {
        $user = new User();
        return view( 'admin.users.create', [ 'user' => $user ]);
    }

    // create user
    public function store( Request $request, Redirector $redirector ) {
        // validate values
        $this->validate( $request, [
            'user.name' 		=> 'required',
            'user.email' 		=> 'required|email|unique:users,email',
            'user.country' 		=> 'required',
            'password' 			=> 'required|confirmed|min:6',
        ], array(
            'email' => 'Please enter a valid email address.',
            'unique' => 'That email address is already in use.'
        ));

        $user = new User();
        $user->email = $request->input('user.email');
        $user->name = $request->input('user.name');
        $user->setPassword( $request->input('password') );
        $user->country = $request->input('user.country');
        $user->save();

        return $redirector->to('/users/'. $user->id)->with('message', 'User created.');
    }

}