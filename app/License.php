<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class License extends Model {

	protected $table = 'licenses';
	protected $fillable = ['license_key', 'expires_at', 'email', 'sendowl_order_id', 'sendowl_product_id'];
	protected $guarded = ['id'];

	public $timestamps = true;

	public function sites()
	{
		return $this->hasMany('App\Site', 'license_id', 'id');
	}

	public function isAtLimit() {
		return count( $this->getActiveSites() ) >= $this->site_limit;
	}

	public function isValid() {
		return $this->expires_at >= new \DateTime('now');
	}

	public function getActiveSites() {
		return $this->sites->filter(function($site) {
			return $site->active;
		});
	}

	/**
	 * @param $url
	 * @param $plugin
	 *
	 * @return null
	 */
	public function getSite( $url, $plugin )
	{

		foreach($this->sites as $site) {
			if( $site->url === $url && $site->plugin === $plugin ) {
				return $site;
			}
		}

		return null;
	}
}
