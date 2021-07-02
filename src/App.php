<?php

declare(strict_types=1);

namespace VividLamp\Framework;

use Illuminate\Container\Container;
use VividLamp\Framework\Error;

class App extends Container
{
    /** @var string 程序根目录 */
    protected $rootPath;

    protected $loadedProviders;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;

        static::setInstance($this);

        $this->instance(App::class, $this);

        $this->singleton('http', Http::class);
    }

    /**
     * 注册服务提供者
     * @param string $provider
     */
    public function register(string $provider)
    {
       $provider = new $provider($this);
       $provider->register();
       $this->loadedProviders[get_class($provider)] = $provider;
    }

    public function initialize()
    {
        $this->make(Error::class)->init();
        
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
