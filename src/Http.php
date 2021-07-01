<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use SleepyLamp\Framework\Route;

class Http
{

    protected $app;

    protected $middleware = [];

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
            throw $e;
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

    public function runWithRequest(?ServerRequestInterface $request = null): ResponseInterface
    {
        $request = $request ?? ServerRequestFactory::createFromGlobals();

        $middleware = $this->middleware;
        $middleware[] = function (ServerRequestInterface $request, RequestHandlerInterface $next) {
            return $this->dispatchToRoute($request);
        };

        return (new RequestHandler($middleware))->handle($request);
    }

    public function dispatchToRoute(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->make(Route::class)->dispatch($request);
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
