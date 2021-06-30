<?php

namespace app\kernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use app\kernel\App;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Psr7\Factory\ServerRequestFactory;
use Throwable;

class Http
{
    protected $app;
    protected $config;

    public function __construct(App $app, Config $config)
    {
        $this->app = $app;
        $this->config = $config;
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
        if (!$this->app->initialized()) {
            $this->app->initialize();
        }
    }

    public function runWithRequest(?ServerRequestInterface $request = null): ResponseInterface
    {
        $middleware = $this->config->get('middleware');
        $middleware[] = $this;
        $relay = new Relay($middleware, function ($entry) {
            return is_object($entry) ? $entry : $this->app->make($entry);
        });
        $request = $request ?? ServerRequestFactory::createFromGlobals();
        return $relay->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->dispatchToRoute($request);
    }

    public function dispatchToRoute(ServerRequestInterface $request): ResponseInterface
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            Route::setRouteCollector($r);
            include $this->app->getRootPath() . 'app/route/app.php';
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

        $relay = new Relay($middleware, function ($entry) {
            return is_object($entry) ? $entry : $this->app->make($entry);
        });
        return $relay->handle($request);
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
