<?php namespace App\Handlers\Events;

use App\Events\LicenseCreated;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class PushNewLicenseNotification {

	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $api_url = '';

	/**
	 * @var string
	 */
	protected $api_key = '';

	/**
	 * Create the event handler.
	 *
	 * @param Client $client
	 */
	public function __construct( Client $client)
	{
		$this->client = $client;
		$this->api_url = Config::get('services.pushbullet.api_url');
		$this->api_key = Config::get('services.pushbullet.api_key');
	}

	/**
	 * Handle the event.
	 *
	 * @param  LicenseCreated  $event
	 * @return void
	 */
	public function handle(LicenseCreated $event)
	{
		$license = $event->getLicense();
		$message = sprintf("Order #%s\n Email: %s\nName: %s\nLicense: %s", $license->sendowl_order_id, $license->user->email, $license->user->name, $license->license_key );

		$this->client->post( $this->api_url . '/pushes', [
			'body' => [
				'type' => 'note',
				'title' => sprintf( 'STB Order #%s - %s', $license->sendowl_order_id, $license->plan->name ),
				'body' => $message
			],
			'headers' => [
				'Authorization' => sprintf( 'Bearer %s', $this->api_key )
			]
		]);
	}

}
