<?php

namespace SudoBee\Cygnus\Page\Resolvers;

use SudoBee\Cygnus\Page\Objects\NavigationItem;

class NavigationResolver
{
	/** @var NavigationItem[] */
	private array $navigationItems = [];

	/**
	 * @param NavigationItem[] $navigationItems
	 */
	public function __construct(array $navigationItems = [])
	{
		$this->navigationItems = $navigationItems;
	}

	/**
	 * @return NavigationItem[]
	 */
	public function getNavigationItems(): array
	{
		return $this->navigationItems;
	}
}
