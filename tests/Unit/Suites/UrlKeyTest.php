<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\InvalidUrlKeySourceException;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKey;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 */
class UrlKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateUrlKeyFromNonString()
    {
        $this->expectException(InvalidUrlKeySourceException::class);
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
     * @param string $urlKeySource
     * @param string $expectedUrlKey
     */
    public function testDisallowedCharactersAreReplacedWithUnderscores($urlKeySource, $expectedUrlKey)
    {
        $urlKey = UrlKey::fromString($urlKeySource);
        $this->assertEquals($expectedUrlKey, (string) $urlKey);
    }

    /**
     * @return array[]
     */
    public function urlKeySourceProvider()
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
