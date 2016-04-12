<?php

namespace App\Services;

use App\Plugin;
use GuzzleHttp;

class PluginDownloader {

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var
     */
    protected $downloads_dir;

    /**
     * PluginDownloader constructor.
     *
     * @param Plugin $plugin
     * @param string $downloads_dir
     */
    public function __construct( Plugin $plugin, $downloads_dir = '' ) {
        $this->plugin = $plugin;
        $this->downloads_dir = $downloads_dir;

        if( empty( $downloads_dir ) ) {
            $this->downloads_dir = storage_path( 'downloads' );
        }
    }

    /**
     * @param string $version
     *
     * @return string
     */
    public function download( $version = '' ) {

        // generate filename
        $filename = sprintf( '%s/%s%s.zip', $this->downloads_dir, $this->plugin->slug, ! empty( $version ) ? '-' . $version : '' );

        $exists = file_exists( $filename );
        $last_modified = $exists ? filemtime( $filename ) : 0;
        $some_time_ago = time() - 1500;

        // if file does not exist or is older than 15 minutes, re-download & process
        if( ! $exists || $last_modified < $some_time_ago ) {

            if( ! is_dir( $this->downloads_dir ) ) {
                mkdir( $this->downloads_dir );
            }

            // download file
            $client = new GuzzleHttp\Client();

            try {
                $res = $client->request( 'GET', $this->plugin->getDownloadUrl( $version ) );
            } catch( GuzzleHttp\Exception\ClientException $e ) {
                abort( $e->getCode() );
                exit;
            }

            file_put_contents( $filename, $res->getBody() );

            // open zip & rename index directory because WordPress expects plugin slug as directory name.
            $zip = new \ZipArchive;
            $zip->open( $filename );

            $current_base_directory = $zip->getNameIndex(0);
            $new_base_directory = $this->plugin->slug . '/';

            for( $i = 0; $i < $zip->numFiles; $i++ ){
                $name = $zip->getNameIndex( $i );
                $newName = str_replace( $current_base_directory, $new_base_directory, $name );
                $zip->renameIndex( $i, $newName );
            }

            $zip->close();
        }

        return $filename;
    }
}