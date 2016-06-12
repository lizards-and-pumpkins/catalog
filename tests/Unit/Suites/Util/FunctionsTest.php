<?php

namespace LizardsAndPumpkins\Util;

/**
 * @covers \every
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        self::$callbackArguments = [];
    }

    public function testEveryItemIsPassedToTheCallback()
    {
        $sourceItems = [
            new \stdClass(),
            new \stdClass(),
        ];
        $receivedArguments = [];
        every($sourceItems, function ($item, $key) use (&$receivedArguments) {
            $receivedArguments[$key] = $item;
        });
        $this->assertSame($receivedArguments, $sourceItems);
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

    public function testEveryWorksWithStringArrayKeys()
    {
        $items = ['foo' => 'bar', 'baz' => 'qux'];
        $receivedIndexes = [];
        every($items, function ($item, $index) use (&$receivedIndexes) {
            $receivedIndexes[] = $index;
        });
        $this->assertSame(array_keys($items), $receivedIndexes);
    }

    public function testEveryWorksWithStringCallbacks()
    {
        $items = [111];
        every($items, '\LizardsAndPumpkins\Util\callback_function');
        $this->assertSame([[0, 111]], self::$callbackArguments);
    }

    public function testEveryWorksWithArrayCallbacks()
    {
        $items = [222];
        every($items, [self::class, 'notifyCallback']);
        $this->assertSame([[0, 222]], self::$callbackArguments);
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
