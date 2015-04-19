<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model {

	protected $table = 'plans';
	public $timestamps = true;
	protected $fillable = ['id', 'name', 'site_limit'];
	protected $guarded = ['id'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function plugins() {
		return $this->belongsToMany('App\Plugin', 'plan_plugins', 'plan_id', 'plugin_id');
	}
}
