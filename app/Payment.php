<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
 * @property Carbon $created_at
 * @property Carbon $updated_at
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
		return round( $this->subtotal, 2 );
	}

	/**
	 * @return string
	 */
	public function getFormattedTotal() {
		return $this->getCurrencySign() . ( $this->getTotal() + 0 );
	}

	/**
	 * @return double
	 */
	public function getTotal() {
		return $this->getSubtotal() + $this->getTax();
	}

	/**
	 * @return double
	 */
	public function getTax() {
		return round( $this->tax, 2 );
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

		if( ! empty( $this->currency ) ) {
			return $map[ $this->currency ];
		}

		return '$';
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
	 * @return string
	 */
	public function getStripeUrl() {
		return sprintf( 'https://dashboard.stripe.com/payments/%s', $this->stripe_id );
	}

	/**
	 * @return string
	 */
	public function getMoneybirdUrl() {
		return sprintf( 'https://moneybird.com/%s/sales_invoices/%s', config('services.moneybird.administration'), $this->moneybird_invoice_id );
	}
}
