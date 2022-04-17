<?php

namespace SudoBee\Cygnus\Page;

use SudoBee\Cygnus\Component\Component;
use SudoBee\Cygnus\Core\Traits\HasRegisterRoutes;
use SudoBee\Cygnus\Core\Traits\HasResolveHelpers;
use SudoBee\Cygnus\Core\Utilities\ExportBuilder;
use SudoBee\Cygnus\Form\Operation;
use SudoBee\Cygnus\Layout\Layout;
use SudoBee\Cygnus\Layout\Layouts\TopSideLayout;
use SudoBee\Cygnus\Page\Resolvers\LayoutResolver;
use SudoBee\Cygnus\Page\Resolvers\NavigationResolver;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route as Router;
use Inertia\Inertia;
use Inertia\Response;

abstract class Page extends Controller
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	use HasRegisterRoutes;
	use HasResolveHelpers;

	abstract public function title(): string;

	abstract public function route(): string;

	/**
	 * @return Component[]
	 */
	abstract public function nodes(): array;

	public function routeName(): ?string
	{
		return null;
	}

	/**
	 * @return string[]
	 */
	public function operations(): array
	{
		return [];
	}

	/**
	 * @return Component[]
	 */
	public function actions(): array
	{
		return [];
	}

	public function layout(): Layout
	{
		/**
		 * @var Layout|LayoutResolver $layout
		 */
		$layout = app(LayoutResolver::class);

		if ($layout instanceof LayoutResolver) {
			return TopSideLayout::make();
		}

		return $layout;
	}

	/**
	 * @return array{route: string, title: string, activeMatch: string}[]
	 */
	public function navigation(): array
	{
		$navigation = app(NavigationResolver::class);

		return ExportBuilder::exportArray($navigation->getNavigationItems());
	}

	protected function getPageTitle(): string
	{
		return $this->title();
	}

	/**
	 * @todo Change mixed to DTO
	 * @return array<string, mixed>[]
	 */
	protected function getTabs(): array
	{
		return [];
	}

	protected function getTabsDesign(): ?string
	{
		return null;
	}

	public function handleRequest(): Response
	{
		// TODO: also handle all exceptions gracefully, like done in Operation class

		return Inertia::render("StructuredPage", [
			"layout" => $this->layout()->export(),
			"layoutProperties" => [
				"title" => __($this->getPageTitle()),
				"navigation" => $this->navigation(),
				"tabs" => $this->getTabs(),
				"tabsDesign" => $this->getTabsDesign(),
			],
			"nodes" => ExportBuilder::exportArray($this->nodes()),
		]);
	}

	public function registerRoutes(): void
	{
		foreach ($this->operations() as $operationClass) {
			/** @var Operation $operation */
			$operation = new $operationClass();

			$operation::register();
		}

		$routeName = $this->routeName();
		$route = Router::get($this->route(), fn() => $this->handleRequest());
		if ($routeName !== null) {
			$route->name($routeName);
		}
	}
}
