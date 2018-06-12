<?php

namespace Nahid\JsonQ\Tests;

use Nahid\JsonQ\Condition;

class ConditionTest extends AbstractTestCase
{
    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider equalProvider
     */
    public function testEqual($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::equal($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider strictEqualProvider
     */
    public function testStrictEqual($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::strictEqual($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider notEqualProvider
     */
    public function testNotEqual($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::notEqual($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider strictNotEqualProvider
     */
    public function testStrictNotEqual($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::strictNotEqual($value, $comparable));
    }

    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider greaterThanProvider
     */
    public function testGreaterThan($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::greaterThan($value, $comparable));
    }

    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider lessThanProvider
     */
    public function testLessThan($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::lessThan($value, $comparable));
    }

    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider greaterThanOrEqualProvider
     */
    public function testGreaterThanOrEqual($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::greaterThanOrEqual($value, $comparable));
    }

    /**
     * @param mixed $value
     * @param mixed $comparable
     * @param bool $result
     * 
     * @dataProvider lessThanOrEqualProvider
     */
    public function testLessThanOrEqual($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::lessThanOrEqual($value, $comparable));
    }    
    
    /**
     * @param mixed $value
     * @param array $comparable
     * @param bool $result
     * 
     * @dataProvider inProvider
     */
    public function testIn($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::in($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param array $comparable
     * @param bool $result
     * 
     * @dataProvider notInProvider
     */
    public function testNotIn($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::notIn($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param bool $result
     * 
     * @dataProvider isNullProvider
     */
    public function testIsNull($value, $result)
    {
        $this->assertEquals($result, Condition::isNull($value));
    }
    
    /**
     * @param mixed $value
     * @param bool $result
     * 
     * @dataProvider isNotNullProvider
     */
    public function testIsNotNull($value, $result)
    {
        $this->assertEquals($result, Condition::isNotNull($value));
    }
    
    /**
     * @param mixed $value
     * @param string $comparable
     * @param bool $result
     * 
     * @dataProvider startWithProvider
     */
    public function testStartWith($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::startWith($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param string $comparable
     * @param bool $result
     * 
     * @dataProvider endWithProvider
     */
    public function testEndWith($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::endWith($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param string $comparable
     * @param bool $result
     * 
     * @dataProvider matchProvider
     */
    public function testMatch($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::match($value, $comparable));
    }
    
    /**
     * @param mixed $value
     * @param string $comparable
     * @param bool $result
     * 
     * @dataProvider containsProvider
     */
    public function testContains($value, $comparable, $result)
    {
        $this->assertEquals($result, Condition::contains($value, $comparable));
    }
    
    public function containsProvider()
    {
        return [
            ['test', 'a', false],
            ['test', 'te', true],
            ['test', 'st',  true],
            ['test', 'es',  true],
            ['test', 'test', true],
        ];
    }
    
    public function matchProvider()
    {
        return [
            [[], 1, false],
            [2, new \stdClass(), false],
            ['test', 'te', false],
            ['test', 'st', false],
            ['test', 'es', false],
            ['test', 'test', true],
            ['test', 'hh', false],
            ['test', null, false],
        ];
    }
    
    public function endWithProvider()
    {
        return [
            [[], 1, false],
            [2, new \stdClass(), false],
            ['test', 'te', false],
            ['test', 'st', true],
            ['test', null, true],
        ];
    }
    
    public function startWithProvider()
    {
        return [
            [[], 1, false],
            [2, new \stdClass(), false],
            ['test', 'te', true],
            ['test', 'es', false],
            ['test', null, true],
        ];
    }
    
    public function isNotNullProvider()
    {
        return [
            [1, true],
            [[1, 2, 3], true],
            ['test', true],
            [0, true],
            [null, false],
        ];
    }
    
    public function isNullProvider()
    {
        return [
            [1, false],
            [[1, 2, 3], false],
            ['test', false],
            [0, false],
            [null, true],
        ];
    }
    
    public function inProvider()
    {
        return [
            [1, [], false],
            [1, [1, 2, 3], true],
            [1, 1, false],
            [1, true, false],
            [1, [2, 3], false],
            [1, [2,3, [1]], false],
            [1, ['key1' => 1, 'key2' => 2], true]
        ];
    }
    
    public function notInProvider()
    {
        return [
            [1, [], true],
            [1, [1, 2, 3], false],
            [1, 1, false],
            [1, true, false],
            [1, [2, 3], true],
            [1, [2,3, [1]], true],
            [1, ['key1' => 1, 'key2' => 2], false]
        ];
    }
    
    public function greaterThanProvider()
    {
        return [
            [0, 1, false],
            [0,'1', false],
            [2, 2, false],
            [-1, 2, false],
            [-2, -3, true],
            [1, 0, true],
            [1.2, 1.1, true]
        ];
    }
    
    public function lessThanProvider()
    {
        return [
            [0, 1, true],
            [0,'1', true],
            [2, 2, false],
            [-1, 2, true],
            [-2, -3, false],
            [1, 0, false],
            [1.2, 1.1, false]
        ];
    }
    
    public function greaterThanOrEqualProvider()
    {
        return [
            [0, 1, false],
            [0,'1', false],
            [2, 2, true],
            [-1, 2, false],
            [-2, -3, true],
            [1, 0, true],
            [1.2, 1.1, true]
        ];
    }
    
    public function lessThanOrEqualProvider()
    {
        return [
            [0, 1, true],
            [0,'1', true],
            [2, 2, true],
            [-1, 2, true],
            [-2, -3, false],
            [1, 0, false],
            [1.2, 1.1, false]
        ];
    }
    
    public function strictEqualProvider()
    {
        return [
            [0, 1, false],
            ['0', 0, false],
            ['text', 'text2', false],
            ['text', 'text', true],
            [0, 0, true],
            [true, false, false],
            [true, true, true],
            [true, 'true', false]
        ];
    }
    
    public function equalProvider()
    {
        return [
            [0, 1, false],
            ['0', 0, true],
            ['text', 'text2', false],
            ['text', 'text', true],
            [0, 0, true],
            [true, false, false],
            [true, true, true]
        ];
    }
    
    public function strictNotEqualProvider()
    {
        return [
            [0, 1, true],
            ['0', 0, true],
            ['text', 'text2', true],
            ['text', 'text', false],
            [0, 0, false],
            [true, false, true],
            [true, true, false],
            [true, 'true', true]
        ];
    }
    
    public function notEqualProvider()
    {
        return [
            [0, 1, true],
            ['0', 0, false],
            ['text', 'text2', true],
            ['text', 'text', false],
            [0, 0, false],
            [true, false, true],
            [true, true, false]
        ];
    }
}
