<?php

namespace App\Console\Commands;

use App\License;
use Illuminate\Console\Command;

class DisableLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:disable {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disables a license.';



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
	    $license_id = (int) $this->argument('id');

        // retrieve license
	    $license = License::find( $license_id );
	    if( ! $license instanceof License ) {
		    $this->error( sprintf( 'No license found with ID %d', $license_id ) );
	        return;
	    }

	    $license->delete();

	    $this->info( sprintf( "License %d has been deleted.", $license_id ) );
    }

}
