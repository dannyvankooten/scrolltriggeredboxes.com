<?php namespace App;

use GrahamCampbell\GitHub\Facades\GitHub;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Plugin extends Model {

	protected $table = 'plugins';
	public $timestamps = true;
	protected $fillable = [];

	// hidden from json export
	protected $hidden = array( 'id', 'created_at', 'updated_at', 'changelog', 'description', 'url', 'slug', 'upgrade_notice', 'tested', 'image_path', 'status' );
	protected $appends = [ 'page_url', 'image_url' ];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function licenses() {
		return $this->belongsToMany('App\License', 'plugin_licenses', 'license_id', 'plugin_id' );
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function sites() {
		return $this->hasMany('App\Activation', 'plugin_id', 'id');
	}

	/**
	 * @return string
	 */
	public function getGitHubRepositoryOwner() {
		return substr( $this->github_repo, 0, strpos( $this->github_repo, '/' ) );
	}

	/**
	 * @return string
	 */
	public function getGitHubRepositoryName() {
		return substr( $this->github_repo, strpos( $this->github_repo, '/' ) + 1 );
	}


	/**
	 * @return array
	 */
	public function getUpdateInfo() {

		$cacheKey = "plugins.{$this->url}.update-info";
		$fileContent = Cache::get( $cacheKey );

		if( ! $fileContent ) {
			$fileContent = GitHub::connection()->repo()->contents()->download( $this->getGitHubRepositoryOwner(), $this->getGitHubRepositoryName(), 'info.json');

			// Remove GitHub-added formatting to make this machine readable again
			$fileContent = str_ireplace( PHP_EOL, '', $fileContent );
			$fileContent = str_ireplace( ',}', '}', $fileContent );

			Cache::put( $cacheKey, $fileContent, 60 );
		}

		$data = json_decode( $fileContent, true );

		return $data;
	}

	/**
	 * Get the raw GitHub API download URL. Please note that this URL contains the access token so it shouldn't be used publicly.
	 *
	 * @param string $version
	 *
	 * @return string
	 */
	public function getDownloadUrl( $version = '' ) {
		$url = sprintf( 'https://api.github.com/repos/%s/%s/zipball/%s?access_token=%s', $this->getGitHubRepositoryOwner(), $this->getGitHubRepositoryName(), $version, env( 'GITHUB_TOKEN', '' ) );
		return $url;
	}

	/**
	 * @return string
	 */
	public function getChangelog() {
		$cacheKey = "plugins.{$this->url}.changelog";
		$html = Cache::get( $cacheKey );

		if( ! $html ) {
			$fileContent = GitHub::connection()->repo()->contents()->download( $this->getGitHubRepositoryOwner(), $this->getGitHubRepositoryName(), 'CHANGELOG.md');
			$html = Markdown::convertToHtml( $fileContent );

			Cache::put( $cacheKey, $html, 60 );
		}

		return $html;
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		$info = $this->getUpdateInfo();
		return isset( $info['version'] ) ? $info['version'] : '';
	}

	/**
	 * @return array
	 */
	public function toWpArray() {

		$data = [
			'id' => $this->id,
			'url' => url( '/plugins/' . $this->url ),
			'homepage' => url( '/plugins/' . $this->url ),
			'package' => url( '/api/v1/plugins/' . $this->id .'/download' ),
			'download_url' => url( '/api/v1/plugins/' . $this->id .'/download' ),
			'name'      => $this->name,
			'sections'  => [
				'changelog'     => $this->getChangelog(),
				'description'   => $this->description
			],
			'last_updated' => $this->updated_at->format( 'F, Y' ),
			'banners'   => [
				'high'      => asset( $this->image_path )
			]
		];

		$updateInfo = $this->getUpdateInfo();
		$data = array_merge( $data, $updateInfo );

		return $data;
	}

	public function getImageUrlAttribute()
	{
		return asset( $this->image_path );
	}

	public function getPageUrlAttribute()
	{
		return url( sprintf( '/plugins/%s', $this->url ) );
	}
}
