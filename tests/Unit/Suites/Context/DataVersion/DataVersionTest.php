<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\Exception\EmptyVersionException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class DataVersionTest extends TestCase
{
    /**
     * @dataProvider emptyVersionProvider
     * @param mixed $emptyVersion
     */
    public function testExceptionIsThrownIfVersionIsEmpty($emptyVersion): void
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

    public function testExceptionIsThrownIfVersionIsInvalid(): void
    {
        $this->expectException(\TypeError::class);
        DataVersion::fromVersionString(true);
    }

    public function testVersionIsReturned(): void
    {
        $version = '1.0';
        $dataVersion = DataVersion::fromVersionString($version);
        $this->assertSame($version, (string) $dataVersion);
    }
}
