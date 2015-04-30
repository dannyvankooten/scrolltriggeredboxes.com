<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {

	protected $table = 'plugins';
	protected $fillable = [ 'name', 'slug', 'version'];
	protected $guarded = ['id'];
	public $timestamps = true;

	// hidden from json export
	protected $hidden = array( 'id', 'created_at', 'updated_at', 'changelog', 'description', 'url', 'slug', 'upgrade_notice', 'tested', 'image_path' );
	protected $appends = [ 'page_url', 'image_url' ];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function plans() {
		return $this->belongsToMany('App\Plan', 'plan_plugins', 'plan_id', 'plugin_id');
	}

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
	 * @return array
	 */
	public function toWPArray() {
		return [
			'id' => $this->id,
			'url' => url( '/plugins/' . $this->url ),
			'homepage' => url( '/plugins/' . $this->url ),
			'package' => url( '/api/v1/plugins/' . $this->id .'/download' ),
			'download_url' => url( '/api/v1/plugins/' . $this->id .'/download' ),
			'name'      => $this->name,
			'version'   => $this->version,
			'author'    => $this->author,
			'sections'  => [
				'changelog'     => $this->changelog,
				'description'   => $this->description
			],
			'requires'  => '3.8',
			'tested'    => $this->tested,
			'last_updated' => $this->updated_at->format( 'F, Y' ),
			'upgrade_notice' => $this->upgrade_notice,
			'banners'   => [
				'high'      => asset( $this->image_path )
			]
		];
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
