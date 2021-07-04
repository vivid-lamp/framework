<?php

declare(strict_types=1);

namespace VividLamp\Framework;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;


class Http
{
    /** @var App  */
    protected $app;

    /** @var mixed[] */
    protected $middleware = [];

    /** @var callable */
    protected $routeDispatcher;

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

            $this->app->getExceptionHandler()->report($e);
            $response = $this->app->getExceptionHandler()->render($request, $e);
        }
        $this->send($response);
    }

    public function initialize()
    {
        $this->app->initialize();
    }

    public function loadMiddleware(array $middleware)
    {
        $this->middleware = $middleware;
    }

    public function loadRouteDispatcher(callable $dispatcher)
    {
        $this->routeDispatcher = $dispatcher;
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
        return ($this->routeDispatcher)($request);
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
