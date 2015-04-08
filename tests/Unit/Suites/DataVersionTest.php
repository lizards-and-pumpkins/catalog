<?php

namespace Brera;

/**
 * @covers Brera\DataVersion
 */
class DataVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Brera\EmptyVersionException
     * @param mixed $emptyVersion
     * @dataProvider emptyVersionProvider
     */
    public function itShouldThrowOnEmptyVersion($emptyVersion)
    {
        DataVersion::fromVersionString($emptyVersion);
    }

    /**
     * @return mixed[]
     */
    public function emptyVersionProvider()
    {
        return [
        [''],
        [0],
        [0.00],
        ];
    }

    /**
     * @test
     * @expectedException \Brera\InvalidVersionException
     * @param mixed $invalidVersion
     * @dataProvider invalidVersionProvider
     */
    public function itShouldThrownOnInvalidVersion($invalidVersion)
    {
        DataVersion::fromVersionString($invalidVersion);
    }

    /**
     * @return mixed[]
     */
    public function invalidVersionProvider()
    {
        return [
        [null],
        [[]],
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
    }
}
