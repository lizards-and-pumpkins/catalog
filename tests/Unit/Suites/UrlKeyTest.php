<?php

namespace Brera;

/**
 * @covers \Brera\UrlKey
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
}
