<?php

namespace Brera\PoC;

class DataVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Brera\PoC\EmptyVersionException
     * @dataProvider emptyVersionProvider
     *
     * @param $emptyVersion
     */
    public function itShouldThrowOnEmptyVersion($emptyVersion)
    {
        DataVersion::fromVersionString($emptyVersion);
    }

    public function emptyVersionProvider()
    {
        return [
            [''],
            [0],
            [0.00],
        ];
    }

    /**
     * @param $invalidVersion
     *
     * @test
     * @expectedException \Brera\PoC\InvalidVersionException
     * @dataProvider invalidVersionProvider
     *
     */
    public function itShouldThrownOnInvalidVersion($invalidVersion)
    {
        DataVersion::fromVersionString($invalidVersion);
    }

    public function invalidVersionProvider()
    {
        return [
            [null],
            [array()],
            [new \stdClass()],
            [true],
            [false],
        ];
    }
}
