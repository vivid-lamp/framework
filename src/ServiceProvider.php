<?php

declare(strict_types=1);

namespace VividLamp\Framework;

abstract class ServiceProvider
{
    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }
}
