<?php

namespace SleepyLamp\Framework\Facades;

use SleepyLamp\Framework\Facade;

class Route extends Facade
{
    protected static function getFacadeClass()
    {
        return \SleepyLamp\Framework\Route::class;
    }
}
