<?php

namespace SudoBee\Cygnus\Authentication\Pages;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use SudoBee\Cygnus\Authentication\Operations\LogoutOperation;

class AuthenticationPages
{
	private static bool $enableRegistration = false;

	private static bool $enablePasswordReset = false;

	/** @var array<int, mixed>|string|null  */
	private array|string|null $guestMiddleware = "guest";

	public static function make(): self
	{
		return new self();
	}

	public static function isRegistrationEnabled(): bool
	{
		return self::$enableRegistration;
	}

	public static function isPasswordResetEnabled(): bool
	{
		return self::$enablePasswordReset;
	}

	/**
	 * @param bool $enable
	 * @return static
	 */
	public function enableRegistration(bool $enable = true)
	{
		self::$enableRegistration = $enable;

		return $this;
	}

	/**
	 * @param bool $enable
	 * @return static
	 */
	public function enablePasswordReset(bool $enable = true)
	{
		self::$enablePasswordReset = $enable;

		return $this;
	}

	/**
	 * @param array<int, mixed>|string|null $middleware
	 * @return static
	 */
	public function setGuestMiddleware(array|string|null $middleware)
	{
		$this->guestMiddleware = $middleware;

		return $this;
	}

	public function register(): void
	{
		Route::middleware($this->guestMiddleware ?? [])->group(function () {
			LoginPage::register();

			if (self::isRegistrationEnabled()) {
				RegisterPage::register();
			}

			if (self::isPasswordResetEnabled()) {
				ForgotPasswordPage::register();
				ForgotPasswordEmailSentPage::register();
				ResetPasswordPage::register();
			}
		});

		LogoutOperation::register();

		Route::get("/", fn() => redirect(RouteServiceProvider::getHomepage()));
	}
}
