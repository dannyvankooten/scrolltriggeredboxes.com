<?php namespace App;

use DvK\Laravel\Vat\Facades\Rates as VatRates;
use DvK\Laravel\Vat\Facades\Countries as Countries;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * Class User
 *
 * @package App
 *
 * @property License[] $licenses
 * @property Payment[] $payments
 * @property string $email
 * @property string $name
 * @property string $country
 * @property string $vat_number
 * @property string $company
 * @property string $card_last_four
 * @property string $address
 * @property string $city
 * @property string $zip
 * @property string $state
 * @proeprty boolean $is_admin
 * @property \DateTime created_at
 * @property \DateTime updated_at
 */
class User extends Model implements AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract {

	use Authorizable, Authenticatable, CanResetPassword;

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
	protected $fillable = [ 'name', 'email', 'address', 'city', 'zip', 'state', 'company', 'country', 'vat_number' ];

	/**
	 * @var array
	 */
	protected $dates = [ 'updated_at', 'created_at', 'last_login_at' ];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function licenses()
	{
		return $this->hasMany('App\License', 'user_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function payments()
	{
		return $this->hasMany('App\Payment', 'user_id', 'id');
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
	 * @return boolean
	 */
	public function inEurope() {
		return Countries::inEurope( $this->country );
	}

	/**
	 * @return double
	 */
	public function getTaxRate() {

		// no tax for non-EU customers
		if( ! $this->inEurope() ) {
			return 0;
		}

		// Dutch tax for all NL customers
		if( $this->country === 'NL' ) {
			return VatRates::country( 'NL', 'standard' );
		}

		// Reverse charge for EU businesses (outside of NL)
		if( ! empty( $this->vat_number ) ) {
			return 0;
		}

		// EU tax rate of specific country otherwise
		return VatRates::country( $this->country, 'standard' );
	}

	/**
	 * @return bool
	 */
	function isAdmin() {
		return (bool) $this->is_admin;
	}

}
