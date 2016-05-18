<?php namespace App;

use GrahamCampbell\GitHub\Facades\GitHub;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use DateTime;

/**
 * Class Plugin
 *
 * @property Activation[] $sites
 * @property int $id
 * @property string $sid
 * @property string $slug
 * @property string $short_description
 * @property string $github_repo
 * @property string $description
 * @property string $type
 * @property string $name
 * @property DateTime $created_at
 * @property DateTime $updated_at
 *
 *
 * @package App
 */
class Plugin extends Model {

	protected $table = 'plugins';
	public $timestamps = true;
	protected $fillable = [];
	
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

		if( empty( $this->github_repo ) ) {
			return array();
		}

		$cacheKey = "plugins.{$this->sid}.update-info";
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
		$url = sprintf( 'https://api.github.com/repos/%s/%s/zipball/%s?access_token=%s', $this->getGitHubRepositoryOwner(), $this->getGitHubRepositoryName(), $version, config('github.connections.main.token') );
		return $url;
	}

	/**
	 * @return string
	 */
	public function getChangelog() {
		if( empty( $this->github_repo ) ) {
			return '';
		}

		$cacheKey = "plugins.{$this->sid}.changelog";
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
	public function getVersion()
	{
		$info = $this->getUpdateInfo();
		return isset($info['version']) ? $info['version'] : '';
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	public function toJson( $options = array() ) {
		static $defaults = [ 'format' => 'default' ];
		$options = array_replace( $defaults, $options );
		$method = 'to' . ucfirst( $options['format'] ) . 'Array';
		$data = $this->$method();
		return json_encode( $data );
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return [
			'name' => $this->name,
			'short_description' => $this->short_description,
			'page_url' => $this->getPageUrl(),
			'image_url' => $this->getImageUrl(),
			'type' => $this->type,
		];
	}


	/**
	 * @return array
	 */
	public function toWpArray() {

		$data = [
			'id' => $this->sid, // this is to fix a bug in the plugin where it checks for "id" then compares it with local sid
			'sid' => $this->sid,
			'url' => $this->getPageUrl(),
			'homepage' => domain_url( '/' ),
			'package' => url( '/v1/plugins/' . $this->id .'/download' ),
			'download_url' => url( '/v1/plugins/' . $this->id .'/download' ),
			'name'      => $this->name,
			'sections'  => [
				'changelog'     => $this->getChangelog(),
				'description'   => $this->description
			],
			'last_updated' => $this->updated_at->format( 'F, Y' ),
			'banners'   => [
				'high'      => $this->getImageUrl()
			]
		];

		$updateInfo = $this->getUpdateInfo();
		$data = array_merge( $data, $updateInfo );

		return $data;
	}

	/**
	 * @return string
	 */
	public function getPageUrl() {
		return domain_url( sprintf( '/add-ons/%s' , $this->sid ) );
	}

	/**
	 * @return string
	 */
	public function getImageUrl() {
		return domain_url( sprintf( '/assets/img/plugins/%s.png', $this->sid ) );
	}
}
