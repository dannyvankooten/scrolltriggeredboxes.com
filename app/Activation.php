<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Activation extends Model {

	protected $table = 'activations';

	protected $fillable = ['url', 'domain'];
	public $timestamps = true;

	// hidden from json export
	protected $hidden = array( 'id', 'license_id', 'plugin_id', 'created_at', 'url' );

	public function license()
	{
		return $this->belongsTo('App\License', 'license_id', 'id');
	}

	public function plugin() {
		return $this->belongsTo('App\Plugin', 'plugin_id', 'id');
	}
}
