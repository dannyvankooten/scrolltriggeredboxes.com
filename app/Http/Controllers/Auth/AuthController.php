<?php namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller {

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var Guard
	 */
	protected $auth;

	public function __construct( Guard $auth, User $user ) {
		$this->user = $user;
		$this->auth = $auth;

		//$this->middleware('guest', ['except' => ['logout']]);
	}

	/**
	 * Handle an authentication attempt.
	 *
	 * @return Response
	 */
	public function postLogin( Request $request )
	{
		if ($this->auth->attempt($request->only('email', 'password'), $request->input('remember_me'))) {
			return redirect('/');
		} else {
			return redirect()->back()->withErrors([
				'email' => 'The credentials you entered did not match our records.'
			]);
		}
	}

	/**
	 * @return \Illuminate\View\View
	 */
	public function getLogin( ) {
		return view( 'auth.login' );
	}

	/**
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function getLogout() {
		$this->auth->logout();
		return redirect('/');
	}

	/**
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getLoginFromPurchase( Request $request ) {
		$sendowl_config = config('services.sendowl');

		$message = sprintf( 'buyer_email=%s&buyer_name=%s&order_id=%s&secret=%s',
			$request->input('buyer_email'),
			$request->input('buyer_name'),
			$request->input('order_id'),
			$sendowl_config['api_secret'] );

		$key = $sendowl_config['api_key'] .'&'. $sendowl_config['api_secret'];
		$expected_signature = base64_encode( hash_hmac('sha1', $message, $key, true ) );
		if( $expected_signature != $request->input('signature') ) {
			abort(403);
		}

		// success! now, get this user.
		$user = User::where('email', $request->input('buyer_email'))->firstOrFail();

		// success! log user in
		$this->auth->loginUsingId($user->id);

		// set flash message
		Session::flash('message', sprintf( 'Thank you for your purchase! You can now download any of the premium plugin from this page. An email with login credentials for this site has been sent to <strong>%s</strong>.', $user->email ) );

		return response()->redirectTo('/');
	}

}