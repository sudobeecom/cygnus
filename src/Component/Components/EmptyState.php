<?php

namespace SudoBee\Cygnus\Component\Components;

use SudoBee\Cygnus\Component\Component;
use SudoBee\Cygnus\Component\Traits\HasButtons;
use SudoBee\Cygnus\Component\Traits\HasDescription;
use SudoBee\Cygnus\Component\Traits\HasImage;
use SudoBee\Cygnus\Component\Traits\HasTitle;
use SudoBee\Cygnus\Core\Utilities\ExportBuilder;

class EmptyState extends Component
{
	use HasTitle;
	use HasDescription;
	use HasImage;
	use HasButtons;

	public static function make(): self
	{
		return new self();
	}

	/**
	 * @return array<mixed>
	 */
	public function export(): array
	{
		return ExportBuilder::make($this)
			->mergeProperties($this->titleExport())
			->mergeProperties($this->descriptionExport())
			->mergeProperties($this->imageExport())
			->mergeProperties($this->buttonsExport())
			->export();
	}
}
