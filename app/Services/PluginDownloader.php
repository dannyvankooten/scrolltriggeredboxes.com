<?php

namespace App\Services;

use App\Plugin;
use GuzzleHttp;
use Illuminate\Support\Facades\Cache;

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
     * @var GuzzleHttp\Client
     */
    protected $client;

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

        $this->client = new GuzzleHttp\Client([
            'query' => [ 'access_token' => config('services.github.access_token') ]
        ]);
    }

    /**
     * @param string $resource
     * @return mixed
     */
    protected function getCachedResource( $resource ) {
        $prefix = sprintf( 'github/%s/%s:', $this->plugin->getGitHubRepositoryOwner(), $this->plugin->getGitHubRepositoryName() );
        $content = Cache::get( $prefix . $resource );
        return $content;
    }

    /**
     * @param string $resource
     * @param string $content
     */
    protected function cacheResource( $resource, $content ) {
        $prefix = sprintf( 'github/%s/%s:', $this->plugin->getGitHubRepositoryOwner(), $this->plugin->getGitHubRepositoryName() );
        Cache::put( $prefix . $resource, $content, 60 );
    }
    /**
     * @param string $path
     * @return string
     */
    protected function getResourceUrl( $path ) {
        return sprintf( 'https://api.github.com/repos/%s/%s/%s', $this->plugin->getGitHubRepositoryOwner(), $this->plugin->getGitHubRepositoryName(), ltrim( $path, '/' ) );
    }

    /**
     * @return object
     */
    public function getInfo() {
        $resource = 'contents/info.json';

        // check cache
        $content = $this->getCachedResource( $resource );
        if( ! $content ) {
            // fetch from remote
            try {
                $url = $this->getResourceUrl($resource);
                $response = $this->client->request('GET', $url);
                $data = json_decode($response->getBody());

                $client = new GuzzleHttp\Client();
                $response = $client->request('GET', $data->download_url);
                $content = (string)$response->getBody();

                // store in cache
                $this->cacheResource($resource, $content);
            } catch( GuzzleHttp\Exception\RequestException $e ) {
                return new \stdClass;
            }
        }

        $data = json_decode( $content );
        return $data;
    }

    /**
     * @return string
     */
    public function getChangelog() {
        $resource = 'contents/CHANGELOG.md';
        $content = $this->getCachedResource( $resource );

        if( ! $content ) {
            try {
                $url = $this->getResourceUrl($resource);
                $headers = ['Accept' => 'application/vnd.github.v3.html'];
                $response = $this->client->request('GET', $url, ['headers' => $headers]);
                $content = (string)$response->getBody();

                // store in cache
                $this->cacheResource($resource, $content);
            } catch( GuzzleHttp\Exception\RequestException $e ) {
                return '';
            }
        }

        return $content;
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
            $url = $this->getResourceUrl( sprintf( 'zipball/%s', $version ) );

            try {
                $res = $this->client->request( 'GET', $url, [ 'sink' => $filename ] );
            } catch( GuzzleHttp\Exception\ClientException $e ) {
                abort( $e->getCode() );
                exit;
            }

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