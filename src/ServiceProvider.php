<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

abstract class ServiceProvider
{

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }
    public function register()
    {
    }

    public function boot()
    {
    }
}
