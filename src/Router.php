<?php

declare(strict_types=1);

namespace VividLamp\Framework;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VividLamp\Framework\Exception\RouteMissed;

class Router
{
    /** @var RouteCollector */
    protected $routeCollector;

    /** @var string */
    protected $groupPrefix = '';

    /** @var string[] */
    protected $groupMiddleware;

    /** @var App */
    protected $app;

    /** @var string */
    protected $configFile;

    public function __construct(App $app, string $configFile)
    {
        $this->app = $app;
        $this->configFile = $configFile;
    }

    /**
     * @param mixed $method
     * @param string $route
     * @param callable|array $handler
     * @param string|array $middleware
     */
    public function addRoute($method, $route, $handler, $middleware = '')
    {
        $route = $this->groupPrefix . $route;

        if (empty($middleware)) {
            $middleware = [];
        } elseif (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        if (is_array($this->groupMiddleware)) {
            $middleware = array_merge($this->groupMiddleware, $middleware);
        } elseif (!empty($this->groupMiddleware)) {
            array_push($middleware, $this->groupMiddleware);
        }

        $this->routeCollector->addRoute($method, $route, [$handler, $middleware]);
    }

    public function get($route, $handler, $middleware = '')
    {
        $this->routeCollector->addRoute('GET', $route, [$handler, $middleware]);
    }

    public function post($route, $handler, $middleware = '')
    {
        $this->routeCollector->addRoute('POST', $route, [$handler, $middleware]);
    }

    /**
     * @param string $prefix
     * @param callable $callable
     * @param string|string[] $middleware
     */
    public function addGroup($prefix, $callable, $middleware = '')
    {
        $currentGroupPrefix = $this->groupPrefix;
        $currentGroupMiddleware = $this->groupMiddleware;
        $this->groupPrefix = $prefix;
        $this->groupMiddleware = $middleware;
        $callable();
        $this->groupPrefix = $currentGroupPrefix;
        $this->groupMiddleware = $currentGroupMiddleware;
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $fastDispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $this->routeCollector = $r;
            require $this->configFile;
        });

        $info = $fastDispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($info[0] === Dispatcher::NOT_FOUND || $info[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new RouteMissed('Route missed', $info[0]);
        }

        $routeParam = $info[2] ?? [];

        foreach ($routeParam as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        [$handler, $middleware] = $info[1];

        $middleware[] = function (ServerRequestInterface $request, callable $next) use ($handler) {
            if (is_array($handler) && !is_object($handler[0])) {
                $handler[0] = $this->app->make($handler[0]);
            }
            return $this->app->call($handler, [ServerRequestInterface::class => $request]);
        };

        return (new RequestHandler($middleware))->handle($request);
    }
}
