<?php

namespace SudoBee\Cygnus\Form\Actions;

use SudoBee\Cygnus\Component\Component;
use SudoBee\Cygnus\Form\Fields\Field;

class GetNestedFieldsDefaultValuesAction
{
	/**
	 * @param Component[] $nodes
	 * @return array<string, mixed>
	 */
	public function execute(array $nodes): array
	{
		$fields = app(GetNestedFieldsAction::class)->execute($nodes);

		return array_reduce(
			$fields,
			function (array $previous, Component $field) {
				if ($field instanceof Field) {
					$previous = array_merge(
						$previous,
						$field->getDefaultValue()
					);
				}

				return $previous;
			},
			[]
		);
	}
}
