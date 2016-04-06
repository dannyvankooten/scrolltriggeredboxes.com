<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {

	protected $table = 'subscriptions';
	public $timestamps = true;

	protected $fillable = ['amount', 'payment_token', 'interval', 'user_id', 'license_id' ];


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function user() {
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

}
