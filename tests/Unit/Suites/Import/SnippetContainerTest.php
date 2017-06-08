<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Import\Exception\InvalidSnippetContainerCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\SnippetContainer
 */
class SnippetContainerTest extends TestCase
{
    public function testThrowsAnExceptionIfTheContainerCodeIsNotAString()
    {
        $this->expectException(\TypeError::class);
        new SnippetContainer(12, []);
    }

    public function testThrowsAnExceptionIfTheContainerCodeIsTooShort()
    {
        $this->expectException(InvalidSnippetContainerCodeException::class);
        $this->expectExceptionMessage('The snippet container code has to be at least 2 characters long');

        new SnippetContainer('i', []);
    }

    public function testReturnsSnippetContainerArrayRepresentation()
    {
        $container = new SnippetContainer('test', ['foo', 'bar']);
        $jsonData = $container->toArray();

        $this->assertSame(['test' => ['foo', 'bar']], $jsonData);
    }

    public function testCanBeRehydrated()
    {
        $rehydrated = SnippetContainer::rehydrate('test', ['foo', 'bar']);

        $this->assertInstanceOf(SnippetContainer::class, $rehydrated);
        $this->assertSame(['test' => ['foo', 'bar']], $rehydrated->toArray());
    }
}
