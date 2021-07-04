<?php

declare(strict_types=1);

namespace VividLamp\Framework;

use Illuminate\Container\Container;
use VividLamp\Framework\Exception\Handler;


class App extends Container
{
    /** @var string 程序根目录 */
    protected $rootPath;

    /** @var ServiceProvider[] */
    protected $loadedProviders;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;

        static::setInstance($this);
        $this->instance(App::class, $this);

        $this->singleton(Http::class);
        $this->alias(Http::class, 'http');
    }

    /**
     * 注册服务提供者
     * @param string $provider
     */
    public function register(string $provider)
    {
        $provider = new $provider($this);
        if (method_exists($provider, 'register')) {
            $provider->register();
        }
        $this->loadedProviders[get_class($provider)] = $provider;
    }

    public function initialize()
    {
        (new Error())->init();

        $this->boot();
    }

    public function boot()
    {
        array_walk($this->loadedProviders, function ($provider) {
            if (method_exists($provider, 'boot')) {
                $this->call([$provider, 'boot']);
            }
        });
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getExceptionHandler(): Handler
    {
        return $this->make(Handler::class);
    }
}
