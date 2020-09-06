<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\ContentBlockNotFoundException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockService
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryWithCallbackTrait
 */
class ContentBlockServiceTest extends TestCase
{
    /**
     * @var SnippetKeyGenerator|MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var DataPoolReader|MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var Context|MockObject
     */
    private $dummyContext;

    /**
     * @var ContentBlockService
     */
    private $service;

    final protected function setUp(): void
    {
        $this->dummyContext = $this->createMock(Context::class);
        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);

        /** @var SnippetKeyGeneratorLocator|MockObject $stubSnippetKeyGeneratorLocator */
        $stubSnippetKeyGeneratorLocator = $this->createMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturn($this->stubSnippetKeyGenerator);

        $this->service = new ContentBlockService($this->mockDataPoolReader, $stubSnippetKeyGeneratorLocator);
    }

    public function testThrowsExceptionIfBlockDoesNotExist(): void
    {
        $this->expectException(ContentBlockNotFoundException::class);

        $this->mockDataPoolReader->method('getSnippet')->willThrowException(new KeyNotFoundException());
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->with($this->dummyContext, [])->willReturn('');

        $this->service->getContentBlock('foo', $this->dummyContext);
    }

    public function testReturnsSnippet(): void
    {
        $contentBlockName = 'foo';
        $snippetContentValue = 'bar';

        $this->mockDataPoolReader->method('getSnippet')->with($contentBlockName)->willReturn($snippetContentValue);
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->with($this->dummyContext, [])
            ->willReturn($contentBlockName);

        $result = $this->service->getContentBlock($contentBlockName, $this->dummyContext);

        $this->assertSame($snippetContentValue, $result);
    }
}
