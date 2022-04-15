<?php

namespace SudoBee\Cygnus\Authentication\Pages;

use SudoBee\Cygnus\Authentication\Partials\SubtitleWithLoginPageLink;
use SudoBee\Cygnus\Component\Components\Text;
use SudoBee\Cygnus\Layout\Layout;
use SudoBee\Cygnus\Layout\Layouts\UnauthorizedLayout\UnauthorizedLayout;
use SudoBee\Cygnus\Page\Page;

class ForgotPasswordEmailSentPage extends Page
{
	public function title(): string
	{
		return "Reset your password";
	}

	public function route(): string
	{
		return "/auth/password/sent";
	}

	public function layout(): Layout
	{
		return UnauthorizedLayout::make()->setSubtitle(
			SubtitleWithLoginPageLink::make()
		);
	}

	public function nodes(): array
	{
		return [Text::make("passwords.sent")];
	}
}
