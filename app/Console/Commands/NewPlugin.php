<?php namespace App\Console\Commands;

use App\Plugin;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
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
	public function __construct(  )
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

		$plugin = Plugin::where('url', $slug )->first();
		if( ! $plugin ) {

			// create plugin in db
			$plugin = Plugin::create([
				'name' => $name,
				'url' => $slug,
				'slug' => 'stb-' . $slug,
				'version' => '1.0',
				'author' => 'Danny van Kooten',
				'image_path' => '/img/plugins/' . $slug . '.jpg',
				'type' => 'premium'
			]);
		}

		// create directory for plugin
		// check if plugin file exists
		$dir = sprintf( 'app/plugins/%s', $plugin->slug, $plugin->slug );
		$storage = Storage::disk('local');
		$exists = $storage->exists( $dir );
		if( ! $exists ) {
			$storage->makeDirectory($dir);
		}

		$this->info( sprintf( 'Plugin %s (#%d) created!', $name, $plugin->id ) );
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