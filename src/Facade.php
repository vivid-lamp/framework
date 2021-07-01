<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

use think\Container;

class Facade
{

    protected static function createFacade()
    {
        return Container::getInstance()->make(static::getFacadeClass());
    }

    protected static function getFacadeClass()
    {}

    public static function __callStatic($method, $params)
    {
        return call_user_func_array([static::createFacade(), $method], $params);
    }

}
