<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


class RequestHandler implements RequestHandlerInterface
{

    /** @var mixed[] */
    protected $queue;

    public function __construct($queue)
    {
        $this->queue = $queue;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = current($this->queue);
        next($this->queue);
        if (is_object($middleware) && $middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        } elseif ($middleware instanceof Closure) {
            return $middleware($request, $this);
        } else {
            $object = new $middleware();
            return $object->process($request, $this);
        }
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }
}
