<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

	protected $table = 'payments';
	public $timestamps = true;
	protected $fillable = [];

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
		return $this->total;
	}

	/**
	 * @return double
	 */
	public function getTotal() {
		return $this->total + $this->tax;
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
}
