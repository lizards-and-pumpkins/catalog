<?php

namespace LizardsAndPumpkins\Util;

/**
 * @covers \every
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
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
}
