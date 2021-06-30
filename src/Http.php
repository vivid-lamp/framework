<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

use Throwable;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use SleepyLamp\Framework\Route;

class Http
{

    protected $app;

    protected $config;

    protected $middleware = [];

    public function __construct(App $app, Config $config)
    {
        $this->app      = $app;
        $this->config   = $config;
    }

    public function run(?ServerRequestInterface $request = null)
    {
        try {
            $this->initialize();
            $response = $this->runWithRequest($request);
        } catch (Throwable $e) {
            throw $e;
        }
        $this->send($response);
    }
    public function initialize()
    {
    }

    public function runWithRequest(?ServerRequestInterface $request = null): ResponseInterface
    {
        $middleware = $this->config->get('middleware');

        $middleware[] = function (ServerRequestInterface $request, RequestHandlerInterface $next) {
            return $this->dispatchToRoute($request);
        };

        $request = $request ?? ServerRequestFactory::createFromGlobals();

        return (new RequestHandler($middleware))->handle($request);
    }

    public function dispatchToRoute(ServerRequestInterface $request): ResponseInterface
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            Route::setRouteCollector($r);
            require $this->app->getRootPath() . 'App/routes.php';
        });

        $uri    = $request->getUri()->getPath();
        $method = $request->getMethod();

        $info   = $dispatcher->dispatch($method, $uri);

        [$routeStatus, $handler] = $info;
        $routeParam = $info[2] ?? [];

        foreach ($routeParam as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        if ($routeStatus !== \FastRoute\Dispatcher::FOUND) {
            throw new RuntimeException('Route missed');
        }

        [$handler, $middleware] = $handler;

        $middleware = $middleware ?? [];
     
        $middleware[] = function (ServerRequestInterface $request, callable $next) use ($handler) {
            return $this->app->invoke($handler, [ServerRequestInterface::class => $request]);
        };

        return (new RequestHandler($middleware))->handle($request);
    }

    public function send(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        echo $response->getBody();
    }

    public function end()
    {
    }
}
