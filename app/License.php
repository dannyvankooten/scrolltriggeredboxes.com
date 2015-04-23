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
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function plan() {
		return $this->belongsTo('App\Plan', 'plan_id', 'id');
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
	public function allowsActivation() {
		return ! $this->isExpired();
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

		if( $plugin instanceof Collection ) {
			$plugins = $plugin;
		} else {
			$plugins = array( $plugin );
		}

		foreach( $plugins as $plugin ) {

			if( $this->grantsAccessTo($plugin) ) {
				continue;
			}

			$this->plugins()->attach( $plugin );
		}

		// reload plugins
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
		$this->load('activations');
		return $this->site_limit - count( $this->getPluginActivations($plugin) );
	}

	/**
	 * @return bool
	 */
	public function allowsActivationForPlugin($plugin) {
		return $this->allowsActivation() && ! $this->isAtSiteLimitForPlugin( $plugin );
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
