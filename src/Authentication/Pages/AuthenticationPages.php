<?php

namespace SudoBee\Cygnus\Authentication\Pages;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthenticationPages
{
	// TODO: add $enablePasswordReset?
	public static function register(bool $enableRegistration = false): void
	{
		LoginPage::register();

		if ($enableRegistration) {
			RegisterPage::register();
		}

		Route::get("/", fn() => redirect(RouteServiceProvider::getHomepage()));
	}
}
