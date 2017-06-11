<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class SnippetContainerTest extends TestCase
{
    public function testReturnsSnippetContainerArrayRepresentation()
    {
        $containedSnippetCodes = [new SnippetCode('foo'), new SnippetCode('bar')];
        $container = new SnippetContainer(new SnippetCode('test'), ...$containedSnippetCodes);

        $this->assertSame(['test' => $containedSnippetCodes], $container->toArray());
    }

    public function testCanBeRehydrated()
    {
        $rehydrated = SnippetContainer::rehydrate('test', ...['foo', 'bar']);

        $this->assertInstanceOf(SnippetContainer::class, $rehydrated);
        $this->assertEquals(['test' => [new SnippetCode('foo'), new SnippetCode('bar')]], $rehydrated->toArray());
    }
}
