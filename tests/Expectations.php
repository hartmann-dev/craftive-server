<?php

expect()->extend('toHaveMiddleware', function (string $middleware) {
    if ($this->value instanceof \Illuminate\Routing\Route) {
        $route = $this->value;
    } else if (is_string($this->value)) {
        $route = getRouteByName($this->value);
    } else {
        throw new InvalidArgumentException('Value is not a Route or String, ' . gettype($this->value) . ' given.');
    }

    expect($route->middleware())->toContain($middleware);
    return $this;
});

expect()->extend('toHaveMiddlewares', function (array $middlewares) {
    foreach ($middlewares as $middleware) {
        expect($this->value)->toHaveMiddleware($middleware);
    }
    return $this;
});


expect()->extend('toHaveExactMiddleware', function (string $middleware) {
    expect($this->value)->toHaveExactMiddlewares([$middleware]);
    return $this;
});

expect()->extend('toHaveExactMiddlewares', function (array $middlewares) {
    if ($this->value instanceof \Illuminate\Routing\Route) {
        $route = $this->value;
    } else if (is_string($this->value)) {
        $route = getRouteByName($this->value);
    } else {
        throw new InvalidArgumentException('Value is not a Route or String, ' . gettype($this->value) . ' given.');
    }
    expect($route)->toHaveMiddlewares($middlewares);
    expect($route->middleware())->toHaveCount(count($middlewares));

    return $this;
});
