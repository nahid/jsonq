<?php

namespace Nahid\JsonQ\Tests\Facades;

use GrahamCampbell\TestBenchCore\FacadeTrait;
use Nahid\JsonQ\Tests\TestCase;

/**
 * This is the Talk facade test class.
 */
class Jsonq extends TestCase
{
    use FacadeTrait;

    /**
     * Get the facade accessor.
     *
     * @return string
     */
    protected function getFacadeAccessor()
    {
        return 'jsonq';
    }

    /**
     * Get the facade class.
     *
     * @return string
     */
    protected function getFacadeClass()
    {
        return \Nahid\JsonQ\Facades\Jsonq::class;
    }

    /**
     * Get the facade root.
     *
     * @return string
     */
    protected function getFacadeRoot()
    {
        return \Nahid\JsonQ\Jsonq::class;
    }
}
