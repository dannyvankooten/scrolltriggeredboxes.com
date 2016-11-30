<?php

use Illuminate\Database\Seeder;
use App\Plugin;

class PluginsTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        $data = [
            [
                'name' => 'Exit Intent',
                'sid' => 'exit-intent',
                'slug' => 'boxzilla-exit-intent',
                'description' => "Exit Intent add-on for Boxzilla",
                'short_description' => "Exit Intent add-on",
                "github_repo" => "ibericode/boxzilla-exit-intent",
            ],
            [
                'name' => 'Theme Pack',
                'sid' => 'theme-pack',
                'slug' => 'boxzilla-theme-pack',
                'description' => "Theme Pack add-on for Boxzilla",
                'short_description' => "Theme Pack add-on",
                "github_repo" => "ibericode/boxzilla-theme-pack",
            ],

            [
                'name' => 'Google Analytics',
                'sid' => 'google-analytics',
                'slug' => 'boxzilla-google-analytics',
                'description' => "Google Analytics add-on for Boxzilla",
                'short_description' => "Google Analytics add-on",
                "github_repo" => "ibericode/boxzilla-google-analytics",
            ]
        ];

        foreach( $data as $pluginData ) {
            $plugin = new Plugin();

            foreach( $pluginData as $prop => $value ) {
                $plugin->$prop = $value;
            }

            $plugin->save();
        }
	}

}
