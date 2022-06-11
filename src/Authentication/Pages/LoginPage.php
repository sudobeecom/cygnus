<?php

namespace SudoBee\Cygnus\Authentication\Pages;

use SudoBee\Cygnus\Authentication\Forms\LoginForm;
use SudoBee\Cygnus\Component\Components\Link\Link;
use SudoBee\Cygnus\Component\Components\Panel;
use SudoBee\Cygnus\Component\Components\Text;
use SudoBee\Cygnus\Layout\Layout;
use SudoBee\Cygnus\Layout\Layouts\CentralLayout;
use SudoBee\Cygnus\Page\Page;
use SudoBee\Cygnus\Page\Utilities\Link as Linker;
use Illuminate\Support\Facades\Route;

class LoginPage extends Page
{
	public function route(): string
	{
		return "/auth/login";
	}

	public function routeName(): string
	{
		return "login";
	}

	public function title(): string
	{
		return "Login to your account";
	}

	public function layout(): Layout
	{
		$layout = CentralLayout::make()->setTitle($this->title());

		if (config("cygnus.enable_registration")) {
			$layout->setSubtitle(
				Text::make(
					"Or ",
					Link::make()
						->setTitle("create new account")
						->toPage(RegisterPage::class)
				)
			);
		}

		return $layout;
	}

	public function nodes(): array
	{
		return [
			Panel::make()
				->setPadding(10)
				->setNodes([LoginForm::make()]),
		];
	}

	public function operations(): array
	{
		return [LoginForm::class];
	}
}
