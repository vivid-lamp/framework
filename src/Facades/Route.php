<?php

namespace VividLamp\Framework\Facades;

use VividLamp\Framework\Facade;

class Route extends Facade
{
    protected static function getFacadeClass(): string
    {
        return \VividLamp\Framework\Route::class;
    }
}
