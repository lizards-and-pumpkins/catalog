<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\UrlKey;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 */
class UrlKeyTest extends TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateUrlKeyFromNonString()
    {
        $this->expectException(\TypeError::class);
        UrlKey::fromString(1);
    }

    public function testUrlKeyCanBeCastedToString()
    {
        $dummyKey = 'foo';
        $urlKey = UrlKey::fromString($dummyKey);

        $this->assertEquals($dummyKey, (string) $urlKey);
    }

    /**
     * @dataProvider urlKeySourceProvider
     */
    public function testDisallowedCharactersAreReplacedWithUnderscores(string $urlKeySource, string $expectedUrlKey)
    {
        $urlKey = UrlKey::fromString($urlKeySource);
        $this->assertEquals($expectedUrlKey, (string) $urlKey);
    }

    /**
     * @return array[]
     */
    public function urlKeySourceProvider() : array
    {
        return [
            ['foo', 'foo'],
            ['foo_:bar', 'foo__bar'],
            ['foo1/bar', 'foo1/bar'],
            ['bar.html', 'bar.html'],
            ['/foo%', '/foo_'],
            ['///', '///'],
            ['$&"#', '$___'],
            ['$-_.+!*\'(),', '$-_.+!*\'(),']
        ];
    }
}
