<?php namespace App\Handlers\Events;

use App\Events\UserCreated;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\User;
use Illuminate\Support\Facades\Mail;

class EmailUserCredentials {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Handle the event.
	 *
	 * @param  UserCreated  $event
	 * @return void
	 */
	public function handle(UserCreated $event)
	{
		Mail::send('emails.welcome', [ 'user' => $event->user, 'password' => $event->password ], function($message) use($event)
		{
			$message->to( $event->user->email, $event->user->name )->subject('Welcome - your Scroll Triggered Boxes license!');
		});
	}

}
