<?php namespace App\Jobs;

use App\Events\UserCreated;
use App\User;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command implements SelfHandling {

	/**
	 * @var string
	 */
	protected $email = '';

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var
	 */
	protected $user;

	/**
	 * Create a new command instance.
	 *
	 * @param        $email
	 * @param string $name
	 */
	public function __construct( $email, $name = '' )
	{
		$this->email = $email;
		$this->name = $name;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$raw_password = str_random( 16 );
		$this->user = new User();
		$this->user->email = $this->email;
		$this->user->name = $this->name;
		$this->user->password = Hash::make( $raw_password );
		$this->user->save();
		Log::info('User created: ' . $this->user->email );
		event(new UserCreated($this->user, $raw_password));
	}

	/**
	 * @return User;
	 */
	public function getUser() {
		return $this->user;
	}

}
