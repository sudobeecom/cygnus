<?php

namespace SudoBee\Cygnus\Core\Traits;

use Exception;
use Illuminate\Support\Str;

trait HasResolveHelpers
{
	/**
	 * @template T
	 * @param T $class
	 * @return T|mixed
	 * @throws Exception
	 */
	public function resolve($class, string $name = null)
	{
		/** @phpstan-ignore-next-line */
		$resolvedName = $name ?? Str::camel(class_basename($class));

		$value =
			request()->route($resolvedName) === null
				? /** @phpstan-ignore-next-line */
					$this->parameters[$resolvedName] ?? null
				: request()->route($resolvedName);

		if ($value === null) {
			abort(404, "Could not resolve \"$resolvedName\".");
		}

		if ($class === null) {
			return $value;
		}

		$model = $class::find($value);

		if ($model === null) {
			abort(404, "Model was not found.");
		}

		return $model;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function resolveRoute(): string
	{
		preg_match_all(
			"/{+(.*?)}/",
			$this->route(),
			$parts,
			PREG_PATTERN_ORDER
		);

		$parameters = [];

		foreach ($parts[1] as $part) {
			$parameters[$part] = $this->resolve(null, $part);
		}

		$route = $this->route();

		foreach ($parameters as $key => $value) {
			/** @phpstan-ignore-next-line */
			$route = (string) Str::of($route)->replace(
				"{" . $key . "}",
				$value
			);
		}

		return $route;
	}
}
