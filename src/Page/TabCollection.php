<?php

namespace SudoBee\Cygnus\Page;

use SudoBee\Cygnus\Page\Enums\TabsDesign;

abstract class TabCollection
{
	public string $tabsDesign = TabsDesign::REGULAR;

	abstract public function title(): string;

	/**
	 * @return array<int, class-string<TabPage>>
	 */
	abstract public function tabs(): array;

	public static function register(): void
	{
		$class = static::class;

		$tabCollection = new $class();

		foreach ($tabCollection->tabs() as $tabClass) {
			$tabClass::register();
		}
	}
}
