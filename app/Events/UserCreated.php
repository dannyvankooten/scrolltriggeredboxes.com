<?php namespace App\Events;

use App\Events\Event;

use App\User;
use Illuminate\Queue\SerializesModels;

class UserCreated extends Event {

	use SerializesModels;

	public $user;

	public $password;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct( User $user, $password)
	{
		$this->user = $user;
		$this->password = $password;
	}

}
