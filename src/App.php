<?php

namespace SleepyLamp\Framework;

use DI\ContainerBuilder;

class App
{
    protected $rootPath;

    protected $container;

    public function __construct($rootPath)
    {
        $builder = new ContainerBuilder();
        $this->rootPath = $rootPath;
        $this->container = $builder->build();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
