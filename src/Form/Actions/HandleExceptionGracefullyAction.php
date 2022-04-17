<?php

namespace SudoBee\Cygnus\Form\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use SudoBee\Cygnus\Core\Utilities\Notification;
use Throwable;

class HandleExceptionGracefullyAction
{
	/**
	 * @throws Throwable
	 */
	public function execute(
		Request $request,
		Throwable $throwable
	): JsonResponse|RedirectResponse {
		$isInertiaRequest = app(IsInertiaRequestAction::class)->execute(
			$request
		);

		if ($throwable instanceof ValidationException) {
			if ($isInertiaRequest) {
				/**
				 * Validation exception will be handled
				 * by InertiaJS
				 */
				throw $throwable;
			}

			return response()->json([
				"success" => false,
				"data" => null,
				"errors" => array_map(
					fn($fieldErrors) => $fieldErrors[0],
					$throwable->errors()
				),
				"notification" => Notification::getAndClear(),
			]);
		}

		report($throwable);

		Notification::danger(
			"Sorry for inconvenience, please try again later."
		);

		if ($isInertiaRequest) {
			return redirect()->back();
		}

		return response()->json([
			"success" => false,
			"data" => null,
			"errors" => [],
			"notification" => Notification::getAndClear(),
		]);
	}
}
