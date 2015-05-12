<?php namespace App\Events;

use App\Events\Event,
	App\License;

use Illuminate\Queue\SerializesModels;

class LicenseCreated extends Event {

	use SerializesModels;

	/**
	 * @var License
	 */
	protected $license;

	/**
	 * Create a new event instance.
	 *
	 * @param License $license
	 */
	public function __construct( License $license )
	{
		$this->license = $license;
	}

	/**
	 * @return License
	 */
	public function getLicense()
	{
		return $this->license;
	}

}
