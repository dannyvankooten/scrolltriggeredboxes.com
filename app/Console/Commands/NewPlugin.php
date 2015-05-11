<?php namespace App\Console\Commands;

use App\Plugin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class NewPlugin extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'plugins:new';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

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
	public function fire()
	{
		$name = $this->argument('name');
		$slug = str_slug( $name );

		Plugin::create([
			'name' => $name,
			'url' => $slug,
			'slug' => 'stb-' . $slug,
			'version' => '1.0',
			'author' => 'Danny van Kooten',
			'image_path' => '/img/plugins/' . $slug . '.jpg',
			'type' => 'premium'
		]);

		$this->info('Plugin ' . $slug . ' created!');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'Name of the plugin.']
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
		];
	}

}
