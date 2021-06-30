<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

use think\Container;

class App extends Container
{
    protected $rootPath;

    protected $bind = [
        'http' => Http::class,
    ];

    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
        $this->instance(App::class, $this);
    }


    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
