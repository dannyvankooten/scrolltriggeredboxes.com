<?php namespace App;


use Carbon\Carbon;
use DateInterval;
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
 * @property Payment[] $payments
 * @property int $user_id
 * @property int $site_limit
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deactivated_at
 * @property string $stripe_subscription_id
 * @property string $paypal_subscription_id
 * @property string $payment_method
 * @property string $interval
 * @property string $plan
 * @property string $status
 */
class License extends Model {

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
     * @deprecated 1.1
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subscription() {
        return $this->hasOne('App\Subscription');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function activations() {
		return $this->hasMany('App\Activation', 'license_id', 'id')->orderBy('created_at', 'DESC');
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany('App\Payment', 'license_id', 'id')->orderBy('created_at', 'DESC');
    }

    /**
     * Get the license status
     *
     * - active
     * - canceled
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Does this license have an active subscription?
     *
     * @return bool
     */
    public function isActive() {
        return $this->getStatus() === 'active' && ( ! empty( $this->stripe_subscription_id ) || ! empty( $this->paypal_subscription_id ) );
    }

	/**
     * Did this license expire?
     *
	 * @return bool
	 */
	public function isExpired() {
		return empty( $this->expires_at ) || $this->expires_at < Carbon::now();
	}

	/**
     * For a license to be valid, it needs to be active or not yet expired.
     *
	 * @return bool
	 */
	public function isValid() {
		return $this->isActive() || ! $this->isExpired();
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
	public function getActivationsCount() {
        return count($this->activations);
    }

	/**
	 * @return int
	 */
	public function getActivationsLeftCount() {
		return $this->site_limit - $this->getActivationsCount();
	}

	/**
	 * @return float
	 */
	public function usagePercentage() {
		return $this->getActivationsCount() / $this->site_limit * 100;
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
     * Extend license by 1 interval.
     */
    public function extend() {
        $fromDate = $this->expires_at;

        if(empty($fromDate) || $this->isExpired()) {
            $fromDate = Carbon::now();
        }

        // add 1 interval to current expiration date.
        $this->expires_at = $fromDate->modify("+1 {$this->interval}");
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
     * @return string
     */
	public function getPlan() {

        // license has no plan yet, calculate from site limit.
        if( empty( $this->plan ) ) {
            if( $this->site_limit <= 2 ) {
                $this->plan = 'personal';
            } else if( $this->site_limit >= 8 ) {
                $this->plan = 'developer';
            } else {
                // legacy plans
                $this->plan = sprintf( '2016-%d-sites', $this->site_limit );
            }
        }

        return $this->plan;
    }

}
