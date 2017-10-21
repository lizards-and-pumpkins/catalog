<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SnippetReader
 */
class SnippetReaderTest extends TestCase
{
    public function testReturnsPageMetaSnippet()
    {
        $testUrlKey = 'foo';
        $testSnippetContent = 'bar';
        $testContextParts = ['baz', 'qux'];

        /** @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject $stubKeyValueStore */
        $stubKeyValueStore = $this->createMock(KeyValueStore::class);
        $stubKeyValueStore->method('get')->with('meta_foo_baz:bazValue_qux:quxValue')->willReturn($testSnippetContent);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('getIdForParts')->with(...$testContextParts)->willReturn('baz:bazValue_qux:quxValue');

        $snippetReader = new SnippetReader($stubKeyValueStore, ...$testContextParts);

        $this->assertSame($testSnippetContent, $snippetReader->getPageMetaSnippet($testUrlKey, $stubContext));
    }
}
