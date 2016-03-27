<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\Exception\InvalidContentBlockIdException;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 */
class ContentBlockIdTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateContentBlockIdFromNonString()
    {
        $this->expectException(InvalidContentBlockIdException::class);
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
