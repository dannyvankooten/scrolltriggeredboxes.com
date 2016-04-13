<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Activation
 *
 * @package App
 *
 * @property License $license
 * @property Plugin $plugin
 * @property int id
 * @property string url
 * @property \DateTime created_at
 * @property \DateTime updated_at
 */
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
