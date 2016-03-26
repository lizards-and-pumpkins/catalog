<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\DataVersion\Exception\EmptyVersionException;
use LizardsAndPumpkins\Context\DataVersion\Exception\InvalidVersionException;

/**
 * @covers LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class DataVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider emptyVersionProvider
     * @param mixed $emptyVersion
     */
    public function testExceptionIsThrownIfVersionIsEmpty($emptyVersion)
    {
        $this->expectException(EmptyVersionException::class);
        DataVersion::fromVersionString($emptyVersion);
    }

    /**
     * @return array[]
     */
    public function emptyVersionProvider()
    {
        return [
            [''],
            [' '],
        ];
    }

    /**
     * @dataProvider invalidVersionProvider
     * @param mixed $invalidVersion
     */
    public function testExceptionIsThrownIfVersionIsInvalid($invalidVersion)
    {
        $this->expectException(InvalidVersionException::class);
        DataVersion::fromVersionString($invalidVersion);
    }

    /**
     * @return mixed[]
     */
    public function invalidVersionProvider()
    {
        return [
            [1],
            [.1],
            [null],
            [[]],
            [new \stdClass()],
            [true],
            [false],
        ];
    }

    public function testVersionIsReturned()
    {
        $version = '1.0';
        $dataVersion = DataVersion::fromVersionString($version);
        $this->assertSame($version, (string) $dataVersion);
    }
}
