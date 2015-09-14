<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\UrlKey
 */
class UrlKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateUrlKeyFromNonString()
    {
        $this->setExpectedException(InvalidUrlKeySourceException::class);
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
            ['foo1/bar', 'foo1_bar'],
            ['bar.html', 'bar.html'],
            ['/foo%', '_foo_'],
            ['///', '___'],
            ['$&"#', '$___'],
            ['$-_.+!*\'(),', '$-_.+!*\'(),']
        ];
    }
}
