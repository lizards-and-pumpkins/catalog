<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 */
class ContentBlockIdTest extends TestCase
{
    public function testStringRepresentationOfContentBlockIdIsReturned(): void
    {
        $contentBlockIdString = 'foo';
        $contentBlockId = ContentBlockId::fromString($contentBlockIdString);
        $result = (string) $contentBlockId;

        $this->assertEquals($contentBlockIdString, $result);
    }
}
