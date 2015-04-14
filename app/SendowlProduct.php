<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SendowlProduct extends Model {

	protected $table = 'sendowl_products';
	public $timestamps = true;
	protected $fillable = ['id', 'name', 'plugin_id', 'site_limit'];
	protected $guarded = ['id'];

	public function plugin() {
		return $this->belongsTo('App\Plugin', 'plugin_id', 'id');
	}
}
