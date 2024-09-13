<?php
use Illuminate\Support\Str;

function getRouteByName($name)
{
    $routes = \Illuminate\Support\Facades\Route::getRoutes();

    /** @var Route $route */
    $route = $routes->getByName($name);

    if (!$route) {
        throw new InvalidArgumentException("Route with name [{$name}] not found!");
    }

    return $route;
}




function longName(int $min = 10, int $max = 20): string
{
    return Str::repeat('craftive', rand($min, $max));
}
