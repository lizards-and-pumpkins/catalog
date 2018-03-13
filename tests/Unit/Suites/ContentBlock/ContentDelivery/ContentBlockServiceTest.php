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
 * Class ContentBlockServiceTest
 *
 * @package LizardsAndPumpkins\ContentBlock\ContentDelivery
 * @covers \LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockService
 */
class ContentBlockServiceTest extends TestCase
{

    /**
     * @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $snippetKeyGeneratorLocator;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $snippetKeyGenerator;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockDataPoolReader;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var ContentBlockService
     */
    protected $contentBlockService;

    public function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->snippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->snippetKeyGeneratorLocator = $this->createMock(SnippetKeyGeneratorLocator::class);

        $this->contentBlockService = new ContentBlockService($this->mockDataPoolReader, $this->snippetKeyGeneratorLocator);
    }

    public function testThrowsExceptionIfBlockDoesNotExist()
    {
        $this->mockDataPoolReader->method('getSnippet')
                                 ->willThrowException(new KeyNotFoundException());

        $this->snippetKeyGenerator->method('getKeyForContext')->with($this->context, [])->willReturn('');
        $this->snippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($this->snippetKeyGenerator);

        $this->expectException(ContentBlockNotFoundException::class);

        $this->contentBlockService->getContentBlock('foo', $this->context);
    }

    public function testReturnsSnippet()
    {
        $snippetContentValue = '{"hello":"world"}';
        $contentBlockName = 'block_name';
        $this->mockDataPoolReader->method('getSnippet')->with($contentBlockName)->willReturn($snippetContentValue);
        $this->snippetKeyGenerator->method('getKeyForContext')->with($this->context, [])->willReturn($contentBlockName);
        $this->snippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($this->snippetKeyGenerator);

        $this->assertSame($snippetContentValue, $this->contentBlockService->getContentBlock($contentBlockName, $this->context));
    }
}