<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\Exception\EmptyVersionException;

/**
 * @covers \LizardsAndPumpkins\Context\DataVersion\DataVersion
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
    public function emptyVersionProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testVersionIsReturned()
    {
        $version = '1.0';
        $dataVersion = DataVersion::fromVersionString($version);
        $this->assertSame($version, (string) $dataVersion);
    }
}
