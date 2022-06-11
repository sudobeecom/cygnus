<?php

namespace SudoBee\Cygnus\Authentication\Pages;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use SudoBee\Cygnus\Authentication\Operations\LogoutOperation;

class AuthenticationPages
{
	public static function register(): void
	{
		Route::middleware(config("cygnus.guest_middlewares"))->group(
			function () {
				LoginPage::register();

				if (config("cygnus.enable_registration")) {
					RegisterPage::register();
				}

				if (config("cygnus.enable_reset_password")) {
					ForgotPasswordPage::register();
					ForgotPasswordEmailSentPage::register();
					ResetPasswordPage::register();
				}
			}
		);

		LogoutOperation::register();

		Route::get("/", fn() => redirect(RouteServiceProvider::getHomepage()));
	}
}
