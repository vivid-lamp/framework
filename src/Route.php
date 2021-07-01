<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Route
{
    /** @var RouteCollector */
    protected $routeCollector;

    protected  $groupPrefix = '';

    protected $groupMiddleware;

    protected $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function addRoute($method, $route, $handler, $middleware = '')
    {
        $route = $this->groupPrefix . $route;

        if (!empty($middleware) && !is_array($middleware)) {
            $middleware = [$middleware];
        } else {
            $middleware = [];
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

    public function post($route, $handler, $middleware = [])
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
            require $this->app->getRootPath() . 'App/routes.php';
        });

        $uri    = $request->getUri()->getPath();
        $method = $request->getMethod();

        $info   = $fastDispatcher->dispatch($method, $uri);

        [$routeStatus, $handler] = $info;
        $routeParam = $info[2] ?? [];

        foreach ($routeParam as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        if ($routeStatus !== \FastRoute\Dispatcher::FOUND) {
            throw new \RuntimeException('Route missed');
        }

        [$handler, $middleware] = $handler;

        $middleware = $middleware ?? [];
     
        $middleware[] = function (ServerRequestInterface $request, callable $next) use ($handler) {
            return $this->app->invoke($handler, [ServerRequestInterface::class => $request]);
        };

        return (new RequestHandler($middleware))->handle($request);
    }
}
