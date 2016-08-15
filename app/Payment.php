<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Payment
 * @package App
 *
 * @property User $user
 * @property Subscription $subscription
 * @property Payment[] $activations
 * @property string $currency
 * @property double $subtotal
 * @property double $tax
 * @property string $moneybird_invoice_id
 * @property int $subscription_id
 * @property int $user_id
 * @property string $stripe_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property int $subscription_id
 * @property int $related_payment_id
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refunds() {
        return $this->hasMany('App\Payment', 'related_payment_id', 'id')->orderBy('created_at', 'DESC');
    }

	/**
	 * @return string
	 */
	public function getFormattedTotal() {

	    if( $this->getTotal() < 0 ) {
            return '- ' . $this->getCurrencySign() . abs( $this->getTotal() );
        }

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

	/**
	 * Determines whether the payment is still eligible for a refund (3 months).
	 *
	 * @return bool
	 */
	public function isEligibleForRefund() {
		$border = new Carbon('-90 days');
		return $this->subtotal > 0 && count( $this->refunds) === 0 && $this->created_at->gt($border);
	}
}
