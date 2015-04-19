<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {

	protected $table = 'plugins';
	protected $fillable = [ 'name', 'slug', 'version'];
	protected $guarded = ['id'];
	public $timestamps = true;

	// hidden from json export
	protected $hidden = array( 'id', 'created_at', 'updated_at', 'version', 'changelog', 'author', 'description', 'url' );

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function plans() {
		return $this->belongsToMany('App\Plan', 'plan_plugins', 'plan_id', 'plugin_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function licenses() {
		return $this->belongsToMany('App\License', 'plugin_licenses', 'license_id', 'plugin_id' );
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function sites() {
		return $this->hasMany('App\Activation', 'plugin_id', 'id');
	}



}
