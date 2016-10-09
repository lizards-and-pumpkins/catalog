<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 */
class ContentBlockIdTest extends \PHPUnit_Framework_TestCase
{
    public function testStringRepresentationOfContentBlockIdIsReturned()
    {
        $contentBlockIdString = 'foo';
        $contentBlockId = ContentBlockId::fromString($contentBlockIdString);
        $result = (string) $contentBlockId;

        $this->assertEquals($contentBlockIdString, $result);
    }
}
