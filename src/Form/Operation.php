<?php

namespace SudoBee\Cygnus\Form;

use SudoBee\Cygnus\Core\Traits\HasResolveHelpers;
use SudoBee\Cygnus\Core\Utilities\Notification;
use SudoBee\Cygnus\Form\Enums\OperationResponseType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class Operation
{
	use HasResolveHelpers;

	/** @var array<string, string> $parameters */
	protected array $parameters = [];

	protected string $responseType = OperationResponseType::REGULAR;

	public function __construct()
	{
		if (method_exists($this, "initStore")) {
			$this->initStore();
		}
	}

	abstract public function route(): string;

	/**
	 * @param object $validated
	 * @return mixed
	 */
	abstract public function handle(object $validated);

	/**
	 * @param object $validated
	 * @return mixed
	 */
	protected function callHandle(object $validated)
	{
		return $this->handle($validated);
	}

	/**
	 * @param Request $request
	 * @return array<mixed>
	 * @throws ValidationException
	 */
	protected function validate(Request $request): array
	{
		return [];
	}

	final protected function updateStore(): void
	{
		if (property_exists($this, "store")) {
			$this->store->update();
		}
	}

	public function authorize(): bool
	{
		return true;
	}

	/**
	 * @param Request $request
	 * @return JsonResponse|RedirectResponse
	 * @throws ValidationException
	 */
	public function handleRequest(
		Request $request
	): JsonResponse|RedirectResponse {
		try {
			$this->updateStore();

			$isInertiaRequest = $this->isInertiaRequest($request);

			if (!$this->authorize()) {
				Notification::danger("You do not have permission for that.");

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

			$validated = $this->validate($request);

			$response = $this->callHandle((object) $validated);

			if ($isInertiaRequest) {
				return $response;
			}

			return response()->json([
				"success" => true,
				"data" => $response,
				"errors" => null,
				"notification" => Notification::getAndClear(),
			]);
		} catch (Throwable $throwable) {
			return $this->handleExceptionGracefully($request, $throwable);
		}
	}

	/**
	 * @throws ValidationException
	 */
	private function handleExceptionGracefully(
		Request $request,
		Throwable $throwable
	): JsonResponse|RedirectResponse {
		$isInertiaRequest = $this->isInertiaRequest($request);

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

	private function isInertiaRequest(Request $request): bool
	{
		return $request->headers->get("X-Inertia") !== null;
	}

	public function getResponseType(): string
	{
		return $this->responseType;
	}

	/**
	 * @param array<string, string> $parameters
	 * @return string
	 */
	public static function link(array $parameters = []): string
	{
		$className = get_called_class();

		/** @var Operation $operation */
		/** @phpstan-ignore-next-line */
		$operation = new $className();

		$route = $operation->route();

		foreach ($parameters as $key => $value) {
			$route = (string) Str::of($route)->replace(
				"{" . $key . "}",
				$value
			);
		}

		return $route;
	}

	public static function register(): void
	{
		$operationClass = get_called_class();

		/** @var Operation $operation */
		/** @phpstan-ignore-next-line */
		$operation = new $operationClass();

		Router::post($operation->route(), [$operationClass, "handleRequest"]);
	}
}
