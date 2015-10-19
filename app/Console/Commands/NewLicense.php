<?php namespace App\Console\Commands;

use App\Jobs\CreateUser;
use App\Jobs\PurchasePlan;
use App\Plan;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class NewLicense extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'licenses:new';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new license.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$user = User::where('email', $this->option('email'))->first();
		if( ! $user ) {
			$command = new CreateUser( $this->option('email'), $this->option('name') );
			Bus::dispatch( $command );
			$user = $command->getUser();
		}

		// get local information about SendOwl product
		$plan = Plan::find($this->option('plan_id'))->firstOrFail();

		$command = new PurchasePlan( $plan, $user, $this->option('order_id') );
		Bus::dispatch( $command );

		$this->info($command->getLicense()->license_key);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['email', null, InputOption::VALUE_REQUIRED, 'The email address this license.', null],
			['name', null, InputOption::VALUE_OPTIONAL, 'The name this license.', ''],
			['plan_id', null, InputOption::VALUE_REQUIRED, 'The plan ID this license.', null],
			['order_id', null, InputOption::VALUE_OPTIONAL, 'The order ID for this license.', 0],
		];
	}

}
