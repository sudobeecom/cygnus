<?php

namespace SudoBee\Cygnus\Authentication\Forms;

use App\Providers\RouteServiceProvider;
use SudoBee\Cygnus\Form\Fields\TextField;
use SudoBee\Cygnus\Form\Form;
use SudoBee\Cygnus\Form\Form\FormButton;
use SudoBee\Cygnus\Form\ProcessableForm;
use Domain\Team\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterForm extends ProcessableForm
{
	public function route(): string
	{
		return "/auth/register";
	}

	protected function form(Form $form): Form
	{
		return $form->withoutPanel()->setNodes([
			TextField::make("Full name"),
			TextField::make("Email")
				->presetEmail()
				->addValidationRule("unique:users"),
			TextField::make("Password")->presetPassword(),
			FormButton::make()->setTitle("Register"),
		]);
	}

	public function handle(object $validated)
	{
		$user = User::create([
			"name" => $validated->full_name,
			"email" => $validated->email,
			"password" => Hash::make($validated->password),
		]);

		Auth::login($user);

		event(new Registered($user));

		return redirect(RouteServiceProvider::getHomepage());
	}
}
