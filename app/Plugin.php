<?php namespace App;

use App\Services\PluginDownloader;
use Illuminate\Database\Eloquent\Model;
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
	 * @return object
	 */
	public function getUpdateInfo() {
		if( empty( $this->github_repo ) ) {
			return new \stdClass;
		}

		$downloader = new PluginDownloader( $this );
        return $downloader->getInfo();
	}

	/**
	 * @return string
	 */
	public function getChangelog() {
		if( empty( $this->github_repo ) ) {
			return '';
		}

        $downloader = new PluginDownloader( $this );
        return $downloader->getChangelog();
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		$info = $this->getUpdateInfo();
		return isset( $info->version ) ? $info->version : '';
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

		$updateInfo = (array) $this->getUpdateInfo();
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
