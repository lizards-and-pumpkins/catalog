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
    /**
     * @param string $code
     * @param string[] $containedSnippetCodes
     * @return SnippetContainer
     */
    private function createInstance(string $code, array $containedSnippetCodes) : SnippetContainer
    {
        return new SnippetContainer($code, $containedSnippetCodes);
    }

    public function testItReturnsTheContainerCode()
    {
        $container = $this->createInstance('test', ['foo', 'bar']);

        $this->assertSame('test', $container->getCode());
    }

    public function testItReturnsTheContainedSnippetCodes()
    {
        $container = $this->createInstance('test', ['abc', 'def']);
        $this->assertSame(['abc', 'def'], $container->getSnippetCodes());
    }

    public function testItThrowsAnExceptionIfTheContainerCodeIsNotAString()
    {
        $this->expectException(\TypeError::class);
        new SnippetContainer(12, []);
    }

    public function testItThrowsAnExceptionIfTheContainerCodeIsTooShort()
    {
        $this->expectException(InvalidSnippetContainerCodeException::class);
        $this->expectExceptionMessage('The snippet container code has to be at least 2 characters long');

        $this->createInstance('i', []);
    }

    public function testItReturnsAnAssociativeArray()
    {
        $container = $this->createInstance('test', ['foo', 'bar']);
        $jsonData = $container->toArray();
        
        $this->assertSame(['test' => ['foo', 'bar']], $jsonData);
    }

    public function testCanBeRehydrated()
    {
        $source = $this->createInstance('test', ['foo', 'bar']);
        $rehydrated = SnippetContainer::rehydrate($source->getCode(), $source->getSnippetCodes());
        
        $this->assertInstanceOf(SnippetContainer::class, $rehydrated);
        $this->assertSame($source->getCode(), $rehydrated->getCode());
        $this->assertSame($source->getSnippetCodes(), $rehydrated->getSnippetCodes());
    }
}
