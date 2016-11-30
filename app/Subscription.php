<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use DateTime;

/**
 * Class Subscription
 *
 * @package App
 *
 * @property int $id
 * @property User $user
 * @property License $license
 * @property Payment[] $payments
 * @property bool $active
 * @property Carbon $next_charge_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property double $amount
 * @property string $interval
 * @property int $user_id
 * @property int $license_id
 * @property string $paypal_id
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
        return $this->belongsTo('App\License', 'license_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments() {
        return $this->hasMany('App\Payment', 'subscription_id', 'id')->orderBy('created_at', 'DESC');
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
        return round( $this->amount, 2 );
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

        return round( $taxAmount, 2 );
    }

    /**
     * Gets the amount for this subscription incl. VAT
     */
    public function getAmountInclTax() {
        return $this->getAmount() + $this->getTaxAmount();
    }

    /**
     * @return string
     */
    public function getFormattedAmount() {
        return '$' . ( $this->getAmount() + 0 );
    }

    /**
     * @return string
     */
    public function getFormattedTaxAmount() {
        return '$' . $this->getTaxAmount();
    }

    /**
     * @return string
     */
    public function getFormattedAmountInclTax() {
        return '$' . ( $this->getAmountInclTax() + 0 );
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
