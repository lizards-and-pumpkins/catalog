<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers \every
 * @covers \typeof
 * @covers \pipeline
 */
class FunctionsTest extends TestCase
{
    private static $callbackArguments = [];

    /**
     * @param mixed $value
     * @param string|int $index
     */
    public static function notifyCallback($value, $index)
    {
        self::$callbackArguments[] = [$index, $value];
    }

    public function multiplyBy3(...$args)
    {
        return array_sum($args) * 3;
    }

    /**
     * @return array[]
     */
    private function getReceivedCallbackArguments() : array
    {
        return self::$callbackArguments;
    }

    protected function setUp()
    {
        self::$callbackArguments = [];
    }

    public function testEveryItemAndIndexIsPassedToTheCallback()
    {
        $sourceItems = [
            new \stdClass(),
            new \stdClass(),
        ];
        $receivedArguments = [];
        every($sourceItems, function ($item, $index) use (&$receivedArguments) {
            $receivedArguments[$index] = $item;
        });
        $this->assertSame($receivedArguments, $sourceItems);
    }

    public function testEveryWorksWithStringArrayIndexes()
    {
        $items = ['foo' => 'bar', 'baz' => 'qux'];
        $receivedIndexes = [];
        every($items, function ($item, $index) use (&$receivedIndexes) {
            $receivedIndexes[] = $index;
        });
        $this->assertSame(array_keys($items), $receivedIndexes);
    }

    public function testEveryWorksWithTraversable()
    {
        $array = ['foo', 'bar', 'baz'];
        $items = new \ArrayIterator($array);
        $receivedArguments = [];
        every($items, function ($item, $key) use (&$receivedArguments) {
            $receivedArguments[$key] = $item;
        });
        $this->assertSame($array, $receivedArguments);
    }

    public function testEveryWorksWithStringCallbacks()
    {
        $items = [111];
        every($items, '\LizardsAndPumpkins\Util\callback_function');
        $this->assertSame([[0, 111]], $this->getReceivedCallbackArguments());
    }

    public function testEveryWorksWithArrayCallbacks()
    {
        $items = [222];
        every($items, [self::class, 'notifyCallback']);
        $this->assertSame([[0, 222]], $this->getReceivedCallbackArguments());
    }

    /**
     * @param mixed $value
     * @param string $expected
     * @dataProvider typeofDataProvider
     */
    public function testTypeofReturnsExpectedStringRepresentationOfType($value, string $expected)
    {
        $this->assertSame($expected, typeof($value));
    }

    /**
     * @return array[]
     */
    public function typeofDataProvider() : array
    {
        return [
            ['', 'string'],
            [null, 'NULL'],
            [1, 'integer'],
            [.1, 'double'],
            [[], 'array'],
            [fopen(__FILE__, 'r'), 'resource'],
            [$this, get_class($this)],
        ];
    }

    /**
     * @param mixed $input
     * @param bool $expectedOutput
     * @dataProvider voidDataProvider
     */
    public function testIsEmptyReturnsExpectedBooleanValue($input, $expectedOutput)
    {
        $this->assertEquals($expectedOutput, isEmpty($input));
    }

    /**
     * @return array[]
     */
    public function voidDataProvider()
    {
        return [
            'Test 1' => ['', true],
            'Test 2' => ["", true],
            'Test 3' => [null, true],
            'Test 4' => [[], true],
            'Test 5' => [false, false],
            'Test 6' => [true, false],
            'Test 7' => [1, false],
            'Test 8' => [42, false],
            'Test 9' => [0, false],
            'Test 10' => [-1, false],
            'Test 11' => ["1", false],
            'Test 12' => ["0", false],
            'Test 13' => ["-1", false],
            'Test 14' => ["php", false],
            'Test 15' => ["true", false],
            'Test 16' => ["false", false],
            'Test 17' => [0.0, false],
            'Test 18' => ["0.0", false],
            'Test 19' => [0.00, false],
            'Test 20' => ["0.00", false],
            'Test 21' => [10/10-1, false]
        ];
    }

    public function testPipelineSingleFunction()
    {
        $this->assertSame('FOO BAR', pipeline('strtoupper')('foo bar'));
    }
    
    public function testPipelineCallsFunctionsLeftToRight()
    {
        $r = pipeline('strtolower', 'ucwords', function ($str) { return explode(' ', $str); }, 'array_reverse');
        $this->assertTrue(is_callable($r));
        $this->assertSame(['Bar', 'Foo'], $r('fOO BaR'));
    }

    public function testPipelineAcceptsDifferentCallables()
    {
        $inc = new class {
            public function __invoke($arg)
            {
                return $arg + 1;
            }
        };
        $r = pipeline(function () { return range(1, 10); }, 'array_sum', [$this, 'multiplyBy3'], $inc);
        $this->assertSame(166, $r());
    }

    public function testPipelineResultWithArrayMap()
    {
        $input = ['foo', 'barr', 'ba', 'qux'];
        $expected = ['1.51.5', '22', '11', '1.51.5'];
        $half = function ($n) { return $n / 2; };
        $duplicate_str = function ($s) { return str_repeat($s, 2); };
        
        $this->assertSame($expected, array_map(pipeline('strlen', $half, 'strval', $duplicate_str), $input));
    }
}

/**
 * @param mixed $value
 * @param int|string $index
 */
function callback_function($value, $index)
{
    FunctionsTest::notifyCallback($value, $index);
}
