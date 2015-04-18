<?php namespace App;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;

class License extends Model {

	use SoftDeletes;

	protected $table = 'licenses';
	protected $fillable = ['license_key', 'expires_at', 'sendowl_order_id'];
	protected $guarded = ['id'];

	// hidden from json export
	protected $hidden = array( 'id', 'sendowl_order_id', 'user_id', 'created_at', 'updated_at', 'deleted_at' );

	public $timestamps = true;
	protected $dates = ['deleted_at', 'expires_at' ];

	public function user() {
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public function activations()
	{
		return $this->hasMany('App\Activation', 'license_id', 'id');
	}

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
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function grantsAccessTo( $plugin ) {
		return $this->plugins->contains( $plugin->id );
	}

	/**
	 * @param $plugin
	 */
	public function grantAccessTo( $plugin ) {
		if($this->grantsAccessTo($plugin)) {
			return;
		}

		$this->plugins()->save( $plugin );
		$this->load('plugins');
	}

	/**
	 * @param $plugin
	 *
	 * @return Collection
	 */
	public function getPluginActivations( $plugin ) {
		return $this->activations->filter(function($a) use($plugin) {
			return $a->plugin->id === $plugin->id;
		});
	}

	/**
	 * @return bool
	 */
	public function isAtSiteLimitForPlugin($plugin) {
		return count( $this->getPluginActivations($plugin) ) >= $this->site_limit;
	}

	/**
	 * @param $plugin
	 *
	 * @return int
	 */
	public function getActivationsLeftForPlugin($plugin) {
		return $this->site_limit - count( $this->getPluginActivations($plugin) );
	}

	/**
	 * @return bool
	 */
	public function allowsActivationForPlugin($plugin) {
		return ! $this->isExpired() && ! $this->isAtSiteLimitForPlugin( $plugin );
	}

	/**
	 * @param $domain
	 * @param $plugin
	 *
	 * @return static
	 */
	public function findDomainActivationForPlugin($domain, $plugin) {
		$this->load('activations');
		return $this->getPluginActivations($plugin)->filter(function($activation) use($domain){
			return $activation->domain === $domain;
		})->first();
	}
}
