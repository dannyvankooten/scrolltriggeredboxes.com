<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		//'App\Http\Middleware\VerifyCsrfToken',
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth.user' => 'App\Http\Middleware\AuthenticateUser',
		'auth.user+license' => 'App\Http\Middleware\AuthenticateUserAndLicense',
		'auth.license' => 'App\Http\Middleware\AuthenticateLicense',
		'auth.license+site' => 'App\Http\Middleware\AuthenticateLicenseAndSite',
		'auth.admin' => 'App\Http\Middleware\AuthenticateAdmin',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
		'sendowl.signature' => 'App\Http\Middleware\VerifySendowlSignature',
		'helpscout.signature' => 'App\Http\Middleware\VerifyHelpScoutSignature',
	];

}
