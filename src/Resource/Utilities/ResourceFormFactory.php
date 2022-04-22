<?php

namespace SudoBee\Cygnus\Resource\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SudoBee\Cygnus\Core\Utilities\Translate;
use SudoBee\Cygnus\Form\Fields\TextField;
use SudoBee\Cygnus\Form\Form;

class ResourceFormFactory
{
	public static function make(
		Model $model,
		?Model $entity,
		string $actionLink,
		string $modelHeadline
	): Form {
		$fillableColumnKeys = collect($model->getFillable());

		$values =
			$entity === null
				? []
				: $fillableColumnKeys
					->diff($model->getHidden())
					->mapWithKeys(
						fn($columnKey) => [
							(string) $columnKey => $entity->{$columnKey},
						]
					)
					->all();

		return Form::make($actionLink)
			->setValues($values)
			->setTitle(
				Translate::text($entity === null ? "Create new" : "Edit") .
					" " .
					Str::of($modelHeadline)->lower()
			)
			->setSubmitButtonText($entity === null ? "Create" : "Update")
			->setNodes(
				$fillableColumnKeys
					->map(
						fn(string $columnKey) => TextField::make(
							(string) Str::of($columnKey)
								->headline()
								->lower()
								->ucfirst(),
							$columnKey
						)
					)
					->all()
			);
	}
}
