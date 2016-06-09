<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use DateTime;

/**
 * Class Subscription
 *
 * @package App
 *
 * @property User $user
 * @property License $license
 * @property Payment[] $payments
 * @property bool $active
 * @property Carbon $next_charge_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property double $amount
 * @property int $user_id
 * @property int $license_id
 */
class Subscription extends Model {

	protected $table = 'subscriptions';
	public $timestamps = true;
	protected $fillable = [ 'interval', 'next_charge_at', 'active' ];
	protected $dates = ['created_at', 'updated_at', 'next_charge_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function license() {
		return $this->belongsTo( 'App\License', 'license_id', 'id' );
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function payments()
	{
		return $this->hasMany('App\Payment', 'subscription_id', 'id');
	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		return $this->active;
	}

	/**
	 * @return bool
	 */
	public function isPaymentDue() {
		$now = new DateTime('now');
		return $this->isActive() && $now > $this->next_charge_at;
	}

	/**
	 * @return DateTime
	 */
	public function getNextChargeDate() {
		return $this->isPaymentDue() ? new DateTime('now') : $this->next_charge_at;
	}

	
	/**
	 * @return double
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @return double
	 */
	public function getTaxAmount() {

		$taxRate = $this->user->getTaxRate();
		$taxAmount = 0.00;

		if( $taxRate > 0) {
			$taxAmount = $this->amount * ( $taxRate / 100 );
		}

		return $taxAmount;
	}

	/**
	 * Gets the amount for this subscription incl. VAT
	 */
	public function getAmountInclTax() {
		return round( $this->getAmount() + $this->getTaxAmount(), 2 );
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function belongsToUser( User $user ) {
		return $this->user_id == $user->id;
	}

}
