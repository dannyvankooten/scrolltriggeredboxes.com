<?php namespace App;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;

class License extends Model {

	use SoftDeletes;

	protected $table = 'licenses';
	protected $fillable = [ 'license_key', 'expires_at', 'user_id' ];
	protected $guarded = ['id'];

	// hidden from json export
	protected $hidden = array( 'id', 'user_id', 'created_at', 'updated_at', 'deleted_at' );

	public $timestamps = true;
	protected $dates = ['deleted_at', 'expires_at' ];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function activations() {
		return $this->hasMany('App\Activation', 'license_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function plugins() {
		return $this->belongsToMany('App\Plugin', 'plugin_licenses', 'license_id', 'plugin_id' );
	}

	/**
	 * @return bool
	 */
	public function isExpired() {
		return $this->expires_at < Carbon::now();
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return ! $this->isExpired() && ! $this->trashed();
	}

	/**
	 * @param $domain
	 *
	 * @return static
	 */
	public function findDomainActivation($domain) {
		return $this->activations->filter(function($activation) use($domain){
			return $activation->domain === $domain;
		})->first();
	}

	/**
	 * @return bool
	 */
	public function isAtSiteLimit() {
		return count( $this->activations ) >= $this->site_limit;
	}

	/**
	 * @return int
	 */
	public function getActivationsLeft() {
		$this->load('activations');
		return $this->site_limit - count( $this->activations );
	}

	/**
	 * Generate a truly unique license key
	 *
	 * @return string
	 */
	public static function generateKey() {
		// generate a truly unique key
		$key_exists = true;
		$key = '';

		while( $key_exists ) {
			$key = strtoupper( sprintf( '%s-%s-%s-%s', str_random(5), str_random(5), str_random(5), str_random(5) ) );
			$key_exists = self::where('license_key', $key)->first();
		}

		return $key;
	}

}
