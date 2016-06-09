<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use DateTime;

/**
 * Class Payment
 * @package App
 *
 * @property User $user
 * @property Subscription $subscription
 * @property string $currency
 * @property double $subtotal
 * @property double $tax
 * @property string $moneybird_invoice_id
 * @property string $stripe_id
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property int $user_id
 * @property int $subscription_id
 */
class Payment extends Model
{
	protected $table = 'payments';
	public $timestamps = true;
	protected $fillable = [];

	protected $dates =  [ 'created_at', 'updated_at' ];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function subscription() {
		return $this->belongsTo('App\Subscription', 'subscription_id', 'id');
	}

	/**
	 * @return double
	 */
	public function getSubtotal() {
		return $this->subtotal;
	}

	/**
	 * @return double
	 */
	public function getTotal() {
		return number_format( $this->subtotal + $this->tax, 2 );
	}

	/**
	 * @return double
	 */
	public function getTax() {
		return $this->tax;
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		return strtoupper( $this->currency );
	}

	/**
	 * @return string
	 */
	public function getCurrencySign() {
		static $map = [
			'USD' => '$',
			'EUR' => '€'
		];

		return $map[ $this->currency ];
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
