<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Payment
 * @package App
 *
 * @property int $id
 * @property User $user
 * @property Payment[] $refunds
 * @property License $license
 * @property string $currency
 * @property double $subtotal
 * @property double $tax
 * @property string $moneybird_invoice_id
 * @property int $subscription_id
 * @property int $user_id
 * @property string $stripe_id
 * @property string $braintree_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $related_payment_id
 * @property int $license_id
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
    public function license()
    {
        return $this->belongsTo('App\License', 'license_id', 'id');
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
            return '-' . $this->getCurrencySign() . ( abs( $this->getTotal() ) + 0 );
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
		return ! empty( $this->currency ) ? strtoupper( $this->currency ) : 'USD';
	}

	/**
	 * @return string
	 */
	public function getCurrencySign() {
		$map = [
			'USD' => '$',
			'EUR' => '€'
		];

        $currency = $this->getCurrency();
        if( ! isset( $map[ $currency ] ) ) {
            throw new \InvalidArgumentException(sprintf('%s has no known currency sign', $currency ) );
        }

        return $map[ $currency ];
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
	public function getGatewayName() {
        if( ! empty( $this->stripe_id ) ) {
            return 'Stripe';
        }

        if( ! empty( $this->braintree_id ) ) {
            return 'PayPal';
        }
    }

    /**
     * @return string
     */
	public function getGatewayUrl() {
        if( $this->stripe_id ) {
            return $this->getStripeUrl();
        }

        if( $this->braintree_id ) {
            return $this->getBraintreeUrl();
        }
    }

    /**
     * @return string
     */
	public function getBraintreeUrl() {
        $config = config('services.braintree');
        $subdomain = $config['environment'] != 'production' ? $config['environment'] : 'www';

        return sprintf( 'https://%s.braintreegateway.com/merchants/%s/transactions/%s', $subdomain, $config['merchant_id'], $this->braintree_id );
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
		return ! $this->isRefund() && count( $this->refunds) === 0 && $this->created_at->gt($border);
	}

    /**
     * @return bool
     */
	public function isRefund() {
	    return $this->subtotal < 0;
    }

    /**
     * @return bool
     */
    public function isRefunded() {
        return count( $this->refunds) > 0 && -($this->refunds->sum('subtotal')) == $this->subtotal;
    }
}
