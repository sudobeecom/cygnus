<?php

namespace SudoBee\Cygnus\Tests;

use SudoBee\Cygnus\Page\Page;
use Database\Seeders\DatabaseSeeder;
use Domain\Integration\Models\Integration;
use Domain\Shop\Actions\CreateShopAction;
use Domain\Shop\Models\Shop;
use Domain\Team\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionException;

abstract class TestCase extends BaseTestCase
{
	use RefreshDatabase;
	use CreatesApplication;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(DatabaseSeeder::class);
	}

	/**
	 * @param mixed $object
	 * @param string $property
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function getPrivateProperty($object, string $property)
	{
		$reflectedClass = new ReflectionClass($object);
		$reflection = $reflectedClass->getProperty($property);
		$reflection->setAccessible(true);

		return $reflection->getValue($object);
	}

	public function withUser()
	{
		$this->actingAs(User::factory()->create());
	}

	public function withShop()
	{
		/** @var Shop $shop */
		$shop = app(CreateShopAction::class)->execute(
			"Fake shop",
			"http://fake-shop.test",
			Integration::whereActive(true)->first()
		);

		$shop->update(["secret" => "TEST_132"]);
	}

	public function getPageProps(Page $page)
	{
		return $page
			->handleRequest()
			->toResponse(request())
			->original->getData()["page"]["props"];
	}

	protected function assertRouteSlugExist(string $slug)
	{
		$this->assertContains(ltrim($slug, "/"), $this->getRouteSlugs());
	}

	protected function assertRouteSlugNotExist(string $slug)
	{
		$this->assertNotContains(ltrim($slug, "/"), $this->getRouteSlugs());
	}

	/**
	 * @return string[]
	 */
	private function getRouteSlugs(): array
	{
		$slugs = [];
		$routes = Route::getRoutes();

		foreach ($routes as $route) {
			$slugs[] = $route->uri();
		}
		return array_unique($slugs);
	}
}
