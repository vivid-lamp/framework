<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

use think\Container;


class App extends Container
{
    protected $rootPath;

    protected $loadedProviders;

    protected $bind = [
        'http' => Http::class,
    ];

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->instance(App::class, $this);
        static::setInstance($this);
    }

    public function register(string $provider)
    {
       $provider = new $provider($this);
       $provider->register();
       $this->loadedProviders[get_class($provider)] = $provider;
    }

    public function initialize()
    {
        $this->boot();
    }

    public function boot()
    {
        foreach($this->loadedProviders as $provider) {
            $provider->boot();
        }
    }


    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
