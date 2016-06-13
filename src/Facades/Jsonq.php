<?php

namespace Nahid\JsonQ\Facades;

use Illuminate\Support\Facades\Facade;

class Jsonq extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Jsonq';
    }
}
