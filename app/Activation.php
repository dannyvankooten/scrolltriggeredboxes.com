<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use DateTime;

/**
 * Class Activation
 *
 * @package App
 *
 *
 * @property License $license
 * @property Plugin $plugin
 * @property int id
 * @property string url
 * @property string $key
 * @property string $domain
 * @property int $license_id
 * @property int $plugin_id
 * @property DateTime $created_at
 * @property DateTime $updated_at
 */
class Activation extends Model {

	protected $table = 'activations';
	public $timestamps = true;

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function license()
	{
		return $this->belongsTo('App\License', 'license_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function plugin() {
		return $this->belongsTo('App\Plugin', 'plugin_id', 'id');
	}

	/**
	 * @param License $license
	 *
	 * @return bool
	 */
	public function belongsToLicense( License $license ) {
		return $this->license_id == $license->id;
	}

	/**
	 * Generate a unique activation key of 60 chars.
	 *
	 * @return string
	 */
	public static function generateKey() {
		// generate a truly unique key
		$key_exists = true;
		$key = '';

		while( $key_exists ) {
			$key = str_random(60);
			$key_exists = self::where('key', $key)->first();
		}

		return $key;
	}
}
