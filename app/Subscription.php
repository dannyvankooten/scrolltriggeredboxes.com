<?php namespace App;

use Illuminate\Database\Eloquent\Model;

use DateTime;

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
	public function getVatAmount() {

		$vatRate = $this->user->getVatRate();
		$vatAmount = 0.00;

		if( $vatRate > 0) {
			$vatAmount = $this->amount * ( $vatRate / 100 );
		}

		return $vatAmount;
	}

	/**
	 * Gets the amount for this subscription incl. VAT
	 */
	public function getAmountInclVat() {
		return $this->getAmount() + $this->getVatAmount();
	}

}
