<?php declare(strict_types=1);

namespace Palette\DemoApp;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * Class RouterFactory
 * @package Palette\DemoApp
 */
class RouterFactory
{
    /**
     * Create app router.
     * @return RouteList
     */
	public static function createRouter(): RouteList
	{
		$router = new RouteList();
		$router[] = new Route('<presenter>/<action>', 'Palette:default');

		return $router;
	}
}
