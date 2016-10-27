<?php namespace App;


use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class License
 *
 * @package App
 *
 * @property int $id
 * @property string $license_key
 * @property User $user
 * @property Activation[] $activations
 * @property Subscription $subscription
 * @property int $user_id
 * @property int $site_limit
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string stripe_subscription_id
 * @property string interval
 * @property boolean $auto_renews
 */
class License extends Model {

	use SoftDeletes;

	protected $table = 'licenses';
	protected $fillable = [];


	public $timestamps = true;
	protected $dates = [ 'created_at', 'updated_at', 'deleted_at', 'expires_at' ];

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
		return $this->hasMany('App\Activation', 'license_id', 'id')->orderBy('created_at', 'DESC');
	}

	/**
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function subscription() {
		return $this->hasOne('App\Subscription');
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
	 * @return float
	 */
	public function usagePercentage() {
		return count( $this->activations ) / $this->site_limit * 100;
	}

	/**
	 * @param User $user
	 * 
	 * @return bool
	 */
	public function belongsToUser( User $user ) {
		return $this->user_id == $user->id;
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

	/**
	 * @return bool
	 */
	public function hasActiveSubscription() {
		return $this->subscription && $this->subscription->active;
	}
	
}
