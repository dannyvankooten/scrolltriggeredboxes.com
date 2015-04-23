<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model {

	protected $table = 'plans';
	public $timestamps = true;
	protected $fillable = ['id', 'name', 'site_limit', 'sendowl_product_id'];
	protected $guarded = ['id'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function plugins() {
		return $this->belongsToMany('App\Plugin', 'plan_plugins', 'plan_id', 'plugin_id');
	}

	public function licenses() {
		return $this->hasMany('App\License', 'plan_id', 'id');
	}

}
