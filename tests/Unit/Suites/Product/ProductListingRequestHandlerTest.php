<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\UnableToHandleRequestException;
use Brera\Page;
use Brera\PageBuilder;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;

/**
 * @covers \Brera\Product\ProductListingRequestHandler
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria
 */
class ProductListingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSelectionCriteria;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGeneratorLocator;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var string
     */
    private $testUrlPathKey = 'stub-meta-info-key';

    /**
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $testMetaInfoKey;

    /**
     * @var string
     */
    private $testMetaInfoSnippetJson;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    protected function setUp()
    {
        $this->mockSelectionCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $this->mockSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => SearchCriteria::AND_CONDITION, 'criteria' => []]);

        $this->testMetaInfoKey = 'product_listing_' . $this->testUrlPathKey;
        $this->testMetaInfoSnippetJson = json_encode(ProductListingMetaInfoSnippetContent::create(
            $this->mockSelectionCriteria,
            'root-snippet-code',
            ['child-snippet1']
        )->getInfo());
        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubContext = $this->getMock(Context::class);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);
        $this->mockSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $this->requestHandler = new ProductListingRequestHandler(
            $this->testUrlPathKey,
            $this->stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $this->mockSnippetKeyGeneratorLocator
        );

        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testHttpHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testFalseIsReturnedIfThePageMetaInfoContentSnippetCanNotBeLoaded()
    {
        $exception = new KeyNotFoundException();
        $this->mockDataPoolReader->method('getSnippet')->willThrowException($exception);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testTrueIsReturnedIfThePageMetaInfoContentSnippetCanBeLoaded()
    {
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $this->testMetaInfoSnippetJson]
        ]);
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testPageMetaInfoSnippetIsCreated()
    {
        $this->mockMetaInfoSnippet();
        $this->requestHandler->process($this->stubRequest);

        $this->assertAttributeInstanceOf(
            ProductListingMetaInfoSnippetContent::class,
            'pageMetaInfo',
            $this->requestHandler
        );
    }

    public function testPageIsReturned()
    {
        $this->mockMetaInfoSnippet();
        $this->mockPageBuilder->method('buildPage')->willReturn($this->getMock(Page::class, [], [], '', false));

        $this->assertInstanceOf(Page::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testProductsInListingAreAddedToPageBuilder()
    {
        $this->mockDataPoolReader->method('getProductIdsMatchingCriteria')
            ->willReturn(['product_in_listing_id']);
        $this->mockDataPoolReader->method('getSnippets')
            ->willReturn([]);

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn([]);

        $this->mockSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturn($mockSnippetKeyGenerator);

        $this->mockPageBuilder->expects($this->once())
            ->method('addSnippetsToPage');

        $this->mockMetaInfoSnippet();
        $this->requestHandler->process($this->stubRequest);
    }

    private function mockMetaInfoSnippet()
    {
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $this->testMetaInfoSnippetJson]
        ]);
    }
}
