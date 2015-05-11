<?php namespace App\Commands;

use App\Commands\Command;

use App\License;
use App\Plan, App\User;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Log;

class PurchasePlan extends Command implements SelfHandling {

	/**
	 * @var Plan
	 */
	protected $plan;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var License
	 */
	protected $license;

	/**
	 * @var int
	 */
	protected $order_id = 0;

	/**
	 * Create a new command instance.
	 *
	 * @param Plan $plan
	 * @param User $user
	 * @param int  $order_id
	 */
	public function __construct( Plan $plan, User $user, $order_id = 0 )
	{
		$this->plan = $plan;
		$this->user = $user;
		$this->order_id = $order_id;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle() {

		if( $this->order_id ) {
			// was a key previously generated for this order?
			$this->license = License::where('sendowl_order_id', $this->order_id )->first();
		}

		if( ! $this->license ) {
			// create new license with this key
			$this->license = new License([
				'license_key' => License::generateKey(),
				'expires_at' => new \DateTime("+1 year"),
				'sendowl_order_id' => $this->order_id
			]);

			// attach license to user
			$this->license->user()->associate( $this->user );
			$this->license->plan()->associate( $this->plan );

			// save the license
			$this->license->save();

			Log::info( sprintf( 'License created for %s (%s)', $this->user->email, $this->license->license_key ) );
		}

		// if this product its site_limit is higher than the one previous set (from another product in the same bundle), use this one. <3
		if( $this->plan->site_limit > $this->license->site_limit ) {
			$this->license->site_limit = $this->plan->site_limit;
			$this->license->save();
		}
	}

	/**
	 * @return License
	 */
	public function getLicense() {
		return $this->license;
	}

}
