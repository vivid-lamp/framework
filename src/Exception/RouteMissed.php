<?php

declare(strict_types=1);

namespace VividLamp\Framework\Exception;


class RouteMissed extends \RuntimeException
{
    /** @var int  */
    protected $code;

}