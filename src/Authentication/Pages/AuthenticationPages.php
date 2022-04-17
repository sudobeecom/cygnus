<?php

namespace SudoBee\Cygnus\Authentication\Pages;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use SudoBee\Cygnus\Authentication\Operations\LogoutOperation;

class AuthenticationPages
{
	// TODO: add $enablePasswordReset?
	public static function register(bool $enableRegistration = false): void
	{
		LoginPage::register();
		LogoutOperation::register();

		if ($enableRegistration) {
			RegisterPage::register();
		}

		Route::get("/", fn() => redirect(RouteServiceProvider::getHomepage()));
	}
}
