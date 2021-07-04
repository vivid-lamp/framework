<?php

declare(strict_types=1);

namespace VividLamp\Framework\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Handler
{
    public function report(\Throwable $e)
    {

    }

    public function render(ServerRequestInterface $request, \Throwable $e): ResponseInterface
    {

    }
}
