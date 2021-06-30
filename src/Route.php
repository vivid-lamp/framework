<?php

namespace SleepyLamp\Framework;

use FastRoute\RouteCollector;

class Route
{
    /** @var RouteCollector */
    protected static $routeCollector;

    /** @var string */
    protected static $groupPrefix = '';

    /** @var string|array */
    protected static $groupMiddleware;

    public static function setRouteCollector(RouteCollector $routeCollector)
    {
        static::$routeCollector = $routeCollector;
    }

    /**
     * @param string|string[] $method
     * @param string $route
     * @param Closure|string[2] $handler
     * @param string|string[] $middleware
     */
    public static function addRoute($method, $route, $handler, $middleware = [])
    {
        $route = static::$groupPrefix . $route;

        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }
        if (is_array(static::$groupMiddleware)) {
            $middleware = array_merge(static::$groupMiddleware, $middleware);
        } elseif (isset(static::$groupMiddleware)) {
            array_push($middleware, static::$groupMiddleware);
        }
        static::$routeCollector->addRoute($method, $route, [$handler, $middleware]);
    }


    public static function get($route, $handler, $middleware = [])
    {
        static::$routeCollector->addRoute('GET', $route, [$handler, $middleware]);
    }

    public static function post($route, $handler, $middleware = [])
    {
        static::$routeCollector->addRoute('POST', $route, [$handler, $middleware]);
    }

    /**
     * @param string $prefix
     * @param callable $callable
     * @param string|string[] $middleware
     */
    public static function addGroup($prefix, $callable, $middleware = [])
    {
        $currentGroupPrefix = static::$groupPrefix;
        $currentGroupMiddleware = static::$groupMiddleware;
        static::$groupPrefix = $prefix;
        static::$groupMiddleware = $middleware;
        $callable();
        static::$groupPrefix = $currentGroupPrefix;
        static::$groupMiddleware = $currentGroupMiddleware;
    }
}
