<?php

declare(strict_types=1);

namespace VividLamp\Framework;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VividLamp\Framework\Exception\Handler;
use VividLamp\Framework\Route;

class Http
{

    protected $app;

    protected $middleware = [];

    protected $routeConfigFile;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function run(?ServerRequestInterface $request = null)
    {
        try {

            $this->initialize();
            $response = $this->runWithRequest($request);

        } catch (Throwable $e) {
            $handler = $this->app->make(Handler::class);
            $handler->report($e);
            $response = $handler->render($request, $e);
        }
        $this->send($response);
    }
    public function initialize()
    {
        $this->app->initialize();
    }

    public function loadRouteConfig(string $file)
    {
        $this->routeConfigFile = $file;
    }

    public function loadMiddleware(array $middleware)
    {
        $this->middleware = $middleware;
    }

    public function runWithRequest(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->middleware;
        $middleware[] = function (ServerRequestInterface $request, RequestHandlerInterface $next) {
            return $this->dispatchToRoute($request);
        };

        return (new RequestHandler($middleware))->handle($request);
    }

    public function dispatchToRoute(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->app->make(Route::class, ['configFile' => $this->routeConfigFile]);
        $this->app->instance(Route::class, $route);
        return $route->dispatch($request);
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
