<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model {

	protected $table = 'license_sites';

	protected $fillable = ['url', 'plugin', 'active', 'license_id'];
	protected $guarded = ['id'];
	public $timestamps = true;

	public function license()
	{
		return $this->belongsTo('App\License', 'license_id', 'id');
	}
}
