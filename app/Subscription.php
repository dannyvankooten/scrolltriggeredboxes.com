<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {

	protected $table = 'subscriptions';
	public $timestamps = true;

	protected $fillable = ['amount', 'interval', 'user_id', 'license_id', 'next_charge_at', 'active' ];


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function user() {
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public function license() {
		return $this->belongsTo( 'App\License', 'license_id', 'id' );
	}

}
