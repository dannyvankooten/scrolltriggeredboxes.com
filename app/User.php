<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ 'name', 'email', 'password', 'card_last_four', 'company', 'country', 'vat_number' ];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function licenses()
	{
		return $this->hasMany('App\License', 'user_id', 'id');
	}

	/**
	 * @return bool
	 */
	public function hasValidLicense()
	{
		$validLicenses = $this->licenses->filter(function(License $l) {
			return $l->isValid();
		});

		return count( $validLicenses ) > 0;
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		$pos = strpos( $this->name, ' ' );
		if( ! $pos ) {
			return $this->name;
		}

		return substr( $this->name, 0, $pos );
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		$pos = strpos( $this->name, ' ' );
		if( ! $pos ) {
			return $this->name;
		}

		return substr( $this->name, $pos );
	}

	/**
	 *
	 */
	public function inEurope() {
		$euCountries = Countries::europe();
		return in_array( $this->country, array_values( $euCountries ) );
	}

}
