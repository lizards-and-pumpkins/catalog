<?php

namespace Brera\PoC;

/**
 * @covers Brera\PoC\DataVersion
 */
class DataVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $emptyVersion
     *
     * @test
     * @expectedException \Brera\PoC\EmptyVersionException
     * @dataProvider emptyVersionProvider
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

    /**
     * @test
     */
    public function itShouldReturnVersion()
    {
        $version = '1.0';
        $dataVersion = DataVersion::fromVersionString($version);
        $this->assertSame($version, (string)$dataVersion);
        $this->assertSame($version, $dataVersion->getVersion());
    }
}
