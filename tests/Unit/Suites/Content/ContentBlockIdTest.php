<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Content\Exception\InvalidContentBlockIdException;

/**
 * @covers \LizardsAndPumpkins\Content\ContentBlockId
 */
class ContentBlockIdTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateContentBlockIdFromNonString()
    {
        $this->setExpectedException(InvalidContentBlockIdException::class);
        ContentBlockId::fromString(1);
    }

    public function testStringRepresentationOfContentBlockIdIsReturned()
    {
        $contentBlockIdString = 'foo';
        $contentBlockId = ContentBlockId::fromString($contentBlockIdString);
        $result = (string) $contentBlockId;

        $this->assertEquals($contentBlockIdString, $result);
    }
}
