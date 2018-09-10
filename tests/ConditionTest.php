<?php

namespace Nahid\JsonQ\Tests;

use Nahid\JsonQ\Condition;

class ConditionTest extends AbstractTestCase
{
    /**
     * @var  \Nahid\JsonQ\Condition;
     */
    protected $conditions;

    protected function setUp()
    {
        $this->conditions = new Condition();
    }

    public function testEqual()
    {
        $value = 10;
        $comp = '10';

        $resultTrue = $this->conditions->equal($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = 'a10';

        $resultFalse = $this->conditions->equal($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testStrictEqual()
    {
        $value = 10;
        $comp = $value;

        $resultTrue = $this->conditions->strictEqual($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = (string) $comp;
        $resultFalse = $this->conditions->strictEqual($value, $comp);
        $this->assertFalse($resultFalse);
    }


    public function testNotEqual()
    {
        $value = 10;
        $comp = '11';

        $resultTrue = $this->conditions->notEqual($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = '10';

        $resultFalse = $this->conditions->notEqual($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testStrictNotEqual()
    {
        $value = 10;
        $comp = $value + 1;

        $resultTrue = $this->conditions->strictNotEqual($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = 10;

        $resultFalse = $this->conditions->strictNotEqual($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testGreaterThan()
    {
        $value = 10;
        $comp = $value - 1;

        $resultTrue = $this->conditions->greaterThan($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = $value;

        $resultFalse = $this->conditions->greaterThan($value, $comp);
        $this->assertFalse($resultFalse);
    }
    public function testLessThan()
    {
        $value = 10;
        $comp = $value + 1;

        $resultTrue = $this->conditions->lessThan($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = $value;

        $resultFalse = $this->conditions->lessThan($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testGreaterThanOrEqual()
    {
        $value = 11;
        $comp = $value - 1;

        $resultGreaterTrue = $this->conditions->greaterThanOrEqual($value, $comp);
        $this->assertTrue($resultGreaterTrue);

        $comp = $value;

        $resultEqualTrue = $this->conditions->greaterThanOrEqual($value, $comp);
        $this->assertTrue($resultEqualTrue);

        $comp = $value + 1;

        $resultFalse = $this->conditions->greaterThanOrEqual($value, $comp);
        $this->assertFalse($resultFalse);
    }


    public function testLessThanOrEqual()
    {
        $value = 11;
        $comp = $value + 1;

        $resultGreaterTrue = $this->conditions->lessThanOrEqual($value, $comp);
        $this->assertTrue($resultGreaterTrue);

        $comp = $value;

        $resultEqualTrue = $this->conditions->lessThanOrEqual($value, $comp);
        $this->assertTrue($resultEqualTrue);

        $comp = $value - 1;

        $resultFalse = $this->conditions->lessThanOrEqual($value, $comp);
        $this->assertFalse($resultFalse);

    }

    public function testIn()
    {
        $value = 7;
        $comp = [2, 3, 1, 7, 9];

        $resultTrue = $this->conditions->in($value, $comp);
        $this->assertTrue($resultTrue);

        $value = 6;

        $resultFalse = $this->conditions->in($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testNotIn()
    {
        $value = 6;
        $comp = [2, 3, 1, 7, 9];

        $resultTrue = $this->conditions->notIn($value, $comp);
        $this->assertTrue($resultTrue);

        $value = 7;

        $resultFalse = $this->conditions->notIn($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testIsNull()
    {
        $value = null;

        $resultTrue = $this->conditions->isNull($value, null);
        $this->assertTrue($resultTrue);

        $value = true;

        $resultFalse = $this->conditions->isNull($value, null);
        $this->assertFalse($resultFalse);

    }

    public function testIsNotNull()
    {
        $value = true;

        $resultTrue = $this->conditions->isNotNull($value, null);
        $this->assertTrue($resultTrue);

        $value = null;

        $resultFalse = $this->conditions->isNotNull($value, null);
        $this->assertFalse($resultFalse);
    }


    public function testStartWith()
    {
        $value = '8801848044143';
        $comp = '88';

        $resultTrue = $this->conditions->startWith($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = '91';

        $resultFalse = $this->conditions->startWith($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testEndWith()
    {
        $value = 'Amir Khan';
        $comp = 'Khan';

        $resultTrue = $this->conditions->endWith($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = 'Chowdhury';

        $resultFalse = $this->conditions->endWith($value, $comp);
        $this->assertFalse($resultFalse);
    }

    public function testMatch()
    {
        $value = '8801848044143';
        $comp = '88[0-9]{11}';

        $resultTrue = $this->conditions->match($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = '99[0-9]{11}';

        $resultFalse = $this->conditions->match($value, $comp);
        $this->assertFalse($resultFalse);

    }

    public function testContains()
    {
        $value = 'I love Bangladesh very much';
        $comp = 'Bangladesh';

        $resultTrue = $this->conditions->contains($value, $comp);
        $this->assertTrue($resultTrue);

        $comp = 'Mars';

        $resultFalse = $this->conditions->contains($value, $comp);
        $this->assertFalse($resultFalse);
    }

    // Todo: make testable

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
